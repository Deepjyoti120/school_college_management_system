<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $year = now()->month < 4 ? now()->year - 1 : now()->year;
            $startDate = Carbon::create($year, 4, 1);
            $endDate = $startDate->copy()->addYear()->subDay();
            $school = School::create([
                'name' => 'Angels English School',
                'address' => 'Sualkuchi, Guwahati',
                'pin' => '781001',
                'district' => 'Kamrup',
                'state' => 'Assam',
                'country' => 'India',
                'phone' => '9876543210',
                'email' => 'info@springfield.edu',
                'website' => 'https://springfield.edu',
                'latitude' => 26.1445,
                'longitude' => 91.7362,
                'is_active' => true,
                'academic_start_date' => $startDate,
                'academic_end_date' => $endDate,
            ]);

            User::create([
                'name' => 'School Principal',
                'email' => 'principal@gmail.com',
                'password' => Hash::make('secret1234'),
                'role' => UserRole::PRINCIPAL,
                'country_code'  => '+91',
                'phone' => '1111111111',
                'school_id' => $school->id,
                'is_active' => true,
            ]);

            // classes and sub classes
            $classes = [
                'Class 1',
                'Class 2',
                'Class 3',
                'Class 4',
                'Class 5',
                'Class 6',
                'Class 7',
                'Class 8',
                'Class 9',
                'Class 10',
            ];
            $sections = ['A', 'B', 'C', 'D', 'E'];

            foreach ($classes as $className) {
                $class = $school->classes()->create(['name' => $className, 'is_active' => true]);
                foreach ($sections as $sectionName) {
                    $class->sections()->create(['name' => $sectionName, 'is_active' => true]);
                }
            }
        });
    }
}
