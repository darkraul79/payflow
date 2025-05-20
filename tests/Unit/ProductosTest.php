<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Product;
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
