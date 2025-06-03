<?php

namespace App\Models;

use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasPayments;
use App\Models\Traits\HasStates;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property mixed $items
 *
 * @method where
 *
 * @property mixed $amount
 * @property mixed $totalRedsys
 */
#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasAddresses, HasFactory, HasPayments, HasStates;

    protected $fillable = [
        'number',
        'shipping',
        'shipping_cost',
        'subtotal',
        'amount',
        'taxes',
        'payment_method',
    ];

    protected $with = [
        'items.product',
        'addresses',
    ];


    public function fechaHumanos(): string
    {

        return Carbon::parse($this->created_at)->diffForHumans();
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
        //        dd($this->states);
        return $this->states
            ->prepend(State::make([

                'name' => 'Recibido',
                'stateable_id' => $this->id,
                'stateable_type' => self::class,
                'created_at' => $this->created_at,
                'updated_at' => $this->created_at,
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
        unset($estados[State::ACEPTADO]); // no se puede asignar el estado pendiente

        return array_flip($estados);
    }

    public function error($mensaje, $redSysResponse): void
    {
        $estado = [
            'name' => State::ERROR,
        ];

        if (!$this->states()->where($estado)->exists()) {

            $estado['info'] = $redSysResponse;
            $estado['info']['Error'] = $mensaje ?? 'Error al procesar el pedido';

            $this->states()->create($estado);

        }

        $this->refresh();

    }

    public function payed(array $redSysResponse): void
    {

        $this->payments->where('number', $redSysResponse['Ds_Order'])->firstOrFail()->update(
            [
                'amount' => convertPriceFromRedsys($redSysResponse['Ds_Amount']),
                'info' => $redSysResponse,

            ]);

        // Si no existe el estado PAGADO, lo creo
        if (!$this->states()->where('name', State::PAGADO)->exists()) {
            // resto la cantidad al stock de los productos
            $this->subtractStocks();
            $this->states()->create([
                'name' => State::PAGADO,
                'info' => $redSysResponse,
            ]);

        }

        $this->refresh();

        // Disparo evento de actualización de pedido
        //        OrderUpdated::dispatch($this);
    }

    /**
     * Resta la cantidad de los productos del pedido al stock.
     */
    public function subtractStocks(): void
    {
        foreach ($this->items as $item) {
            $product = $item->product;

            $stock = $product->stock -= $item->quantity;

            if ($product->stock < 0) {
                $stock = 0;
            }

            $product->update([
                'stock' => $stock,
            ]);
        }

    }

    public function billing_address(): Address
    {
        // Devuelve la dirección de facturación del pedido
        return $this->addresses()->where('type', Address::BILLING)->latest()->first();
    }

    public function shipping_address(): Address|null
    {
        // Devuelve la dirección de facturación del pedido
        return $this->addresses()->where('type', Address::SHIPPING)->get()->first() ?? null;
    }

    /**
     * Devuelve la vista de resultado del pedido según su estado.
     */
    public function getResultView(): string
    {
        if ($this->state->name === State::ERROR) {
            return 'cart.ko';
        } else {
            return 'cart.ok';
        }
    }

    public function getStaticViewParams(): array
    {
        return [
            'page' => Page::factory()->make([
                'title' => 'Pedido',
                'is_home' => false,
                'donation' => false,
                'parent_id' => Page::where('slug', 'tienda-solidaria')->first() ?? null,
            ]),
            'static' => true,
        ];
    }

    public function itemsArray(): array
    {
        // Devuelve un array con los items del pedido
        $items = [];
        foreach ($this->items as $item) {
            $items[] = [
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->product->getFormatedPriceWithDiscount(),
                'subtotal' => convertPrice($item->subtotal),
                'image' => $item->product->getFirstMedia('product_images')->getPath('thumb') ?? '',
            ];
        }

        return $items;
    }

    /**
     * Devuelve el total del pedido formateado para Redsys.
     */
    protected function totalRedsys(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::replace('.', '', number_format($this->attributes['amount'], 2)),
        );
    }
}
