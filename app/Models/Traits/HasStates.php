<?php

namespace App\Models\Traits;

use App\Enums\OrderStatus;
use App\Models\State;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasStates
{
    public static function getStates(): array
    {
        return OrderStatus::values();
    }

    public function state(): MorphOne
    {
        return $this->morphOne(State::class, 'stateable')->latestOfMany();
    }

    public function states(): MorphMany
    {
        return $this->morphMany(State::class, 'stateable');
    }

    /**
     * Delimita el listado de modelos a los aceptados.
     */
    #[Scope]
    public function aceptados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::ACEPTADO->value);
        });
    }

    /**
     * Delimita el listado de modelos a los finalizados.
     */
    #[Scope]
    public function finalizados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::FINALIZADO->value);
        });
    }

    /**
     * Delimita el listado de modelos a los pendientes de pago.
     */
    #[Scope]
    public function pendientePago(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::PENDIENTE->value);
        });
    }

    /**
     * Delimita el listado de modelos a los modelos cancelados.
     */
    #[Scope]
    public function cancelados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::CANCELADO->value);
        });
    }

    /**
     * Delimita el listado de modelos a los pagados.
     */
    #[Scope]
    public function pagados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::PAGADO->value);
        });
    }

    /**
     * Delimita el listado de modelos a los modelos enviados.
     */
    #[Scope]
    public function enviados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::ENVIADO->value);
        });
    }

    /**
     * Delimita el listado de modelos a los modelps con error.
     */
    #[Scope]
    public function conErrores(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::ERROR->value);
        });
    }

    /**
     * Delimita el listado de modelos a los modelos con estado ACTIVA.
     */
    #[Scope]
    public function activas(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderStatus::ACTIVA->value);
        });
    }
}
