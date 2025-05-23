<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {

        return [
            'number' => fake()->unique()->randomNumber(4),
            'shipping' => 'Precio fijo',
            'shipping_cost' => 3.5,
            'subtotal' => $this->faker->randomFloat(2, 1, 100),
            'total' => $this->faker->randomFloat(2, 1, 100),
            'taxes' => $this->faker->randomFloat(),
            'payment_method' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Order $pedido) {
            $pedido->states()->make([
                'name' => OrderState::PENDIENTE,
            ]);
            OrderAddress::factory()->make([
                'type' => OrderAddress::BILLING,
                'order_id' => $pedido->id,
            ]);
        })->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::PENDIENTE,
            ]);
            OrderAddress::factory()->create([
                'type' => OrderAddress::BILLING,
                'order_id' => $pedido->id,
            ]);

        });
    }


    public function pagado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::PAGADO,
            ]);
        });
    }

    public function enviado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::ENVIADO,
            ]);
        });
    }

    public function finalizado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::FINALIZADO,
            ]);
        });
    }

    public function error(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::ERROR,
            ]);
        });
    }

    public function cancelado(): OrderFactory|Factory
    {
        return $this->afterCreating(function (Order $pedido) {
            $pedido->states()->create([
                'name' => OrderState::CANCELADO,
            ]);
        });
    }

    public function withDirecionEnvio()
    {
        return $this->afterMaking(function (Order $pedido) {
            OrderAddress::factory()->create([
                'type' => OrderAddress::BILLING,
                'order_id' => $pedido->id,
            ]);
        })->afterCreating(function (Order $pedido) {
            OrderAddress::factory()->create([
                'type' => OrderAddress::BILLING,
                'order_id' => $pedido->id,
            ]);
        });
    }
}
