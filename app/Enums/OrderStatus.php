<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case DISPATCHED = 'dispatched';

    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Pending',
            self::REJECTED => 'Rejected',
            self::APPROVED => 'Approved',
            self::DISPATCHED => 'Dispatched',
        };
    }
    
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'primary',
            self::REJECTED => 'destructive',
            self::APPROVED => 'secondary',
            self::DISPATCHED => 'success',
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
