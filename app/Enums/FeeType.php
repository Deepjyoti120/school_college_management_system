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

    // public function color(): string
    // {
    //     return match ($this) {
    //         self::ADMISSION => 'warning',
    //         self::MONTHLY => 'info',
    //     };
    // }
    public function color(): string
    {
        return match ($this) {
            self::ADMISSION => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
            self::MONTHLY   => 'bg-blue-500/10 text-blue-700 border border-blue-500/20',
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
