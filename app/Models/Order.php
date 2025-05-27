<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * @property mixed $items
 * @property mixed $total
 * @property mixed $totalRedsys
 */
#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'shipping',
        'shipping_cost',
        'subtotal',
        'total',
        'taxes',
        'payment_method',
    ];

    protected $with = [
        'state',
        'items.product',
        'addresses',
    ];

    public function state(): HasOne
    {
        return $this->hasOne(OrderState::class)->latestOfMany();
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->addresses()->where('type', OrderAddress::BILLING)->first();
            }
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function fechaHumanos(): string
    {

        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function billing_adress()
    {
        return $this->addresses()->where('type', OrderAddress::BILLING)->first();
    }

    public function shipping_adress()
    {
        return $this->addresses()->where('type', OrderAddress::SHIPPING)->first();
    }

    public function Items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images(): Collection
    {
        $data = collect();
        foreach ($this->items as $item) {
            if ($item->product->getMedia('product_images')) {
                $data->push($item->product->getMedia('product_images'));
            }
        }

        return $data;
    }

    public function statesWithStateInitial(): Collection
    {
        // devuelvo los estados y agrega un estado de reccibido
        return $this->states->prepend(OrderState::make([

            'name' => 'Recibido',
            'order_id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->created_at,
            'info' => null,
            'message' => null,

        ]));

    }

    /**
     *  Devuelve los estados disponibles de un pedido, sin contar los ya asignados
     */
    public function available_states(): array
    {

        $estados = array_flip(self::getStates());

        foreach ($this->states as $estadoYaUsado) {
            unset($estados[$estadoYaUsado->name]);
        }

        return array_flip($estados);
    }

    public static function getStates(): array
    {
        $reflector = new ReflectionClass(OrderState::class);
        $constants = $reflector->getConstants();

        unset($constants['CREATED_AT']);
        unset($constants['UPDATED_AT']);

        return $constants;
    }

    public function error($mensaje = null): void
    {
        $estado = [
            'name' => OrderState::ERROR,
        ];

        if (!$this->states()->where($estado)->exists()) {
            $estado['info'] = $mensaje;
            $state = $this->states()->create($estado);
            $this->refresh();

        }

        // Disparo evento de actualización de pedido
        //            PedidoActualizadoEvent::dispatch($this);
    }

    public function states(): HasMany
    {
        return $this->hasMany(OrderState::class);
    }

    public function payed($mensaje = null): void
    {
        if (!$this->states()->where('name', OrderState::PAGADO)->exists()) {

            // resto la cantidad al stock de los productos
            $this->subtractStocks();

            $this->states()->create([
                'name' => OrderState::PAGADO,
                'info' => json_encode($mensaje),
            ]);
            $this->refresh();
        }

        // Disparo evento de actualización de pedido
        //            PedidoActualizadoEvent::dispatch($this);
    }

    /**
     * Resta la cantidad de los productos del pedido al stock.
     */
    public function subtractStocks(): void
    {
        foreach ($this->items as $item) {
            $product = $item->product;
            $product->stock -= $item->quantity;

            if ($product->stock < 0) {
                $product->stock = 0;
            }

            $product->save();
        }

    }

    /**
     * Delimita el listado de pedidos a los finalizados.
     */
    #[Scope]
    protected function finalizados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::FINALIZADO);
        });
    }

    /**
     * Delimita el listado de pedidos a los pendientes de pago.
     */
    #[Scope]
    protected function pendientePago(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::PENDIENTE);
        });
    }

    /**
     * Delimita el listado de pedidos a los pedidos cancelados.
     */
    #[Scope]
    protected function cancelados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::CANCELADO);
        });
    }

    /**
     * Delimita el listado de pedidos a los pagados.
     */
    #[Scope]
    protected function pagados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::PAGADO);
        });
    }

    /**
     * Delimita el listado de pedidos a los pedidos enviados.
     */
    #[Scope]
    protected function enviados(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::ENVIADO);
        });
    }

    /**
     * Delimita el listado de pedidos a los pedidos con error.
     */
    #[Scope]
    protected function conErrores(Builder $query): void
    {
        $query->whereHas('state', function ($query): void {
            $query->where('name', OrderState::ERROR);
        });
    }

    protected function totalRedsys(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::replace('.', '', number_format($this->attributes['total'], 2)),
        );
    }
}
