<?php

namespace App\Enums;

enum AddressType: string
{
    case BILLING = 'Facturación';
    case SHIPPING = 'Envío';
    case CERTIFICATE = 'Certificado';

    /**
     * Obtiene todos los tipos como array de valores
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtiene el icono asociado al tipo de dirección
     */
    public function icon(): string
    {
        return match ($this) {
            self::BILLING => 'bi-receipt',
            self::SHIPPING => 'bi-truck',
            self::CERTIFICATE => 'bi-file-earmark-text',
        };
    }

    /**
     * Obtiene el color asociado al tipo de dirección
     */
    public function color(): string
    {
        return match ($this) {
            self::BILLING => 'primary',
            self::SHIPPING => 'success',
            self::CERTIFICATE => 'info',
        };
    }
}
