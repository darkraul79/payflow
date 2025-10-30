<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'price' => $this->faker->randomFloat(),
            'active' => true,
            'from' => null,
            'until' => null,
            'greater' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function hasDates($from = null, $until = null): Factory
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(2);
        $until = $until ? Carbon::parse($until) : Carbon::now()->addDays(2);

        return $this->state(function () use ($from, $until) {
            return [
                'from' => $from->format('Y-m-d'),
                'until' => $until->format('Y-m-d'),
            ];
        });
    }

    public function hasGreater(?float $amount = null): Factory
    {
        return $this->state(function () use ($amount) {
            return [
                'greater' => $amount ?? $this->faker->randomFloat(),
            ];
        });
    }

    public function hidden(): Factory
    {
        return $this->state(function () {
            return [
                'active' => false,
            ];
        });
    }
}
