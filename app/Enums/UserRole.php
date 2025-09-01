<?php

namespace App\Enums;

enum UserRole: string
{
    case COM = 'com';
    case GM = 'gm';
    case DEL = 'del';
    case FAC = 'fac';

    public function label(): string
    {
        return match ($this) {
            self::COM =>  'Company', //'COM',// 
            self::GM => 'General Manager', //'GM', //
            self::DEL =>  'Dealer', //'DEL',  //
            self::FAC => 'Factory', //'FAC',//
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COM => 'primary',
            self::GM => 'destructive',
            self::DEL => 'secondary',
            self::FAC => 'success',
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