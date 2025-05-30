<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Order;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {

        return [
            'number' => generateOrderNumber(),
            'shipping' => 'Precio fijo',
            'shipping_cost' => 3.5,
            'subtotal' => $this->faker->randomFloat(2, 1, 100),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'taxes' => $this->faker->randomFloat(),
            'payment_method' => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Order $pedido) {
        })->afterCreating(function (Order $pedido) {

            $address = Address::factory()->create([
                'type' => ADDRESS::BILLING,
            ]);
            $pedido->addresses()->attach($address);

        });
    }

    public function pagado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => State::PAGADO,
            ]);
        });
    }

    public function enviado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => State::ENVIADO,
            ]);
        });
    }

    public function finalizado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => State::FINALIZADO,
            ]);
        });
    }

    public function error(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => State::ERROR,
            ]);
        });
    }

    public function cancelado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => State::CANCELADO,
            ]);
        });
    }

    public function withDirecionEnvio(): Factory
    {
        return $this->has(Address::factory()->state([
            'type' => Address::SHIPPING,
        ]), 'addresses');
    }

    public function withCertificado(): Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $address = Address::factory()->create([
                'type' => ADDRESS::CERTIFICATE,
            ]);
            $pedido->addresses()->attach($address);
        });
    }
}
