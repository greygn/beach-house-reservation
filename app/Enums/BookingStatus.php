<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает подтверждения',
            self::Active => 'Подтверждена',
            self::Cancelled => 'Отменена',
        };
    }
}
