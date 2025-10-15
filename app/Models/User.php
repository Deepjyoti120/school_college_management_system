<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Notifiable, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'dob',
        'doj',
        'password',
        'role',
        'phone',
        'latitude',
        'longitude',
        'profile_photo',
        'is_active',
        'country_code',
        'device_info',
        'fcm_token',
        'class_id',
        'section_id',
        'school_id',
        'roll_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'device_info',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dob' => 'date',
            'doj' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
            'device_info' => 'array',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $appends = ['role_label', 'role_color', 'profile_url', 'dob_formatted', 'doj_formatted'];

    public function getDobFormattedAttribute()
    {
        return Carbon::parse($this->dob)->format('F j, Y'); // e.g. March 23, 2025
    }
    public function getDojFormattedAttribute()
    {
        return Carbon::parse($this->doj)->format('F j, Y'); // e.g. March 23, 2025
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }

    public function getRoleColorAttribute(): string
    {
        return $this->role->color();
    }

    public function getProfileUrlAttribute(): ?string
    {
        return $this->profile_photo ?  Storage::url($this->profile_photo) : null;
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(SchoolClassSection::class, 'section_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
