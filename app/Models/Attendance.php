<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'school_id',
        'academic_year_id',
        'date',
        'check_in',
        'check_out',
        'work_minutes',
        'status',
        'remarks',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'class_id',
        'role',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => AttendanceStatus::class,
        'check_in_lat' => 'decimal:8',
        'check_in_lng' => 'decimal:8',
        'check_out_lat' => 'decimal:8',
        'check_out_lng' => 'decimal:8',
        'role' => UserRole::class,
        'work_minutes' => 'integer',
    ];


    protected $appends = [
        'date_formatted',
        'status_label',
        'status_color',
        'role_label',
        'role_color',
    ];

    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('F j, Y');
    }


    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }

    public function getStatusColorAttribute(): ?string
    {
        return $this->status?->color();
    }

    public function getRoleLabelAttribute(): ?string
    {
        return $this->role?->label();
    }

    public function getRoleColorAttribute(): ?string
    {
        return $this->role?->color();
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
