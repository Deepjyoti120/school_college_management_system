<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class TeacherAttendanceExport implements FromArray
{
    protected $month;
    protected $year;
    protected $academicYearId;

    public function __construct($month, $year, $academicYearId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->academicYearId = $academicYearId;
    }

    public function array(): array
    {
        $user = auth()->user();

        // Same query as index page
        $attendances = Attendance::with('user')
            ->where('school_id', $user->school_id)
            ->where('role', 'teacher')
            ->when($this->academicYearId, fn($q) => $q->where('academic_year_id', $this->academicYearId))
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->get();

        // Group exactly like index page
        $users = $attendances->groupBy('user_id')->map(function ($records) {
            $user = $records->first()->user;
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'attendances' => $records->map(fn($a) => [
                    'date' => $a->date->format('Y-m-d'),
                    'status' => $a->status->value,
                    'label' => $a->status_label,
                ]),
            ];
        })->values();

        // Build days of month
        $days = collect(range(1, Carbon::create($this->year, $this->month)->daysInMonth))
            ->map(function ($day) {
                return [
                    'day_number' => $day,
                    'date' => Carbon::create($this->year, $this->month, $day)->format('Y-m-d'),
                ];
            });

        // Build header row
        $header = ['Teacher', 'Present', 'Absent', 'Leave'];
        foreach ($days as $d) {
            $header[] = $d['day_number'];
        }

        $rows = [];
        $rows[] = $header;

        // Build each teacher’s row
        foreach ($users as $u) {
            $row = [];
            $row[] = $u['user']['name'];

            $present = $u['attendances']->where('status', 'present')->count();
            $absent  = $u['attendances']->where('status', 'absent')->count();
            $leave   = $u['attendances']->where('status', 'leave')->count();

            $row[] = $present;
            $row[] = $absent;
            $row[] = $leave;

            foreach ($days as $d) {
                $att = $u['attendances']->firstWhere('date', $d['date']);
                $row[] = $att['label'] ?? '';
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
