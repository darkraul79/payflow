<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('compruebo campos requeridos en CreateProduct: name, stock y price', function () {
    asUser();

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => '',
            'stock' => null,
            'price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'stock' => 'required',
            'price' => 'required',
        ]);
});

it('offer_price es requerido cuando oferta es true', function () {
    asUser();

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Producto Test',
            'stock' => 5,
            'price' => 10.00,
            'oferta' => true,
            'offer_price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'offer_price' => 'required',
        ]);
});

it('offer_price no es requerido cuando oferta es false', function () {
    actingAs(User::factory()->create());

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Producto Test 2',
            'stock' => 3,
            'price' => 8.50,
            'oferta' => false,
            // sin offer_price
            'slug' => 'producto-test-2',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::query()->where('name', 'Producto Test 2')->exists())->toBeTrue();
});

it('un usuario autenticado puede acceder al listado de productos y un invitado es redirigido a login', function () {
    // Invitado -> redirige a login del panel
    $this->get(ProductResource::getUrl('index'))
        ->assertRedirectContains('/admin/login');

    // Autenticado (usuario permitido por el panel) -> 200 OK
    asUser();
    $this->get(ProductResource::getUrl('index'))->assertOk();
});

it('ListProducts permite buscar por nombre y ordenar por nombre', function () {
    asUser();

    $pA = Product::factory()->create(['name' => 'AAA '.Str::random(4), 'price' => 5, 'stock' => 2]);
    $pB = Product::factory()->create(['name' => 'BBB '.Str::random(4), 'price' => 7, 'stock' => 4]);
    $pC = Product::factory()->create(['name' => 'CCC '.Str::random(4), 'price' => 9, 'stock' => 1]);

    // Sin filtros, debe poder ver los registros
    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$pA, $pB, $pC])
        // Buscar por nombre del segundo
        ->searchTable(explode(' ', $pB->name)[0])
        ->assertCanSeeTableRecords([$pB])
        ->assertCanNotSeeTableRecords([$pA, $pC])
        // Ordenar por nombre asc/desc
        ->sortTable('name')
        ->sortTable('name', 'desc');
});

it('EditProduct verifica campos requeridos en edición', function () {
    actingAs(User::factory()->create());

    $product = Product::factory()->create([
        'name' => 'Inicial',
        'stock' => 10,
        'price' => 12.34,
    ]);

    livewire(EditProduct::class, ['record' => $product->getKey()])
        ->fillForm([
            'name' => '',
            'stock' => null,
            'price' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'stock' => 'required',
            'price' => 'required',
        ]);
});
