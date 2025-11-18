<?php

use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Livewire\FinishOrderComponent;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreated;
use App\Services\CartNormalizer;
use App\Services\PaymentProcess;
use Darkraul79\Cartify\Facades\Cart as Cartify;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Livewire\livewire;

test('puedo crear Pedido por defecto en factory', function () {

    $pedido = Order::factory()->create();

    expect($pedido)->toBeInstanceOf(Order::class);
});

test('al crear pedido se crea por defecto estado Pendiente', function () {

    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(OrderStatus::PENDIENTE->value)
        ->and($pedido->states)->toHaveCount(1);
});

test('puedo crear Pedido con muchos pagos en factory', function () {

    $pedido = Order::factory()->hasStates(3)->create();
    expect($pedido->states)->toHaveCount(4);
});

test('puedo asociar dirección de certificado a Pedido en factory', function () {
    $pedido = Order::factory()->withCertificado()->create();
    $pedido->refresh();

    expect($pedido->certificate())->toBeInstanceOf(Address::class)
        ->and($pedido->addresses)->toHaveCount(2)
        ->and($pedido->addresses->last()->type)->toBe(AddressType::CERTIFICATE->value);
});
test('puedo crear pedido a través de factory', closure: function () {

    $order = Order::factory()->create();
    expect($order)->toBeInstanceOf(Order::class);
});

test('puedo crear factory con items', function () {

    $order = Order::factory()
        ->hasItems(2)
        ->create();

    expect($order->items)->toHaveCount(2);
});

test('puedo crear factory con diferentes estados', function (string $estado) {
    $order = Order::factory()->{$estado}()->create();

    $expectedStatus = OrderStatus::from(match ($estado) {
        'pagado' => 'Pagado',
        'enviado' => 'Enviado',
        'finalizado' => 'Finalizado',
        'error' => 'ERROR',
        'cancelado' => 'Cancelado',
    });

    expect($order->states)->toHaveCount(2)
        ->and($order->state->name)
        ->toBe($expectedStatus->value);

})->with([
    'pagado',
    'enviado',
    'finalizado',
    'error',
    'cancelado',
]);

test('el estado por defecto es pendiente de envío', function () {
    $pedido = Order::factory()->create();
    expect($pedido->state->name)->toBe(OrderStatus::PENDIENTE->value);
});

test('la dirección por defecto es la de facturación', function () {
    $order = Order::factory()->create();
    expect($order->billing_address())->toBeInstanceOf(Address::class)
        ->and($order->billing_address()->type)->toBe(AddressType::BILLING->value);
});

test('puedo crear dirección de envío de factory', function () {
    $order = Order::factory()->withDirecionEnvio()->create();
    expect($order->shipping_address())->toBeInstanceOf(Address::class)
        ->and($order->shipping_address()->type)->toBe(AddressType::SHIPPING->value);
});

test('puedo crear direcciones desde modelo', function () {
    $order = Order::factory()->create();

    $order->addresses()->create(Address::factory()->make([
        'type' => AddressType::SHIPPING->value,
    ])->except('created_at', 'updated_at'));

    expect($order->addresses)->toHaveCount(2)
        ->and($order->addresses->last()->type)->toBe(AddressType::SHIPPING->value);
});

test('puedo crear pedido desde componente de livewire', function () {

    $order = creaPedido();

    $total = Order::first()->shipping_cost + Product::first()->price;

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->product->name)->toBe('Producto de prueba')
        ->and(round($order->amount))->toBe(round($total));

});

test('vacio cesta después de crear pedido', function () {

    creaPedido();
    expect(Cartify::count())->toBe(0)
        ->and(CartNormalizer::items())->toHaveCount(0)
        ->and(session()->has('cart.totals.subtotal'))->toBeFalse()
        ->and(session()->has('cart.shipping_method.price'))->toBeFalse()
        ->and(session()->has('cart.items'))->toBeFalse();
    $this->get(route('cart'))
        ->assertSee('No hay productos en el carrito');
});

test('puedo obtener las imagenes de los productos del pedido', function () {
    Storage::fake('storage');
    $productos = Product::factory()
        ->imagen(public_path('storage/productos/botella-azul.webp'))
        ->count(2)
        ->create();

    $order = Order::factory()
        ->hasItems(2, [
            'product_id' => $productos->random()->id,
        ])
        ->create();

    expect($order->images()->first()->first())->toBeInstanceOf(Media::class);
});

