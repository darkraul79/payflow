<?php

namespace Database\Factories;

use App\Models\Blockquote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BlockquoteFactory extends Factory
{
    protected $model = Blockquote::class;

    public function definition(): array
    {
        return [
            'text' => fake()->unique()->sentence(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
