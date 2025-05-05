<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'tile' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->word(),
            'resume' => $this->faker->word(),
            'donacion' => $this->faker->boolean(),
            'published' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
