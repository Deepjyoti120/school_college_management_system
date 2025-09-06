<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'school_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(SchoolClassSection::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', UserRole::STUDENT);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
