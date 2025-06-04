<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 1, 10),
            'number' => generateOrderNumber(),
            'info' => [
                'transaction_id' => $this->faker->uuid(),
                'payment_method' => $this->faker->word(),
                'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            ],
        ];
    }
}
