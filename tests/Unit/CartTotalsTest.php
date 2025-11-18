<?php

use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CartNormalizer;
use App\Services\ShippingSession;
use Darkraul79\Cartify\Facades\Cart;

it('calcula totales con envío', function () {
    Cart::clear();
    $producto = Product::factory()->create(['price' => 12.5]);
    Cart::add($producto->id, $producto->name, 2, $producto->price); // subtotal 25.0
    $metodo = ShippingMethod::factory()->create(['price' => 5.2]);
    ShippingSession::set($metodo);

    $totals = CartNormalizer::totals();

    expect($totals['subtotal'])->toBe(25.0)
        ->and($totals['shipping_cost'])->toBe(5.2)
        ->and($totals['total'])->toBe(30.2)
        ->and($totals['taxes'])->toBe(calculoImpuestos(30.2));
});

it('calcula totales sin envío', function () {
    Cart::clear();
    $producto = Product::factory()->create(['price' => 10]);
    Cart::add($producto->id, $producto->name, 1, $producto->price); // subtotal 10
    ShippingSession::clear();

    $totals = CartNormalizer::totals();

    expect($totals['subtotal'])->toBe(10.0)
        ->and($totals['shipping_cost'])->toBe(0.0)
        ->and($totals['total'])->toBe(10.0)
        ->and($totals['taxes'])->toBe(calculoImpuestos(10.0));
});

it('totales vacíos devuelve ceros', function () {
    Cart::clear();
    ShippingSession::clear();

    $totals = CartNormalizer::totals();

    expect($totals['subtotal'])->toBe(0.0)
        ->and($totals['shipping_cost'])->toBe(0.0)
        ->and($totals['total'])->toBe(0.0)
        ->and($totals['taxes'])->toBe(calculoImpuestos(0.0));
});
