<?php

namespace Database\Factories;

use App\Enums\ContentTypes;
use App\Models\Page;
use App\Models\PageContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->word();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'published_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'published_at' => Carbon::now(),
        ]);
    }

    public function isHome(): static
    {
        return $this->state([
            'is_home' => true,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Page $page) {
            PageContent::factory()->make([
                'page_id' => $page->id,
                'type' => ContentTypes::Basic,
                'content' => $this->generateBasicContent(),
            ]);
        })->afterCreating(function (Page $page) {
            PageContent::factory()->create([
                'page_id' => $page->id,
                'type' => ContentTypes::Basic,
                'content' => $this->generateBasicContent(),
            ]);
        });
    }

    public function generateBasicContent(): string
    {
        $content = "<h1>" . $this->faker->text(10) . "</h1>";
        $content .= "<h2>" . $this->faker->text(20) . "</h2>";
        $content .= "<p>" . $this->faker->text(100) . "</p>";
        $content .= "<p>" . $this->faker->text(60) . "</p>";
        return $content;

    }
}
