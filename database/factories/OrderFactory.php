<?php

namespace Database\Factories;

use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Events\CreateOrderEvent;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $shippingMethod = ShippingMethod::inRandomOrder()->first();

        if (! $shippingMethod) {
            $shippingMethod = ShippingMethod::factory()->create();
        }

        return [
            'number' => generateOrderNumber(),
            'shipping' => $shippingMethod->name,
            'shipping_cost' => $shippingMethod->price,
            'subtotal' => $this->faker->randomFloat(2, 1, 100),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'payment_method' => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Order $pedido) {})->afterCreating(function (Order $pedido) {

            if ($pedido->addresses()->count() === 0) {
                $address = Address::factory()->create([
                    'type' => AddressType::BILLING->value,
                ]);
                $pedido->addresses()->attach($address);
            }
            CreateOrderEvent::dispatch($pedido);

        });
    }

    public function pagado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderStatus::PAGADO->value,
            ]);
        });
    }

    public function enviado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderStatus::ENVIADO->value,
            ]);
        });
    }

    public function finalizado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderStatus::FINALIZADO->value,
            ]);
        });
    }

    public function error(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderStatus::ERROR->value,
            ]);
        });
    }

    public function cancelado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderStatus::CANCELADO->value,
            ]);
        });
    }

    public function withDirecionEnvio($params = null): Factory
    {
        return $this->afterCreating(function (Order $pedido) use ($params) {
            $address = Address::factory()->create([
                'type' => AddressType::SHIPPING->value,
                ...$params ?? [],
            ]);
            $pedido->addresses()->attach($address);
        });
    }

    public function withCertificado(): Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $address = Address::factory()->create([
                'type' => AddressType::CERTIFICATE->value,
            ]);
            $pedido->addresses()->attach($address);
        });
    }

    public function withDireccion($params = null): Factory
    {

        return $this->afterCreating(function (Order $pedido) use ($params) {
            $address = Address::factory()->create([
                'type' => AddressType::BILLING->value,
                ...$params ?? [],
            ]);
            $pedido->addresses()->attach($address);
        });
    }

    public function withDirecciones($billingAddress = null, $shippingAddress = null): Factory
    {

        return $this
            ->has(Address::factory()->state([
                'type' => AddressType::BILLING->value,
                ...$billingAddress ?? [],
            ]), 'addresses')
            ->has(Address::factory()->state([
                'type' => AddressType::SHIPPING->value,
                ...$shippingAddress ?? [],
            ]), 'addresses');
    }

    public function withProductos(Collection|Product|int|null $items = null): Factory
    {
        if (is_int($items)) {
            $total = Product::all()->count();
            if ($total <= $items) {
                Product::factory()->count($items - $total)->create();

                return $this->afterCreating(function (Order $pedido) {
                    $sutotalOrder = 0;
                    foreach (Product::all() as $product) {
                        $sutotalOrder += $product->price;
                        $pedido->items()->create([
                            'product_id' => $product->id,
                            'quantity' => 1,
                            'subtotal' => $product->price,
                            'data' => $product->toArray(),
                        ]);
                    }
                    $pedido->update([
                        'subtotal' => $sutotalOrder,
                        'amount' => $sutotalOrder + $pedido->shipping_cost,
                    ]);
                });
            }

        }

        if (is_a($items, Product::class)) {

            return $this->afterCreating(function (Order $pedido) use ($items) {
                $sutotalOrder = $items->price;
                $pedido->items()->create([
                    'product_id' => $items->id,
                    'quantity' => 1,
                    'subtotal' => $items->price,
                    'data' => $items->toArray(),
                ]);
                $pedido->update([
                    'subtotal' => $sutotalOrder,
                    'amount' => $sutotalOrder + $pedido->shipping_cost,
                ]);
            });
        }

        // Compruebo que es una coleccion de productos
        if (is_a($items, \Illuminate\Database\Eloquent\Collection::class)) {
            return $this->afterCreating(function (Order $pedido) use ($items) {
                $sutotalOrder = 0;
                foreach ($items as $product) {
                    $sutotalOrder += $product->price;
                    $pedido->items()->create([
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'subtotal' => $product->price,
                        'data' => $product->toArray(),
                    ]);
                }
                $pedido->update([
                    'subtotal' => $sutotalOrder,
                    'amount' => $sutotalOrder + $pedido->shipping_cost,
                ]);
            });
        }

        return $this->afterCreating(function (Order $pedido) {

            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            if (Product::count() == 0) {
                Product::factory()->create();
            }
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $product = Product::inRandomOrder()->first();
            $sutotalOrder = $product->price;
            $pedido->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'subtotal' => $product->price,
                'data' => $product->toArray(),
            ]);
            $pedido->update([
                'subtotal' => $sutotalOrder,
                'amount' => $sutotalOrder + $pedido->shipping_cost,
            ]);
        });

    }

    public function porBizum(): Factory
    {
        return $this->state(function () {
            return [
                'payment_method' => 'bizum',
            ];
        });
    }
}
