<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderState extends Model
{
    use HasFactory;

    public const PENDIENTE = 'Pendiente de pago';

    public const PAGADO = 'Pagado';

    public const ENVIADO = 'Enviado';

    public const FINALIZADO = 'Finalizado';

    public const ERROR = 'ERROR';

    public const CANCELADO = 'Cancelado';

    protected $fillable = [
        'order_id',
        'name',
        'message',
        'info',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Devuelve el icono asociado al estado de pedido
     */
    public function icono(): string
    {
        return match ($this->nombre) {
            self::PENDIENTE => 'bi-cash-coin',
            self::PAGADO => 'bi-credit-card',
            self::ENVIADO => 'bi-truck',
            self::FINALIZADO => 'bi-check',
            self::ERROR => 'bi-exclamation-circle-fill',
            self::CANCELADO => 'bi-ban',
            default => 'bi-hash',
        };
    }

    /**
     * Devuelve el icono asociado al estado de pedido
     */
    public function colorEstado(): string
    {
        return match ($this->nombre) {
            self::PENDIENTE => 'warning',
            self::PAGADO => 'info',
            self::ENVIADO => 'secondary',
            self::FINALIZADO => 'success',
            self::CANCELADO => 'danger',
            default => 'primary',
        };
    }


}
