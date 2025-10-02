<?php

namespace App\Models;

use App\Enums\RazorpayPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUlids;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'fee_structure_id',
        'user_id',
        'month',
        'year',
        'status',
        'amount',
        'gst_amount',
        'total_amount',
        'currency',
        'payment_date',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'is_webhook',
    ];

    protected $appends = ['amount_in_paise', 'status_color', 'status_label'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => RazorpayPaymentStatus::class,
        'is_webhook' => 'boolean',
    ];

    public function getStatusColorAttribute(): ?string
    {
        return $this->status?->color();
    }

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }


    public function getAmountInPaiseAttribute(): int
    {
        return (int) round($this->amount * 100);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function class()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
