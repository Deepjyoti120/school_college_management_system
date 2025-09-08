<?php

namespace App\Models;

use App\Enums\FeeType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class FeeGenerate extends Model
{
    use HasUlids;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'month',
        'year',
        'type',
    ];

    protected $casts = [
        'type' => FeeType::class,
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }


}
