<?php

namespace App\Models;

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
        'description',
        'is_sunday',
    ];

    protected $casts = [
        'date' => 'date',
        'is_sunday' => 'boolean',
    ];

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
