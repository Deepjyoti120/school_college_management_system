<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'school_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // protected static function booted()
    // {
    //     static::creating(function ($year) {
    //         AcademicYear::where('is_current', true)->update(['is_current' => false]);
    //         $lastYear = AcademicYear::latest('end_date')->first();
    //         if ($lastYear) {
    //             $year->start_date = Carbon::parse($lastYear->end_date)->addDay();
    //             $year->end_date   = $year->start_date->copy()->addYear()->subDay();
    //         } else {
    //             $year->start_date = Carbon::create(null, 3, 1);
    //             $year->end_date   = $year->start_date->copy()->addYear()->subDay();
    //         }
    //         $year->name = $year->start_date->format('Y') . '-' . $year->end_date->format('Y');
    //         $year->is_current = true;
    //     });
    // }

    public static function getOrCreateCurrentAcademicYear($schoolId): AcademicYear
    {
        $school = School::find($schoolId);
        if (!$school) {
            throw new \Exception("School not found");
        }
        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $startMonth = $school->academic_start_date
            ? Carbon::parse($school->academic_start_date)->month
            : 4;

        $startDay = $school->academic_start_date
            ? Carbon::parse($school->academic_start_date)->day
            : 1;
        $createNewYear = false;
        if (!$academicYear) {
            $createNewYear = true;
        } elseif (Carbon::today()->gt($academicYear->end_date)) {
            $academicYear->update(['is_current' => false]);
            $createNewYear = true;
        }
        if ($createNewYear) {
            if ($academicYear) {
                $startDate = Carbon::parse($academicYear->end_date)->addDay();
            } else {
                $startDate = Carbon::create(null, $startMonth, $startDay);
            }
            $endDate = $startDate->copy()->addYear()->subDay();
            return AcademicYear::create([
                'school_id' => $schoolId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'name' => $startDate->format('Y') . '-' . $endDate->format('Y'),
                'is_current' => true,
                'school_id' => $schoolId,
            ]);
        }
        return $academicYear;
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)
            ->latest('start_date');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
