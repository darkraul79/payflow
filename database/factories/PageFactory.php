<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->word();

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

    public function basica(): static
    {
        return $this->state([
            'blocks' => [
                [
                    'type' => 'basico',
                    'data' => [
                        'subtitle' => 'Somos transparentes',
                        'title' => 'Transparencia',
                        'text' => '<p>Página de prueba para donación en banner<p>',
                    ],
                ],
            ],
        ]);
    }

    public function withDonacion(): static
    {
        return $this->state([
            'layout' => 'donacion',
        ]);
    }
}
