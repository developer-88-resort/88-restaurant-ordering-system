<?php

namespace App\Enums;

enum UserInvitationStatus: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Pending => __('Pending Invitation'),
            self::Expired => __('Invitation Expired'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-green-100 text-green-800',
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Expired => 'bg-gray-200 text-gray-700',
        };
    }
}
