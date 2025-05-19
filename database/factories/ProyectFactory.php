<?php

namespace Database\Factories;

use App\Models\Proyect;
use Database\Factories\Traits\withDonaciones;
use Database\Factories\Traits\withImages;
use Database\Factories\Traits\withPublished;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProyectFactory extends Factory
{

    use withDonaciones, withImages, withPublished;

    protected $model = Proyect::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'content' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'resume' => $this->faker->word(),
            'donacion' => false,
            'published' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
