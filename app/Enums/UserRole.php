<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case TEACHER = 'teacher';
    case ACCOUNTANT = 'accountant';
    case LIBRARIAN = 'librarian';
    case HEAD_MASTER = 'head_master';
    case PRINCIPAL = 'principal';
    case PARENT = 'parent';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin',
            self::STUDENT => 'Student',
            self::TEACHER => 'Teacher',
            self::ACCOUNTANT => 'Accountant',
            self::LIBRARIAN => 'Librarian',
            self::HEAD_MASTER => 'Head Master',
            self::PRINCIPAL => 'Principal',
            self::PARENT => 'Parent',
            self::STAFF => 'Staff',
        };
    }

    // public function color(): string
    // {
    //     return match ($this) {
    //         self::ADMIN => 'warning',
    //         self::SUPER_ADMIN => 'danger',
    //         self::STUDENT => 'info',
    //         self::TEACHER => 'success',
    //         self::ACCOUNTANT => 'primary',
    //         self::LIBRARIAN => 'secondary',
    //         self::HEAD_MASTER => 'dark',
    //         self::PRINCIPAL => 'dark',
    //         self::PARENT => 'info',
    //         self::STAFF => 'secondary',
    //     };
    // }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN        => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',    
            self::SUPER_ADMIN  => 'bg-red-500/10 text-red-700 border border-red-500/20',          
            self::STUDENT      => 'bg-blue-500/10 text-blue-700 border border-blue-500/20',       
            self::TEACHER      => 'bg-green-500/10 text-green-700 border border-green-500/20',    
            self::ACCOUNTANT   => 'bg-indigo-500/10 text-indigo-700 border border-indigo-500/20', 
            self::LIBRARIAN    => 'bg-gray-500/10 text-gray-700 border border-gray-500/20',       
            self::HEAD_MASTER  => 'bg-zinc-700/10 text-zinc-800 border border-zinc-700/20',       
            self::PRINCIPAL    => 'bg-zinc-700/10 text-zinc-800 border border-zinc-700/20',       
            self::PARENT       => 'bg-sky-500/10 text-sky-700 border border-sky-500/20',
            self::STAFF        => 'bg-slate-500/10 text-slate-700 border border-slate-500/20',    
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

    public static function optionsForUser(?UserRole $currentRole): array
    {
        $roles = self::cases();
        if ($currentRole === self::SUPER_ADMIN) {
            $roles = array_filter($roles, fn($role) => $role !== self::SUPER_ADMIN);
        }

        if ($currentRole === self::ADMIN) {
            $roles = array_filter($roles, fn($role) => !in_array($role, [self::SUPER_ADMIN, self::ADMIN]));
        }

        return array_map(
            fn($role) => [
                'label' => $role->label(),
                'value' => $role->value,
            ],
            array_filter($roles, fn($role) => !in_array($role, [self::SUPER_ADMIN, self::ADMIN]))
        );
    }

    public static function allowedForUser(?UserRole $currentRole): array
    {
        $roles = self::cases();

        if ($currentRole === self::SUPER_ADMIN) {
            $roles = array_filter($roles, fn($role) => $role !== self::SUPER_ADMIN);
        }

        if ($currentRole === self::ADMIN) {
            $roles = array_filter($roles, fn($role) => !in_array($role, [self::SUPER_ADMIN, self::ADMIN]));
        }

        return array_filter($roles, fn($role) => !in_array($role, [self::SUPER_ADMIN, self::ADMIN]));
    }
}
