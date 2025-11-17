<?php

namespace App\Enums;

enum DonationFrequency: string
{
    case MENSUAL = 'Mensual';
    case TRIMESTRAL = 'Trimestral';
    case ANUAL = 'Anual';

    /**
     * Obtiene todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtiene un array compatible con el formato antiguo Donation::FREQUENCY
     */
    public static function toArray(): array
    {
        return [
            'MENSUAL' => self::MENSUAL->value,
            'TRIMESTRAL' => self::TRIMESTRAL->value,
            'ANUAL' => self::ANUAL->value,
        ];
    }

    /**
     * Obtiene el icono asociado a la frecuencia
     */
    public function icon(): string
    {
        return match ($this) {
            self::MENSUAL => 'bi-calendar-month',
            self::TRIMESTRAL => 'bi-calendar3',
            self::ANUAL => 'bi-calendar-year',
        };
    }

    /**
     * Obtiene el color asociado a la frecuencia
     */
    public function color(): string
    {
        return match ($this) {
            self::MENSUAL => 'primary',
            self::TRIMESTRAL => 'success',
            self::ANUAL => 'warning',
        };
    }

    /**
     * Obtiene la descripción legible
     */
    public function description(): string
    {
        return match ($this) {
            self::MENSUAL => 'Donación mensual',
            self::TRIMESTRAL => 'Donación trimestral',
            self::ANUAL => 'Donación anual',
        };
    }

    /**
     * Obtiene el número de meses entre pagos
     */
    public function months(): int
    {
        return match ($this) {
            self::MENSUAL => 1,
            self::TRIMESTRAL => 3,
            self::ANUAL => 12,
        };
    }
}
