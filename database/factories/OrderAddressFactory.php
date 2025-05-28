<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderAddress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderAddressFactory extends Factory
{
    protected $model = OrderAddress::class;

    public function definition(): array
    {
        return [
            'type' => OrderAddress::BILLING,
            'name' => $this->faker->name(),
            'last_name' => $this->faker->lastName(),
            'company' => $this->faker->company(),
            'nif' => '123456798A',
            'address' => $this->faker->address(),
            'province' => $this->faker->city(),
            'city' => $this->faker->city(),
            'cp' => $this->faker->regexify('[2-4]{1}[0-9]{4}'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'order_id' => Order::factory(),
        ];
    }
}
