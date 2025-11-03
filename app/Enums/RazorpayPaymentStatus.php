<?php

namespace App\Enums;

enum RazorpayPaymentStatus: string
{
    case PENDING  = 'pending';
    case CAPTURED = 'captured';
    case FAILED   = 'failed';
    case REFUNDED = 'refunded';
    case AUTHORIZED = "authorized";
    case PAID = "paid";

    /**
     * Human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Pending',
            self::CAPTURED => 'Captured',
            self::FAILED   => 'Failed',
            self::REFUNDED => 'Refunded',
            self::AUTHORIZED => 'Authorized',
            self::PAID => 'Paid',
        };
    }

    /**
     * Bootstrap color for badges, etc.
     */
    // public function color(): string
    // {
    //     return match ($this) {
    //         self::PENDING  => 'destructive',
    //         self::CAPTURED => 'success',
    //         self::FAILED   => 'destructive',
    //         self::REFUNDED => 'warning',
    //         self::AUTHORIZED => 'primary',
    //         self::PAID => 'success',    
    //         default => 'default',
    //     };
    // }
    public function color(): string
    {
        return match ($this) {
            self::PENDING     => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
            self::CAPTURED    => 'bg-green-500/10 text-green-700 border border-green-500/20',
            self::FAILED      => 'bg-red-500/10 text-red-700 border border-red-500/20',
            self::REFUNDED    => 'bg-sky-500/10 text-sky-700 border border-sky-500/20',
            self::AUTHORIZED  => 'bg-indigo-500/10 text-indigo-700 border border-indigo-500/20',
            self::PAID        => 'bg-green-500/10 text-green-700 border border-green-500/20',
            default           => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
        };
    }


    /**
     * Return array of options for select inputs
     */
    public static function options(): array
    {
        return array_map(
            fn($status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ],
            self::cases()
        );
    }

    /**
     * Return array of enum values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
