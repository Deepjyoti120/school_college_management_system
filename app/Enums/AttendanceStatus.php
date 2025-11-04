<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT   = 'present';
    case ABSENT    = 'absent';
    case LEAVE     = 'leave';
    case HALF_DAY  = 'half_day';
    case HOLIDAY   = 'holiday';
    case WEEKLY_OFF = 'weekly_off';
    case WORK_FROM_HOME = 'wfh';
    case LATE = 'late';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Present',
            self::ABSENT => 'Absent',
            self::LEAVE => 'Leave',
            self::HALF_DAY => 'Half Day',
            self::HOLIDAY => 'Holiday',
            self::WEEKLY_OFF => 'Weekly Off',
            self::WORK_FROM_HOME => 'Work From Home',
            self::LATE => 'Late',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'bg-green-500/10 text-green-700 border border-green-500/20',
            self::ABSENT => 'bg-red-500/10 text-red-700 border border-red-500/20',
            self::LEAVE => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
            self::HALF_DAY => 'bg-blue-500/10 text-blue-700 border border-blue-500/20',
            self::HOLIDAY => 'bg-purple-500/10 text-purple-700 border border-purple-500/20',
            self::WEEKLY_OFF => 'bg-gray-500/10 text-gray-700 border border-gray-500/20',
            self::WORK_FROM_HOME => 'bg-teal-500/10 text-teal-700 border border-teal-500/20',
            self::LATE => 'bg-orange-500/10 text-orange-700 border border-orange-500/20',
            default => 'bg-zinc-500/10 text-zinc-700 border border-zinc-500/20',
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
