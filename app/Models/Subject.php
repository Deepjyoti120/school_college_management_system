<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasUlids;

    protected $fillable = [
        'school_id',
        'class_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_subjects')
            ->withTimestamps();
    }
}
