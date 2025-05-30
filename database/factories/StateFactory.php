<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(Order::getStates()),
            'message' => $this->faker->word(),
            'info' => [
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],

        ];
    }
}
