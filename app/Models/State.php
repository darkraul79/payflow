<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperState
 */
class State extends Model
{
    use HasFactory;

    public const string PENDIENTE = 'Pendiente de pago';

    public const string PAGADO = 'Pagado';

    public const string ENVIADO = 'Enviado';

    public const string FINALIZADO = 'Finalizado';

    public const string ERROR = 'ERROR';

    public const string CANCELADO = 'Cancelado';

    public const string ACEPTADO = 'Aceptado';

    public const string ACTIVA = 'Activa';

    protected $fillable = [
        'stateable_id',
        'stateable_type',
        'name',
        'message',
        'info',
        'created_at',
        'updated_at',
    ];

    public function stateable(): MorphTo
    {
        return $this->morphTo();
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
            self::FINALIZADO, self::ACTIVA => 'bi-check',
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
            self::PAGADO => 'success',
            self::ACTIVA => 'green',
            self::ENVIADO => 'secondary',
            self::FINALIZADO => 'info',
            self::CANCELADO, self::ERROR => 'danger',
            default => 'primary',
        };
    }

    public function fechaHumanos(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    protected function casts(): array
    {
        return [
            'info' => AsArrayObject::class,
        ];
    }
}
