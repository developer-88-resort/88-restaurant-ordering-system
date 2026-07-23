<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => __('Superadmin'),
            self::Admin => __('Admin'),
            self::Staff => __('Staff'),
        };
    }
}
