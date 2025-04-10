<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PageContentFactory extends Factory
{
    protected $model = PageContent::class;

    public function definition(): array
    {
        return [
            'type' => fake()->word(),
            'content' => fake()->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'page_id' => Page::factory(),
        ];
    }
}