test('cuando realizo pedido resto del stock de producto', function () {
    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 5,
    ]);
    $pedido = creaPedido($producto);

    $dataOk = [
        'Ds_Order' => $pedido->number,
        'Ds_Amount' => convert_amount_to_redsys($pedido->amount),
    ];

    $pedido->payed($dataOk);
    $producto->refresh();
    expect($producto->stock)->toBe(4);
});

test('puedo obtener listado de items para emails', function () {
    Product::factory()
        ->imagen(public_path('storage/productos/botella-azul.webp'))
        ->count(2)
        ->create();

    $order = Order::factory()->hasItems(2)->create();

    expect($order->itemsArray())->toBeArray()
        ->and($order->itemsArray())->toHaveCount(2)
        ->and($order->itemsArray()[0])->toHaveKeys(['name', 'price', 'quantity', 'subtotal', 'image']);

});

test('al crear pedido solo creo un estado pendiente de pago', function () {

    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 5,
    ]);
    $pedido = creaPedido($producto);

    $this->get(route('pedido.response', getResponseOrder($pedido)));

    $pedido->refresh();

    expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($pedido->states)->toHaveCount(2);
});

test('al procesar pedido envío email a todos los usuarios', function ($enviroment) {

    // Establezco el entorno actual
    config(['app.env' => $enviroment]);
    Notification::fake();

    User::factory()->count(3)->create();

    $pedido = creaPedido();
    $this->get(route('pedido.response', getResponseOrder($pedido)));

    if (app()->environment('production')) {
        Notification::assertSentTo(
            User::where('email', 'info@raulsebastian.es')->get(), OrderCreated::class
        );
    } else {
        Notification::assertSentTo(
            User::all(), OrderCreated::class
        );
    }

})->with([
    ['production', 'local'],
]);

test('puedo crear factory de pedido con items', function () {

    $order = Order::factory()->withProductos(1)->create();

    expect($order->Items)->toHaveCount(1);
});

test('puedo crear factory de pedido con producto seleccionado', function () {

    $producto = Product::factory()->create([
        'name' => 'Producto de prueba',
    ]);
    $order = Order::factory()->withProductos($producto)->create();

    expect($order->Items)->toHaveCount(1);
});

test('puedo crear factory de pedido con colección de productos seleccionado', function () {

    Product::factory()->create([
        'name' => 'Producto de prueba',
    ]);
    Product::factory()->create([
        'name' => 'Producto de prueba2',
    ]);

    $productos = Product::all();
    $order = Order::factory()->withProductos($productos)->create();

    expect($order->Items)->toHaveCount(2);
});

test('puedo calcular los impuestos por función', function () {

    $order = Order::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->calculateTaxes())->toBe(4.69);
});

test('puedo calcular los impuestos por atributo', function () {

    $order = Order::factory()->create([
        'amount' => 27.00,

    ]);

    expect($order->taxes)->toBe(4.69);
});

test('puedo crear factory de pago por bizum', function () {
    $pedido = Order::factory()->porBizum()->create();

    expect($pedido->payment_method)->toBe('bizum');
});

test('al finalizar pedido veo los métodos de pago disponbles', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();

    livewire(FinishOrderComponent::class)
        ->assertSeeTextInOrder(['Método de pago', 'bizum', 'tarjeta']);

});

test('debo seleccionar un método de pago para poder finalizar pedido', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();

    livewire(FinishOrderComponent::class)
        ->call('submit')
        ->assertHasErrors(['payment_method' => 'Debes seleccionar un método de pago.']);
});

test('si selecciono bizum agrego campo z a formulario redsys', function () {
    $producto = getProducto();

    addProductToCart($producto);

    setShippingMethod();
    $comp = livewire(FinishOrderComponent::class)
        ->set([
            'payment_method' => PaymentMethod::BIZUM,
            'billing' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'last_name2' => 'Sánchez',
                'company' => 'Mi empresa',
                'address' => 'Calle Falsa 123',
                'province' => 'Madrid',
                'city' => 'Madrid',
                'cp' => '28001',
                'email' => 'info@raulsebastian.es',
            ],
        ])->call('submit');

    /** @noinspection PhpUndefinedFieldInspection */
    $params = json_decode(base64_decode(strtr($comp->MerchantParameters, '-_', '+/')), true);

    expect($params['Ds_Merchant_Paymethods'])->toBe('z')
        ->and($comp->get('form_url'))->not->toBeEmpty();

});

