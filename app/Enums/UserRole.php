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

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'warning',
            self::SUPER_ADMIN => 'danger',
            self::STUDENT => 'info',
            self::TEACHER => 'success',
            self::ACCOUNTANT => 'primary',
            self::LIBRARIAN => 'secondary',
            self::HEAD_MASTER => 'dark',
            self::PRINCIPAL => 'dark',
            self::PARENT => 'info',
            self::STAFF => 'secondary',
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
