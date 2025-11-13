<?php

namespace Database\Factories;

use App\Models\News;
use Database\Factories\Traits\withDonaciones;
use Database\Factories\Traits\withImages;
use Database\Factories\Traits\withPublished;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NewsFactory extends Factory
{
    use withDonaciones, withImages, withPublished;

    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->word(),
            'resume' => $this->faker->word(),
            'donacion' => false,
            'published' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
