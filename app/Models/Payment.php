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
    ];

    protected $appends = ['amount_in_paise'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => RazorpayPaymentStatus::class
    ];

    public function getAmountInPaiseAttribute(): int
    {
        return (int) round($this->amount * 100);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
