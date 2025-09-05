<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SchoolClassSection extends Model
{
    use HasUlids;

    protected $fillable = [
        'class_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'section_id')->where('role', 'student');
    }
}
