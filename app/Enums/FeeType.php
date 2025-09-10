<?php

namespace App\Enums;

enum FeeType: string
{
    case ADMISSION = 'admission';
    case MONTHLY = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::ADMISSION => 'Addmission',
            self::MONTHLY => 'Monthly',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMISSION => 'warning',
            self::MONTHLY => 'info',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($role) => [
                'label' => $role->label(),
                'value' => $role->value,
            ],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
