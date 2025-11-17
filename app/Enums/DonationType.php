<?php

namespace App\Enums;

enum DonationType: string
{
    case UNICA = 'Simple';
    case RECURRENTE = 'Recurrente';

    /**
     * Obtiene el icono asociado al tipo de donación
     */
    public function icon(): string
    {
        return match ($this) {
            self::UNICA => 'bi-heart',
            self::RECURRENTE => 'bi-arrow-repeat',
        };
    }

    /**
     * Obtiene el color asociado al tipo de donación
     */
    public function color(): string
    {
        return match ($this) {
            self::UNICA => 'primary',
            self::RECURRENTE => 'success',
        };
    }

    /**
     * Obtiene la descripción del tipo de donación
     */
    public function description(): string
    {
        return match ($this) {
            self::UNICA => 'Donación única',
            self::RECURRENTE => 'Donación recurrente',
        };
    }

    /**
     * Obtiene todos los tipos como array de valores
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
