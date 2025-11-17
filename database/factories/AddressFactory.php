<?php

namespace Database\Factories;

use App\Enums\AddressType;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'type' => AddressType::BILLING->value,
            'name' => $this->faker->name(),
            'last_name' => $this->faker->lastName(),
            'last_name2' => $this->faker->lastName(),
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
            'notes' => $this->faker->optional()->sentence(),

        ];
    }

    public function certificado(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => AddressType::CERTIFICATE->value,
            ];
        });
    }
}
