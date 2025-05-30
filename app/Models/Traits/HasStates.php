<?php

namespace App\Models\Traits;


use App\Models\State;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use ReflectionClass;

trait HasStates
{


    public static function getStates(): array
    {
        $reflector = new ReflectionClass(State::class);
        $constants = $reflector->getConstants();

        unset($constants['CREATED_AT']);
        unset($constants['UPDATED_AT']);

        return $constants;
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
     * Delimita el listado de modelos a los finalizados.
     */
    #[Scope]
    protected function aceptados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::ACEPTADO);
        });
    }

    /**
     * Delimita el listado de modelos a los finalizados.
     */
    #[Scope]
    protected function finalizados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::FINALIZADO);
        });
    }

    /**
     * Delimita el listado de modelos a los pendientes de pago.
     */
    #[Scope]
    protected function pendientePago(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::PENDIENTE);
        });
    }

    /**
     * Delimita el listado de modelos a los modelps cancelados.
     */
    #[Scope]
    protected function cancelados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::CANCELADO);
        });
    }

    /**
     * Delimita el listado de modelos a los pagados.
     */
    #[Scope]
    protected function pagados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::PAGADO);
        });
    }

    /**
     * Delimita el listado de modelos a los modelps enviados.
     */
    #[Scope]
    protected function enviados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::ENVIADO);
        });
    }

    /**
     * Delimita el listado de modelos a los modelps con error.
     */
    #[Scope]
    protected function conErrores(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', State::ERROR);
        });
    }


}
