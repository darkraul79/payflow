<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderStateFactory extends Factory
{
    protected $model = OrderState::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(Order::getStates()),
            'message' => $this->faker->word(),
            'info' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'order_id' => Order::factory(),
        ];
    }
}
