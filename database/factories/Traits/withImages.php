<?php

namespace Database\Factories\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait withImages
{
    public function imagen(string|array $image): Factory
    {
        return $this->afterCreating(function ($product) use ($image) {
            $images = is_array($image) ? $image : [$image];

            foreach ($images as $img) {
                $product->addMedia($img)
                    ->preservingOriginal()
                    ->toMediaCollection('principal');
            }
        });
    }

    public function galeria(string|array $image): Factory
    {
        return $this->afterCreating(function ($page) use ($image) {
            $images = is_array($image) ? $image : [$image];

            foreach ($images as $img) {
                $page->addMedia($img)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            }
        });
    }
}
