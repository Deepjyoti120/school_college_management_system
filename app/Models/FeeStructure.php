<?php

namespace App\Models;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasUlids;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'name',
        'type',
        'amount',
        'frequency',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'frequency' => FrequencyType::class,
        'type' => FeeType::class,
    ];

    protected $appends = [
        'frequency_label',
        'frequency_color',
        'type_label',
        'type_color',
    ];


    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function getFrequencyLabelAttribute(): string
    {
        return $this->frequency->label();
    }

    public function getFrequencyColorAttribute(): string
    {
        return $this->frequency->color();
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type->color();
    }
}
