<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDIENTE = 'Pendiente de pago';
    case PAGADO = 'Pagado';
    case ENVIADO = 'Enviado';
    case FINALIZADO = 'Finalizado';
    case ERROR = 'ERROR';
    case CANCELADO = 'Cancelado';
    case ACEPTADO = 'Aceptado';
    case ACTIVA = 'Activa';

    /**
     * Obtiene todos los estados como array de valores
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtiene todos los estados disponibles para pedidos
     */
    public static function orderStates(): array
    {
        return [
            self::PENDIENTE->value,
            self::PAGADO->value,
            self::ENVIADO->value,
            self::FINALIZADO->value,
            self::ERROR->value,
            self::CANCELADO->value,
        ];
    }

    /**
     * Obtiene todos los estados disponibles para donaciones
     */
    public static function donationStates(): array
    {
        return [
            self::PENDIENTE->value,
            self::PAGADO->value,
            self::ACTIVA->value,
            self::ERROR->value,
            self::CANCELADO->value,
        ];
    }

    /**
     * Obtiene el icono asociado al estado
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'bi-clock-history',
            self::PAGADO => 'bi-check-circle',
            self::ENVIADO => 'bi-truck',
            self::FINALIZADO => 'bi-check-all',
            self::ERROR => 'bi-exclamation-triangle',
            self::CANCELADO => 'bi-x-circle',
            self::ACEPTADO => 'bi-check',
            self::ACTIVA => 'bi-play-circle',
        };
    }

    /**
     * Obtiene el color asociado al estado
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::PAGADO, self::ACEPTADO => 'success',
            self::ACTIVA => 'green',
            self::ENVIADO => 'secondary',
            self::FINALIZADO => 'info',
            self::CANCELADO, self::ERROR => 'danger',
        };
    }

    /**
     * Obtiene el subject del email para este estado
     */
    public function emailSubject(): string
    {
        return match ($this) {
            self::PENDIENTE => '📩 Tu pedido está pendiente de pago',
            self::PAGADO => '📦 Tu pedido está en preparación 💛',
            self::ENVIADO => '¡🚚 Tu pedido ya está en camino!',
            self::FINALIZADO => '¡Gracias por subirte a la ola solidaria! 🌊',
            self::ERROR => '⚠️ Atención: problema con tu pedido',
            self::CANCELADO => '❌ Pedido cancelado',
            self::ACEPTADO => '✅ Pedido aceptado',
            self::ACTIVA => '🟢 Estado activo',
        };
    }

    /**
     * Obtiene la vista de email para este estado
     */
    public function emailView(): string
    {
        return match ($this) {
            self::PENDIENTE => 'emails.order-pending',
            self::PAGADO => 'emails.order-paid',
            self::ENVIADO => 'emails.order-shipped',
            self::FINALIZADO => 'emails.order-completed',
            self::ERROR => 'emails.order-error',
            self::CANCELADO => 'emails.order-cancel',
            self::ACEPTADO => 'emails.order-accepted',
            self::ACTIVA => 'emails.donation-active',
        };
    }
}
