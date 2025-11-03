<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'school_id',
        'academic_year_id',
        'date',
        'name',
        'description',
        'is_sunday',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_sunday' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'date_formatted',
    ];

    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('F j, Y'); // e.g. March 23, 2025
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
