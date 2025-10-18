<?php

namespace App\Enums;

enum BoardTypes: string
{
    case CBSE = 'cbse';
    case ICSE = 'icse';
    case STATE = 'state';
    case IB = 'ib';
    case IGCSE = 'igcse';
    case NIOS = 'nios';
    case SEBA = 'seba';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CBSE => 'CBSE (Central Board of Secondary Education)',
            self::ICSE => 'ICSE (Indian Certificate of Secondary Education)',
            self::STATE => 'State Board',
            self::IB => 'IB (International Baccalaureate)',
            self::IGCSE => 'IGCSE (Cambridge International)',
            self::NIOS => 'NIOS (National Institute of Open Schooling)',
            self::SEBA => 'SEBA (Board of Secondary Education, Assam)',
            self::OTHER => 'Other Board',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CBSE => 'primary',
            self::ICSE => 'info',
            self::STATE => 'success',
            self::IB => 'warning',
            self::IGCSE => 'secondary',
            self::NIOS => 'dark',
            self::OTHER => 'muted',
            self::SEBA => 'danger',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($board) => [
                'label' => $board->label(),
                'value' => $board->value,
            ],
            self::cases()
        );
    }
}
