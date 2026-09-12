<?php

namespace App\Domain\Admin\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::STAFF => 'Staf Program',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SUPER_ADMIN;
    }
}
