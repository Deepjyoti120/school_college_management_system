<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'fee_structure_id',
        'amount',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
