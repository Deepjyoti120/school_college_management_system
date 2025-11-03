<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class School extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'address',
        'pin',
        'district',
        'state',
        'country',
        'phone',
        'email',
        'website',
        'latitude',
        'longitude',
        'logo_path',
        'cover_path',
        'is_active',
        'is_gst_applicable',
        'gst_rate',
        'academic_start_date',
        'academic_end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_gst_applicable' => 'boolean',
        'gst_rate' => 'decimal:2',
        'academic_start_date' => 'date',
        'academic_end_date' => 'date',
    ];

    protected $appends = ['logo_url', 'cover_url'];


    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ?  Storage::url($this->logo_path) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ?  Storage::url($this->cover_path) : null;
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
