<?php

use App\Models\Product;
use App\Services\CartNormalizer;
use Darkraul79\Cartify\Facades\Cart as Cartify;

it('normaliza items con campos calculados y opciones', function () {
    Cartify::clear();
    $producto = Product::factory()->create([
        'price' => 12.5,
        'stock' => 3,
    ]);
    Cartify::add(
        id: $producto->id,
        name: $producto->name,
        quantity: 2,
        price: $producto->getPrice(),
        options: [
            'image' => $producto->getFirstMediaUrl('product_images', 'thumb'),
        ]
    );

    $items = CartNormalizer::items();
    expect($items)->toHaveCount(1)
        ->and($items[$producto->id]['subtotal'])->toBe(25.0)
        ->and($items[$producto->id])->toHaveKeys(['price_formated', 'subtotal_formated', 'image']);
});

it('normaliza item sin image en options y agrega subtotales', function () {
    Cartify::clear();
    $producto = Product::factory()->create([
        'price' => 5,
        'stock' => 2,
    ]);
    // sin image en options
    Cartify::add(id: $producto->id, name: $producto->name, quantity: 1, price: $producto->getPrice(), options: []);
    $items = CartNormalizer::items();
    expect($items[$producto->id]['subtotal'])->toBe(5.0)
        ->and($items[$producto->id]['price_formated'])->not->toBeEmpty()
        ->and($items[$producto->id])->toHaveKey('subtotal_formated');
});

it('devuelve arreglo vacío cuando no hay items', function () {
    Cartify::clear();
    expect(CartNormalizer::items())->toBeArray()->toHaveCount(0);
});
