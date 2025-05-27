<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Livewire\CardProduct;
use App\Livewire\ProductAddCart;
use App\Models\Product;
use App\Services\Cart;
use function Pest\Livewire\livewire;

test('puedo crear Productos', function () {

    Storage::fake('public');

    $producto = Product::factory()->make();

    asUser();

    livewire(CreateProduct::class)
        ->fillForm($producto->getAttributes())
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(Product::count())->toBe(1);
});

test('puedo editar productos', function () {

    Storage::fake('public');

    $producto = Product::factory()->create();

    asUser();

    livewire(EditProduct::class, ['record' => $producto->getRouteKey()])
        ->fillForm([
            'name' => 'Producto editado',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(Product::first()->title)->toBe('Producto editado');
});


test('urlPrefix es correcto', function () {
    expect(Product::factory()->make()->getUrlPrefix())->toBe('/tienda-solidaria/');
});

test('no puedo agregar productos sin stock a carrito', function () {

    $producto = Product::factory()->create([
        'stock' => 0,
    ]);
    $productoStock = Product::factory()->create([
        'stock' => 1,
    ]);

    livewire(ProductAddCart::class, [
        'product' => $productoStock,
    ])
        ->call('addToCart');

    livewire(ProductAddCart::class, [
        'product' => $producto,
    ])
        ->call('addToCart')
        ->assertDispatched('showAlert', 'No hay suficiente stock')
        ->assertNotDispatched('updatedCart');

    expect(Cart::getItems())->toHaveCount(1);

});

test('no puedo agregar más cantidad de productos mayor que el stock en la tarjeta de producto ', function () {
    $producto = Product::factory()->create([
        'stock' => 1,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto);
    livewire(CardProduct::class, [
        'product' => $producto,
    ])->call('addToCart', $producto)
        ->assertDispatched('showAlert', 'No hay suficiente stock')
        ->assertNotDispatched('updatedCart');


    expect(Cart::getQuantityProduct($producto->id))->toBe(1);

});

test('si el producto está en oferta puedo ver el badge porcentaje en listado de productos', function () {

    $producto = Product::factory()->create([
        'price' => 10,
        'offer_price' => 5,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->assertSeeText('Oferta 50 %');
});

test('si el producto no tiene stock puedo ver el badge agotado en listado de productos', function () {

    $producto = Product::factory()->create([
        'stock' => 0,
    ]);

    livewire(CardProduct::class, [
        'product' => $producto,
    ])
        ->assertSeeText('Agotado');
});
