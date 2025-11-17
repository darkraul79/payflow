<?php

namespace App\Models;

use App\Enums\OrderStatus;
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
        $status = $this->status();

        return $status?->icon() ?? 'bi-question-circle';
    }

    /**
     * Obtiene el enum OrderStatus correspondiente al estado actual
     */
    public function status(): ?OrderStatus
    {
        return OrderStatus::tryFrom($this->name);
    }

    /**
     * Devuelve el color asociado al estado de pedido
     */
    public function colorEstado(): string
    {
        $status = $this->status();

        return $status?->color() ?? 'primary';
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
