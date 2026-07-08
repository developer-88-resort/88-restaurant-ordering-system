<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Preparing => 'Preparing',
            self::Ready => 'Ready',
            self::Served => 'Served',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Preparing => 'bg-blue-100 text-blue-800',
            self::Ready => 'bg-purple-100 text-purple-800',
            self::Served => 'bg-teal-100 text-teal-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Cancelled => 'bg-gray-200 text-gray-700',
        };
    }

    public function dotClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-500',
            self::Preparing => 'bg-blue-500',
            self::Ready => 'bg-purple-500',
            self::Served => 'bg-teal-500',
            self::Completed => 'bg-green-500',
            self::Cancelled => 'bg-gray-500',
        };
    }

    public function isFinal(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }
}
