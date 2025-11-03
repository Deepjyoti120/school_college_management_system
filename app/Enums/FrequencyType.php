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
            self::ONE_TIME => 'bg-indigo-500/10 text-indigo-700 border border-indigo-500/20',
            self::MONTHLY  => 'bg-blue-500/10 text-blue-700 border border-blue-500/20',      
            self::YEARLY   => 'bg-green-500/10 text-green-700 border border-green-500/20',   
            default        => 'bg-gray-500/10 text-gray-700 border border-gray-500/20',      
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
