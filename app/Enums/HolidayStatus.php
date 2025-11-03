<?php

namespace App\Enums;

enum HolidayStatus: string
{
    case HOLIDAY = 'holiday';
    case WEEKLY_OFF = 'weekly_off';
    case SPECIAL_OFF = 'special_off';
    case EXAM_BREAK = 'exam_break';

    public function label(): string
    {
        return match ($this) {
            self::HOLIDAY => 'Holiday',
            self::WEEKLY_OFF => 'Weekly Off',
            self::SPECIAL_OFF => 'Special Off',
            self::EXAM_BREAK => 'Exam Break',
        };
    }

    // public function color(): string
    // {
    //     return match ($this) {
    //         self::HOLIDAY     => 'default', 
    //         self::WEEKLY_OFF  => 'secondary', 
    //         self::SPECIAL_OFF => 'outline',
    //         self::EXAM_BREAK  => 'destructive',
    //     };
    // }

    public function color(): string
    {
        return match ($this) {
            self::HOLIDAY     => 'bg-green-500/10 text-green-700 border border-green-500/20',
            self::WEEKLY_OFF  => 'bg-gray-500/10 text-gray-700 border border-gray-500/20',
            self::SPECIAL_OFF => 'bg-blue-500/10 text-blue-700 border border-blue-500/20',
            self::EXAM_BREAK  => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
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
}
