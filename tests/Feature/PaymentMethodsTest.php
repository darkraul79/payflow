<?php

use App\Helpers\RedsysAPI;
use App\Livewire\CardProduct;
use App\Livewire\FinishOrderComponent;
use App\Livewire\PageCartComponent;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Support\PaymentMethodRepository;

use function Pest\Livewire\livewire;

function finalizarCompra(): void
{
    // Creo un producto
    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 2,
    ]);

    // Creo un metodo de envio
    $metodoEnvio = $shippingMethod ?? ShippingMethod::factory()->create();

    // Añado el producto al carrito
    livewire(CardProduct::class, [
        'product' => $producto,
        'quantity' => 1,
    ])->call('addToCart');

    // Selecciono el metodo de envío en el carrito y envío
    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodoEnvio->id)
        ->call('submit');
}

test('puedo ver los métodos de pago Tarjeta y Bizum en Finalizar Compra', function () {
    finalizarCompra();
    livewire(FinishOrderComponent::class)
        ->assertSee('tarjeta')
        ->assertSee('bizum');
});

test('no puedo finalizar compra sin seleccionar método de pago', function () {
    finalizarCompra();
    livewire(FinishOrderComponent::class)
        ->call('submit')
        ->assertHasErrors(['payment_method']);
});

test('si selecciono bizum se crea un parametro en redsys Ds_Merchant_Paymethods a z', function () {
    finalizarCompra();

    $billingDetails = [
        'name' => 'Juan',
        'last_name' => 'Pérez',
        'last_name2' => 'Sánchez',
        'company' => 'Mi empresa',
        'address' => 'Calle Falsa 123',
        'province' => 'Madrid',
        'city' => 'Madrid',
        'cp' => '28001',
        'email' => 'info@raulsebastian.es',
    ];

    $p = livewire(FinishOrderComponent::class)
        ->set('payment_method', 'bizum')
        ->set(['billing' => $billingDetails])
        ->call('submit')
        ->assertHasNoErrors();

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode((new RedsysAPI)->decodeMerchantParameters($p->MerchantParameters), true);

    expect($params['Ds_Merchant_Paymethods'])->toBe('z');

});

test('si selecciono tarjeta NO se crea un parametro en redsys Ds_Merchant_Paymethods', function () {
    finalizarCompra();

    $billingDetails = [
        'name' => 'Juan',
        'last_name' => 'Pérez',
        'last_name2' => 'Sánchez',
        'company' => 'Mi empresa',
        'address' => 'Calle Falsa 123',
        'province' => 'Madrid',
        'city' => 'Madrid',
        'cp' => '28001',
        'email' => 'info@raulsebastian.es',
    ];

    $p = livewire(FinishOrderComponent::class)
        ->set('payment_method', 'tarjeta')
        ->set(['billing' => $billingDetails])
        ->call('submit')
        ->assertHasNoErrors();

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode((new RedsysAPI)->decodeMerchantParameters($p->MerchantParameters), true);

    expect($params)->not->toHaveKey('Ds_Merchant_Paymethods');

});

test('al crear pedido con pago bizum lo reflejo en base de datos', function () {
    finalizarCompra();

    $billingDetails = [
        'name' => 'Juan',
        'last_name' => 'Pérez',
        'last_name2' => 'Sánchez',
        'company' => 'Mi empresa',
        'address' => 'Calle Falsa 123',
        'province' => 'Madrid',
        'city' => 'Madrid',
        'cp' => '28001',
        'email' => 'info@raulsebastian.es',
    ];

    livewire(FinishOrderComponent::class)
        ->set('payment_method', 'bizum')
        ->set(['billing' => $billingDetails])
        ->call('submit')
        ->assertHasNoErrors();

    expect(Order::first()->payment_method)->toBe('bizum');
});

test('puedo obtener el icono de los métodos de pago', function () {
    $metodos = (new PaymentMethodRepository)->getPaymentsMethods(false)->toArray();

    expect(array_column($metodos, 'icon'))->toHaveCount(2);
});
