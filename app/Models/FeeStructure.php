<?php

namespace App\Models;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\RazorpayPaymentStatus;
use Carbon\Carbon;
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
        'gst_amount',
        'total_amount',
        'frequency',
        'description',
        'month',
        'year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'frequency' => FrequencyType::class,
        'type' => FeeType::class,
        'month' => 'integer',
        'year' => 'integer',
    ];

    protected $appends = [
        'frequency_label',
        'frequency_color',
        'type_label',
        'type_color',
        'month_name',
        'payment_status',
        'payment_status_label',
        'payment_status_color',
    ];

    protected static function booted()
    {
        static::creating(function ($fee) {
            $fee->applyGst();
        });

        static::updating(function ($fee) {
            $fee->applyGst();
        });
    }
    const GST_RATE = 18;

    public function applyGst()
    {
        $school = $this->school;
        if ($school && $school->is_gst_applicable) {
            $rate = $school->gst_rate ?? self::GST_RATE;
            $this->gst_amount = round(($this->amount * $rate) / 100, 2);
            $this->total_amount = $this->amount + $this->gst_amount;
        } else {
            $this->gst_amount = 0;
            $this->total_amount = $this->amount;
        }
    }


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

    public function getMonthNameAttribute(): ?string
    {
        return $this->month ? Carbon::create()->month($this->month)->format('F') : null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPaymentForUser($userId)
    {
        if ($this->relationLoaded('payments')) {
            return $this->payments
                ->where('user_id', $userId)
                ->sortByDesc('payment_date')
                ->first();
        }
        return $this->payments()
            ->where('user_id', $userId)
            ->latest('payment_date')
            ->first();
    }

    // Payment status accessor
    public function getPaymentStatusAttribute(): RazorpayPaymentStatus
    {
        $user = auth()->user();
        if (!$user) return RazorpayPaymentStatus::PENDING;
        $payment = $this->latestPaymentForUser($user->id);
        return $payment?->status ?? RazorpayPaymentStatus::PENDING;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->payment_status->label();
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return $this->payment_status->color();
    }

    public function scopePendingForUser($query, $userId)
    {
        return $query->whereDoesntHave('payments', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status', RazorpayPaymentStatus::PAID->value);
        });
    }
    public function users()
    {
        return $this->hasMany(User::class, 'class_id', 'class_id')
            ->whereColumn('users.school_id', 'fee_structures.school_id');
    }
    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
}
