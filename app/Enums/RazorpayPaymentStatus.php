<?php

namespace App\Enums;

enum RazorpayPaymentStatus: string
{
    case PENDING  = 'pending';
    case CAPTURED = 'captured';
    case FAILED   = 'failed';
    case REFUNDED = 'refunded';
    case AUTHORIZED = "authorized";

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
        };
    }

    /**
     * Bootstrap color for badges, etc.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING  => 'secondary',
            self::CAPTURED => 'success',
            self::FAILED   => 'danger',
            self::REFUNDED => 'warning',
            self::AUTHORIZED => 'info',
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
