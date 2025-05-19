<?php

namespace Database\Factories;

use App\Models\Activity;
use Database\Factories\Traits\withDonaciones;
use Database\Factories\Traits\withImages;
use Database\Factories\Traits\withPublished;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ActivityFactory extends Factory
{
    use withDonaciones, withImages, withPublished;

    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->word(),
            'resume' => $this->faker->word(),
            'date' => $this->faker->dateTime(),
            'address' => $this->faker->address(),
            'donacion' => false,
            'published' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
