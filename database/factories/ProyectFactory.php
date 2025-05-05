<?php

namespace Database\Factories;

use App\Models\Proyect;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProyectFactory extends Factory
{
    protected $model = Proyect::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'content' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'resume' => $this->faker->word(),
            'donacion' => $this->faker->boolean(),
            'published' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
