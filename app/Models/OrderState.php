<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
        'created_at',
        'updated_at',
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
        return match ($this->name) {
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
        return match ($this->name) {
            self::PENDIENTE => 'warning',
            self::PAGADO => 'info',
            self::ENVIADO => 'secondary',
            self::FINALIZADO => 'success',
            self::CANCELADO => 'danger',
            default => 'primary',
        };
    }

    public function fechaHumanos(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    /**
     * Devuelve true si el campo info es Json
     */
    public function infoIsJson(): bool
    {

        $resultado = json_decode($this->info);

        // Verificamos si el JSON es válido
        // La función json_decode() devuelve null si la cadena no es un JSON válido
        if ($resultado === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return true;
    }


}
