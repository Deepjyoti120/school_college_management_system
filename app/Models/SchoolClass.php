<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasUlids;

    protected $fillable = [
        'name'
    ];

    public function sections()
    {
        return $this->hasMany(SchoolClassSection::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }
}
