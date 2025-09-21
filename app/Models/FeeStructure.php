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

    public function payment()
    {
        $user = auth()->user();
        return $this->hasOne(Payment::class)
            ->where('user_id', $user->id)
            ->where('month', $this->month)
            ->where('year', $this->year);
    }

    public function getPaymentStatusAttribute(): string
    {
        $payment = $this->payment;
        return $payment ? $payment->status : RazorpayPaymentStatus::PENDING->value;
    }
}
