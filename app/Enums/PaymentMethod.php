<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BIZUM = 'bizum';
    case TARJETA = 'tarjeta';

    public function label(): string
    {
        return match ($this) {
            self::BIZUM => 'bizum',
            self::TARJETA => 'tarjeta',
        };
    }

    public function supportsRecurring(): bool
    {
        return match ($this) {
            self::TARJETA => true,
            self::BIZUM => false,
        };
    }
}
