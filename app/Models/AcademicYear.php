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

    protected static function booted()
    {
        static::creating(function ($year) {
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
            $lastYear = AcademicYear::latest('end_date')->first();
            if ($lastYear) {
                $year->start_date = Carbon::parse($lastYear->end_date)->addDay();
                $year->end_date   = $year->start_date->copy()->addYear()->subDay();
            } else {
                $year->start_date = Carbon::create(null, 3, 1);
                $year->end_date   = $year->start_date->copy()->addYear()->subDay();
            }
            $year->name = $year->start_date->format('Y') . '-' . $year->end_date->format('Y');
            $year->is_current = true;
        });
    }

     public static function getOrCreateCurrentAcademicYear($schoolId): AcademicYear
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        if (!$academicYear) {
            return AcademicYear::create([
                'school_id' => $schoolId,
            ]);
        }
        if (Carbon::today()->gt($academicYear->end_date)) {
            $academicYear->update(['is_current' => false]);
            return AcademicYear::create([
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