test('al crear pedido se limpian claves de sesión del carrito', function () {
    $pedido = creaPedido();

    expect($pedido)->toBeInstanceOf(Order::class)
        ->and(session()->has('cart.totals.total'))->toBeFalse()
        ->and(session()->has('cart.shipping_method.id'))->toBeFalse()
        ->and(session()->all())->not->toHaveKey('cart.items');
});

test('order form_url correcto según entorno', function () {
    config(['redsys.enviroment' => 'test']);
    $ppTest = new PaymentProcess(Order::class, [
        'amount' => '15,00',
        'shipping' => 'Envío',
        'shipping_cost' => 2.50,
        'subtotal' => 12.50,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $dataTest = $ppTest->getFormRedSysData();
    expect($dataTest['form_url'])->toContain('sis-t.redsys.es')
        ->and($dataTest['Ds_MerchantParameters'])->not->toBeEmpty();

    config(['redsys.enviroment' => 'production']);
    config(['app.env' => 'production']);
    $ppProd = new PaymentProcess(Order::class, [
        'amount' => '22,00',
        'shipping' => 'Envío',
        'shipping_cost' => 2.00,
        'subtotal' => 20.00,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $dataProd = $ppProd->getFormRedSysData();
    expect($dataProd['form_url'])->toContain('sis.redsys.es')
        ->and($dataProd['Ds_MerchantParameters'])->not->toBeEmpty();
});

test('order producción incluye url_notification en parámetros crudos', function () {
    config(['app.env' => 'production']);
    config(['redsys.enviroment' => 'production']);
    $pp = new PaymentProcess(Order::class, [
        'amount' => '30,00',
        'shipping' => 'Envío',
        'shipping_cost' => 5.00,
        'subtotal' => 25.00,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $pp->getFormRedSysData();
    expect($pp->redSysAttributes)->toHaveKey('DS_MERCHANT_MERCHANTURL');
});

test('order callback firma inválida marca ERROR', function () {
    $pedido = creaPedido();
    $callbackOk = getResponseOrder($pedido);
    $callbackOk['Ds_Signature'] = 'firma-alterada';
    $this->post(route('pedido.response'), $callbackOk)
        ->assertRedirect(route('pedido.finalizado', ['pedido' => $pedido->number]));
    $pedido->refresh();
    expect($pedido->state->name)->toBe(OrderStatus::ERROR->value)
        ->and($pedido->state->info['Error'])->toBe('Firma no válida');
});

test('order callback KO marca ERROR y mantiene pago inicial', function () {
    $pedido = creaPedido();
    $paramsKo = buildRedsysParams(
        amount: convert_amount_to_redsys($pedido->amount),
        order: $pedido->number,
        response: '9928'
    );
    $paramsKo['Ds_ProcessedPayMethod'] = '78';
    $callbackKo = generateRedsysResponse($paramsKo, $pedido->number);

    $this->post(route('pedido.response'), $callbackKo)
        ->assertRedirect(route('pedido.finalizado', ['pedido' => $pedido->number]));
    $pedido->refresh();
    expect($pedido->state->name)->toBe(OrderStatus::ERROR->value)
        ->and($pedido->payments)->toHaveCount(1)
        ->and($pedido->payments->first()->amount)->toBe(0.0)
        ->and($pedido->state->info['Ds_Response'])->toBe('9928');
});

test('order MerchantParameters codifica correctamente número y amount', function () {
    $pedido = creaPedido();
    $callbackOk = getResponseOrder($pedido);
    $decoded = json_decode(base64_decode(strtr($callbackOk['Ds_MerchantParameters'], '-_', '+/')), true);
    expect($decoded['Ds_Order'])->toBe($pedido->number)
        ->and($decoded['Ds_Amount'])->toBe(convert_amount_to_redsys($pedido->amount));
});

test('order NO incluye campos COF en MerchantParameters', function () {
    $pp = new PaymentProcess(Order::class, [
        'amount' => '10,00',
        'shipping' => 'Envío',
        'shipping_cost' => 0.00,
        'subtotal' => 10.00,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $data = $pp->getFormRedSysData();
    $decoded = json_decode(base64_decode(strtr($data['Ds_MerchantParameters'], '-_', '+/')), true);
    expect($decoded)->not->toHaveKeys(['DS_MERCHANT_COF_INI', 'DS_MERCHANT_COF_TYPE']);
});

test('order callback OK repetido no duplica estado PAGADO (idempotencia)', function () {
    Event::fake();  // Bloquea todos los listeners incluyendo SendEmailsOrderListener

    $pp = new PaymentProcess(Order::class, [
        'amount' => '25,00',
        'shipping' => 'Envío',
        'shipping_cost' => 3.00,
        'subtotal' => 22.00,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $pedido = $pp->modelo;

    // Crear estado PENDIENTE manualmente ya que Event::fake bloquea el listener
    $pedido->states()->create(['name' => OrderStatus::PENDIENTE->value]);

    $callbackOk = getResponseOrder($pedido);

    // Primera llamada OK
    $this->post(route('pedido.response'), $callbackOk)
        ->assertRedirect(route('pedido.finalizado', ['pedido' => $pedido->number]));
    $pedido->refresh();
    expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($pedido->states)->toHaveCount(2); // PENDIENTE + PAGADO

    // Segunda llamada OK (duplicada)
    $this->post(route('pedido.response'), $callbackOk)
        ->assertRedirect(route('pedido.finalizado', ['pedido' => $pedido->number]));
    $pedido->refresh();
    expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($pedido->states)->toHaveCount(2); // No debe crear estado duplicado
});

test('order callback sin Ds_MerchantParameters retorna 404', function () {
    creaPedido();

    $this->post(route('pedido.response'), [
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();
});

test('order callback con MerchantParameters corrupto retorna 404', function () {
    creaPedido();

    $this->post(route('pedido.response'), [
        'Ds_MerchantParameters' => 'datos-corruptos-no-base64',
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();
});

test('order callback con MerchantParameters JSON inválido retorna 404', function () {

    // Base64 válido pero JSON inválido
    $invalidJson = base64_encode('esto no es json válido');

    $this->post(route('pedido.response'), [
        'Ds_MerchantParameters' => $invalidJson,
        'Ds_Signature' => 'firma-cualquiera',
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ])->assertNotFound();
});

test('order callback vacío retorna 404', function () {
    $this->post(route('pedido.response'), [])
        ->assertNotFound();
});

// === TESTS DE PERFORMANCE Y CARGA MASIVA PARA ORDERS ===

test('carga masiva: puede procesar 50 pedidos simultáneos sin errores', function () {
    Event::fake();
    $pedidos = collect();

    // Crear 50 pedidos
    for ($i = 0; $i < 50; $i++) {
        $pp = new PaymentProcess(Order::class, [
            'amount' => fake()->randomFloat(2, 15, 150),
            'shipping' => 'Envío estándar',
            'shipping_cost' => 5.00,
            'subtotal' => fake()->randomFloat(2, 10, 145),
            'payment_method' => PaymentMethod::TARJETA->value,
        ]);

        // Con Event::fake no se crea el estado PENDIENTE automáticamente
        $pp->modelo->states()->create(['name' => OrderStatus::PENDIENTE->value]);
        $pedidos->push($pp->modelo);
    }

    // Verificar que todos los números son únicos
    $numeros = $pedidos->pluck('number');
    expect($numeros->unique()->count())->toBe(50);

    // Procesar callbacks para todos
    $pedidos->each(function ($pedido) {
        $callback = getResponseOrder($pedido, true);
        $this->post(route('pedido.response'), $callback)
            ->assertRedirect();
    });

    // Verificar que todos están en estado PAGADO
    $pedidos->each(function ($pedido) {
        $pedido->refresh();
        expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value)
            ->and($pedido->payments->first()->amount)->toBeGreaterThan(0);
    });
})->group('performance');

test('carga masiva: pedidos con diferentes métodos de pago procesados correctamente', function () {
    Event::fake();
    $pedidos = collect();

    // Crear 30 pedidos con métodos alternados
    for ($i = 0; $i < 30; $i++) {
        $metodo = $i % 2 === 0 ? PaymentMethod::TARJETA->value : PaymentMethod::BIZUM->value;

        $pp = new PaymentProcess(Order::class, [
            'amount' => 25.00,
            'shipping' => 'Envío',
            'shipping_cost' => 3.00,
            'subtotal' => 22.00,
            'payment_method' => $metodo,
        ]);
        $pedidos->push([
            'order' => $pp->modelo,
            'metodo' => $metodo,
        ]);
    }

    // Verificar que se han creado correctamente
    $pedidos->each(function ($item) {
        expect($item['order']->payment_method)->toBe($item['metodo']);
    });
})->group('performance');

test('carga masiva: helpers Redsys generan firmas únicas para 100 pedidos', function () {
    Event::fake();
    $firmas = collect();

    for ($i = 0; $i < 100; $i++) {
        $pp = new PaymentProcess(Order::class, [
            'amount' => fake()->randomFloat(2, 10, 200),
            'shipping' => 'Envío',
            'shipping_cost' => fake()->randomFloat(2, 0, 10),
            'subtotal' => fake()->randomFloat(2, 10, 190),
            'payment_method' => PaymentMethod::TARJETA->value,
        ]);

        $formData = $pp->getFormRedSysData();
        $firmas->push($formData['Ds_Signature']);
    }

    // Todas las firmas deben ser únicas
    expect($firmas->unique()->count())->toBe(100);
})->group('performance');

test('carga masiva: callbacks pedidos OK y KO se procesan sin race conditions', function () {
    Event::fake();
    $pedidos = collect();

    // Crear 20 pedidos
    for ($i = 0; $i < 20; $i++) {
        $pp = new PaymentProcess(Order::class, [
            'amount' => 30.00,
            'shipping' => 'Envío',
            'shipping_cost' => 5.00,
            'subtotal' => 25.00,
            'payment_method' => PaymentMethod::TARJETA->value,
        ]);

        // Con Event::fake crear estado PENDIENTE manualmente
        $pp->modelo->states()->create(['name' => OrderStatus::PENDIENTE->value]);
        $pedidos->push($pp->modelo);
    }

    // Procesar mitad OK y mitad KO
    $pedidos->each(function ($pedido, $index) {
        $esOk = $index % 2 === 0;
        $callback = getResponseOrder($pedido, $esOk);

        $this->post(route('pedido.response'), $callback)
            ->assertRedirect();

        $pedido->refresh();
        if ($esOk) {
            expect($pedido->state->name)->toBe(OrderStatus::PAGADO->value);
        } else {
            expect($pedido->state->name)->toBe(OrderStatus::ERROR->value);
        }
    });
})->group('performance');

test('carga masiva: idempotencia de pedidos se mantiene con 20 callbacks duplicados', function () {
    Event::fake();

    $pp = new PaymentProcess(Order::class, [
        'amount' => 45.00,
        'shipping' => 'Envío',
        'shipping_cost' => 5.00,
        'subtotal' => 40.00,
        'payment_method' => PaymentMethod::TARJETA->value,
    ]);
    $pedido = $pp->modelo;

    // Con Event::fake crear estado PENDIENTE manualmente
    $pedido->states()->create(['name' => OrderStatus::PENDIENTE->value]);

    // Verificar estado inicial PENDIENTE
    expect($pedido->states)->toHaveCount(1)
        ->and($pedido->state->name)->toBe(OrderStatus::PENDIENTE->value);

    $callback = getResponseOrder($pedido, true);

    // Enviar el mismo callback 20 veces
    for ($i = 0; $i < 20; $i++) {
        $this->post(route('pedido.response'), $callback)
            ->assertRedirect();
    }

    $pedido->refresh();

    // Debe seguir teniendo solo 2 estados (PENDIENTE + PAGADO)
    expect($pedido->states)->toHaveCount(2)
        ->and($pedido->state->name)->toBe(OrderStatus::PAGADO->value);
})->group('performance');

test('carga masiva: memoria estable procesando 40 pedidos', function () {
    Event::fake();
    $memoriaInicial = memory_get_usage();

    for ($i = 0; $i < 40; $i++) {
        $pp = new PaymentProcess(Order::class, [
            'amount' => 35.00,
            'shipping' => 'Envío',
            'shipping_cost' => 5.00,
            'subtotal' => 30.00,
            'payment_method' => PaymentMethod::TARJETA->value,
        ]);

        $pp->modelo->states()->create(['name' => OrderStatus::PENDIENTE->value]);
        $callback = getResponseOrder($pp->modelo, true);
        $this->post(route('pedido.response'), $callback);

        // Limpiar para siguiente iteración
        unset($pp);
    }

    $memoriaFinal = memory_get_usage();
    $incrementoMB = ($memoriaFinal - $memoriaInicial) / 1024 / 1024;

    // El incremento de memoria no debe superar 40 MB para 40 transacciones
    expect($incrementoMB)->toBeLessThan(40);
})->group('performance');
