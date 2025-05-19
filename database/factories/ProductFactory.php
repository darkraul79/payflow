<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => $this->faker->name(),
            'slug' => Str::slug($name),
            'price' => $this->faker->randomFloat(min: 1, max: 50, nbMaxDecimals: 2),
            'stock' => $this->faker->randomNumber(),
            'description' => $this->faker->text(),
            'offer_price' => null,
            'published' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function hasOffer($price): Factory
    {
        return $this->state(function (array $attributes) use ($price) {
            return [
                'offer_price' => $price,
            ];
        });
    }

    public function hidden(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'published' => false,
            ];
        });
    }

    public function imagen(string|array $image): Factory
    {
        return $this->afterCreating(function (Product $product) use ($image) {
            $images = is_array($image) ? $image : [$image];

            foreach ($images as $img) {
                $product->addMedia($img)
                    ->preservingOriginal()
                    ->toMediaCollection('product_images');
            }
        });
    }
}
