<?php

namespace App\Enums;

enum FrequencyType: string
{
    case ONE_TIME = 'one_time';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';


    public function label(): string
    {
        return match ($this) {
            self::ONE_TIME => 'One Time',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ONE_TIME => 'primary',
            self::MONTHLY => 'info',
            self::YEARLY => 'success',
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
