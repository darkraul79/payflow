<?php

use App\Livewire\PageCartComponent;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\ShippingSession;
use Darkraul79\Cartify\Facades\Cart;

use function Pest\Livewire\livewire;

it('guarda claves de sesión con dot notation al actualizar totales', function () {
    Cart::clear();
    $producto = Product::factory()->create(['price' => 10]);
    Cart::add($producto->id, $producto->name, 1, $producto->price);
    $metodo = ShippingMethod::factory()->create(['price' => 3.5]);
    ShippingSession::set($metodo);

    livewire(PageCartComponent::class)
        ->assertSessionHas('cart.totals.subtotal', 10.0)
        ->assertSessionHas('cart.totals.shipping_cost', 3.5)
        ->assertSessionHas('cart.totals.total', 13.5)
        ->assertSessionHas('cart.shipping_method.price', 3.5)
        ->assertSessionHas('cart.total_shipping', 3.5);
});

it('clearCart elimina claves de sesión nuevas', function () {
    Cart::clear();
    $producto = Product::factory()->create(['price' => 10]);
    Cart::add($producto->id, $producto->name, 1, $producto->price);
    $metodo = ShippingMethod::factory()->create(['price' => 3.5]);
    ShippingSession::set($metodo);

    $comp = livewire(PageCartComponent::class);
    $comp->call('clearCart')
        ->assertDispatched('updatedCart');

    expect(session()->has('cart.totals.subtotal'))->toBeFalse()
        ->and(session()->has('cart.shipping_method.price'))->toBeFalse()
        ->and(session()->has('cart.total_shipping'))->toBeFalse();
});
