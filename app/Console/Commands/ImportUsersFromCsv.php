<?php

namespace App\Console\Commands;

use App\Enums\BoardTypes;
use App\Enums\UserRole;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolClassSection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportUsersFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-users-from-csv
                            {file=1 NURSERY VI ONWARD.csv : CSV file path}
                            {--school-id= : Target school ULID}
                            {--board=seba : Board value (cbse|icse|state|ib|igcse|nios|seba|other)}
                            {--password=12345678 : Default password for new users}
                            {--country-code=+91 : Country code for valid phone numbers}
                            {--roll-format=section : section => A-1, raw => 1}
                            {--skip-existing : Skip existing users instead of updating}
                            {--strict : Do not create missing class/section records}
                            {--dry-run : Parse and validate without writing to DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import student users from class-wise CSV into users table with validations.';

    /**
     * @var array<string, string|null>
     */
    private array $classCache = [];

    /**
     * @var array<string, string|null>
     */
    private array $sectionCache = [];

    /**
     * @var list<string>
     */
    private array $notes = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $csvPath = $this->resolveCsvPath((string) $this->argument('file'));
        if ($csvPath === null) {
            $this->error('CSV file not found. Pass a valid path as the first argument.');

            return self::FAILURE;
        }

        $school = $this->resolveSchool((string) $this->option('school-id'));
        if ($school === null) {
            return self::FAILURE;
        }

        $validBoards = array_map(fn(BoardTypes $board): string => $board->value, BoardTypes::cases());
        $board = strtolower((string) $this->option('board'));
        if (!in_array($board, $validBoards, true)) {
            $this->error('Invalid --board value. Allowed: '.implode(', ', $validBoards));

            return self::FAILURE;
        }

        $rollFormat = strtolower((string) $this->option('roll-format'));
        if (!in_array($rollFormat, ['section', 'raw'], true)) {
            $this->error('Invalid --roll-format. Allowed values: section, raw');

            return self::FAILURE;
        }

        $defaultPassword = (string) $this->option('password');
        if ($defaultPassword === '') {
            $this->error('--password cannot be empty.');

            return self::FAILURE;
        }

        $countryCode = (string) $this->option('country-code');
        $dryRun = (bool) $this->option('dry-run');
        $createMissing = ! (bool) $this->option('strict');
        $updateExisting = ! (bool) $this->option('skip-existing');

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->error('Could not open file: '.$csvPath);

            return self::FAILURE;
        }

        $stats = [
            'rows_read' => 0,
            'student_rows' => 0,
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'duplicates' => 0,
            'invalid' => 0,
            'skipped_existing' => 0,
        ];

        $currentClassName = null;
        $currentSectionName = null;
        $seenInFile = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $stats['rows_read']++;
            $lineNo = $stats['rows_read'];

            $firstCell = $this->cleanCell($row[0] ?? null);
            if ($firstCell === '') {
                continue;
            }

            if ($this->isClassHeader($firstCell)) {
                $parsed = $this->parseClassSectionFromHeader($firstCell);
                if ($parsed === null) {
                    $stats['invalid']++;
                    $this->addNote("Line {$lineNo}: could not parse class/section header: {$firstCell}");
                    continue;
                }

                [$currentClassName, $currentSectionName] = $parsed;
                continue;
            }

            if ($this->isColumnHeader($firstCell)) {
                continue;
            }

            if (!preg_match('/^\d+$/', $firstCell)) {
                continue;
            }

            $stats['student_rows']++;

            if ($currentClassName === null || $currentSectionName === null) {
                $stats['invalid']++;
                $this->addNote("Line {$lineNo}: student row found before class/section header.");
                continue;
            }

            $classId = $this->resolveClassId(
                schoolId: $school->id,
                className: $currentClassName,
                createMissing: $createMissing,
                dryRun: $dryRun,
            );
            if ($classId === null) {
                $stats['invalid']++;
                $this->addNote("Line {$lineNo}: class '{$currentClassName}' not found.");
                continue;
            }

            $sectionId = $this->resolveSectionId(
                classId: $classId,
                sectionName: $currentSectionName,
                createMissing: $createMissing,
                dryRun: $dryRun,
            );
            if ($sectionId === null) {
                $stats['invalid']++;
                $this->addNote("Line {$lineNo}: section '{$currentSectionName}' not found for class '{$currentClassName}'.");
                continue;
            }

            $name = $this->cleanCell($row[3] ?? null);
            if ($name === '') {
                $stats['invalid']++;
                $this->addNote("Line {$lineNo}: name is empty.");
                continue;
            }

            $rawRoll = $firstCell;
            $rollNumber = $rollFormat === 'section'
                ? strtoupper($currentSectionName).'-'.$rawRoll
                : $rawRoll;

            $fileDuplicateKey = $school->id.'|'.$classId.'|'.$rollNumber;
            if (isset($seenInFile[$fileDuplicateKey])) {
                $stats['duplicates']++;
                $this->addNote("Line {$lineNo}: duplicate roll '{$rollNumber}' in the same import file.");
                continue;
            }
            $seenInFile[$fileDuplicateKey] = true;

            $phoneInput = $this->cleanCell($row[10] ?? null);
            $phone = $this->normalizePhone($phoneInput);
            if ($phoneInput !== '' && $phone === null) {
                $this->addNote("Line {$lineNo}: phone '{$phoneInput}' is invalid; saved as NULL.");
            }

            $payload = [
                'name' => $name,
                'role' => UserRole::STUDENT->value,
                'school_id' => $school->id,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'roll_number' => $rollNumber,
                'board' => $board,
                'phone' => $phone,
                'country_code' => $phone ? $countryCode : null,
                'is_active' => true,
            ];

            $existingUser = User::query()
                ->where('school_id', $school->id)
                ->where('class_id', $classId)
                ->where('roll_number', $rollNumber)
                ->where('role', UserRole::STUDENT->value)
                ->first();

            if ($existingUser) {
                if (! $updateExisting) {
                    $stats['skipped_existing']++;
                    continue;
                }

                if ($dryRun) {
                    $stats['updated']++;
                    continue;
                }

                $existingUser->fill($payload);
                if ($existingUser->isDirty()) {
                    $existingUser->save();
                    $stats['updated']++;
                } else {
                    $stats['unchanged']++;
                }
                continue;
            }

            if ($dryRun) {
                $stats['created']++;
                continue;
            }

            $payload['password'] = $defaultPassword;
            User::create($payload);
            $stats['created']++;
        }

        fclose($handle);

        $mode = $dryRun ? 'DRY-RUN' : 'WRITE';
        $this->info("Import completed ({$mode}) for school: {$school->name} ({$school->id})");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Rows read', $stats['rows_read']],
                ['Student rows detected', $stats['student_rows']],
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Unchanged', $stats['unchanged']],
                ['Skipped existing', $stats['skipped_existing']],
                ['Duplicate rows in file', $stats['duplicates']],
                ['Invalid rows', $stats['invalid']],
            ]
        );

        if (!empty($this->notes)) {
            $this->newLine();
            $this->warn('Notes (first '.count($this->notes).' only):');
            foreach ($this->notes as $note) {
                $this->line('- '.$note);
            }
        }

        return self::SUCCESS;
    }

    private function resolveCsvPath(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (is_file($path)) {
            return $path;
        }

        $basePathCandidate = base_path($path);
        if (is_file($basePathCandidate)) {
            return $basePathCandidate;
        }

        $storageCandidate = storage_path('app/'.$path);
        if (is_file($storageCandidate)) {
            return $storageCandidate;
        }

        return null;
    }

    private function resolveSchool(string $schoolId): ?School
    {
        if ($schoolId !== '') {
            $school = School::find($schoolId);
            if (!$school) {
                $this->error("School not found for --school-id={$schoolId}");
                return null;
            }

            return $school;
        }

        $schoolCount = School::count();
        if ($schoolCount === 1) {
            $school = School::first();
            if ($school !== null) {
                $this->warn("Using only available school: {$school->name} ({$school->id})");
                return $school;
            }
        }

        $this->error('Please pass --school-id=<ULID>.');
        $this->line('Tip: php artisan tinker --execute="\\App\\Models\\School::select(\'id\',\'name\')->get()"');

        return null;
    }

    private function isClassHeader(string $value): bool
    {
        return str_starts_with(strtoupper($value), 'CLASS');
    }

    private function isColumnHeader(string $value): bool
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        return str_starts_with($normalized, 'ROLLNO');
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function parseClassSectionFromHeader(string $header): ?array
    {
        $compact = strtoupper(preg_replace('/\s+/', '', $header) ?? '');

        if (!preg_match('/CLASS-?([A-Z0-9-]+)-SEC-([A-Z0-9-]+)/', $compact, $matches)) {
            return null;
        }

        $className = trim($matches[1], '-');
        $sectionName = trim($matches[2], '-');

        if ($className === '' || $sectionName === '') {
            return null;
        }

        return [$className, $sectionName];
    }

    private function resolveClassId(string $schoolId, string $className, bool $createMissing, bool $dryRun): ?string
    {
        $cacheKey = $schoolId.'|'.strtoupper($className);
        if (array_key_exists($cacheKey, $this->classCache)) {
            return $this->classCache[$cacheKey];
        }

        $class = SchoolClass::query()
            ->where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = ?', [strtolower($className)])
            ->first();

        if ($class) {
            return $this->classCache[$cacheKey] = $class->id;
        }

        if (! $createMissing) {
            return $this->classCache[$cacheKey] = null;
        }

        if ($dryRun) {
            return $this->classCache[$cacheKey] = 'dry-class-'.Str::ulid();
        }

        $class = SchoolClass::create([
            'name' => $className,
            'school_id' => $schoolId,
            'is_active' => true,
        ]);

        $this->info("Created class '{$className}' for school {$schoolId}.");

        return $this->classCache[$cacheKey] = $class->id;
    }

    private function resolveSectionId(string $classId, string $sectionName, bool $createMissing, bool $dryRun): ?string
    {
        $cacheKey = $classId.'|'.strtoupper($sectionName);
        if (array_key_exists($cacheKey, $this->sectionCache)) {
            return $this->sectionCache[$cacheKey];
        }

        $section = SchoolClassSection::query()
            ->where('class_id', $classId)
            ->whereRaw('LOWER(name) = ?', [strtolower($sectionName)])
            ->first();

        if ($section) {
            return $this->sectionCache[$cacheKey] = $section->id;
        }

        if (! $createMissing) {
            return $this->sectionCache[$cacheKey] = null;
        }

        if ($dryRun) {
            return $this->sectionCache[$cacheKey] = 'dry-section-'.Str::ulid();
        }

        $section = SchoolClassSection::create([
            'class_id' => $classId,
            'name' => $sectionName,
            'is_active' => true,
        ]);

        $this->info("Created section '{$sectionName}' for class {$classId}.");

        return $this->sectionCache[$cacheKey] = $section->id;
    }

    private function normalizePhone(string $phone): ?string
    {
        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 10) {
            return $digits;
        }

        return null;
    }

    private function cleanCell(?string $value): string
    {
        $value = trim((string) $value);

        return ltrim($value, "\xEF\xBB\xBF");
    }

    private function addNote(string $message): void
    {
        if (count($this->notes) < 20) {
            $this->notes[] = $message;
        }
    }
}
