<?php

namespace App\Models;

use App\Enums\HolidayStatus;
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
        'status',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
        'status' => HolidayStatus::class,
    ];

    protected $appends = [
        'date_formatted',
        'status_label',
        'status_color',
    ];

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }

    public function getStatusColorAttribute(): ?string
    {
        return $this->status?->color();
    }

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
