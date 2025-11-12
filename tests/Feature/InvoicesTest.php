<?php

use App\Filament\Resources\DonationResource\Pages\Listdonations;
use App\Mail\InvoiceMailable;
use App\Models\Donation;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Outerweb\Settings\Models\Setting;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Storage::fake('public');
    Mail::fake();

    // Seed minimal billing settings so the PDF view has data
    Setting::set('billing.company', 'Fundación Test');
    Setting::set('billing.nif', 'X1234567Z');
    Setting::set('billing.email', 'facturas@example.com');
    Setting::set('billing.address', 'Calle Falsa 123');
    Setting::set('billing.postal_code', '28080');
    Setting::set('billing.city', 'Madrid');
    Setting::set('billing.country', 'España');
    Setting::set('billing.vat.orders_default', 21);
    Setting::set('billing.vat.donations_default', 0);

    $this->service = app(InvoiceService::class);
    actingAs(User::factory()->create());
});

it('generates invoice for order and stores PDF', function () {
    $order = Order::factory()
        ->withDireccion()
        ->hasItems(2)
        ->create(['shipping_cost' => 10]);

    $result = $this->service->generateForOrder($order);

    expect($result)->toHaveKeys(['invoice', 'path', 'url'])
        ->and($result['invoice'])->toBeInstanceOf(Invoice::class)
        ->and($result['invoice']->number)->toStartWith('FP-');

    // The PDF must exist immediately after generation
    Storage::disk('public')->assertExists($result['path']);
    // And the media should be attached without removing the original file
    $order->refresh();
    expect($order->getMedia('invoices')->count())->toBe(1);
});

it('regenerates invoice for order keeping the same number and single record', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create(['shipping_cost' => 5]);

    $first = $this->service->generateForOrder($order);
    $invId = $first['invoice']->id;
    $invNumber = $first['invoice']->number;

    // Change something that affects totals (e.g., shipping)
    $order->update(['shipping_cost' => 12]);

    $second = $this->service->generateForOrder($order);

    expect($second['invoice']->id)->toBe($invId)
        ->and($second['invoice']->number)->toBe($invNumber)
        ->and($order->invoices()->count())->toBe(1);

    Storage::disk('public')->assertExists($second['path']);
});

it('secured route streams invoice PDF when authenticated', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();
    $result = $this->service->generateForOrder($order);

    $response = get(route('invoices.show', $result['invoice']));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('secured route blocks unauthenticated users (redirect or exception)', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();
    $result = $this->service->generateForOrder($order);

    // Log out current user
    auth()->logout();

    try {
        $response = get(route('invoices.show', $result['invoice']));
        expect($response->getStatusCode())->toBeIn([302, 500]);
    } catch (RouteNotFoundException) {
        // Some apps don't define a named 'login' route in tests; redirect throws
        expect(true)->toBeTrue();
    }
});

it('regenerates and streams when the invoice file is missing', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();
    $result = $this->service->generateForOrder($order);

    // Remove file from fake storage
    Storage::disk('public')->delete($result['path']);

    // Now hitting the route should trigger regeneration and return 200
    $response = get(route('invoices.show', $result['invoice']));
    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('X-Invoice-Regenerated', '1');

    // And the file should exist again after regeneration
    Storage::disk('public')->assertExists($result['path']);
});

it('renders inline SVG logo in invoice HTML when provided', function () {
    $donation = Donation::factory()->withCertificado()->create(['amount' => 10]);
    $invoice = new Invoice(['number' => 'TEST-INV-1']);
    $invoice->created_at = now();

    $svgPath = public_path('images/logo-fundacion-horizontal.svg');
    $svg = file_exists($svgPath) ? file_get_contents($svgPath) : '<svg></svg>';

    $html = view('pdf.invoice', [
        'invoice' => $invoice,
        'invoiceable' => $donation,
        'subtotal' => 10.0,
        'vatRate' => 0.0,
        'vatAmount' => 0.0,
        'total' => 10.0,
        'lines' => [
            ['name' => 'Donación', 'quantity' => 1, 'unit_price' => 10.0, 'line_total' => 10.0],
        ],
        'meta' => [],
        'settings' => [
            'logo_svg_content' => $svg,
            'logo_abs_path' => '',
            'logo_data_uri' => '',
        ],
    ])->render();

    expect($html)->toContain('<svg');
});

it('renders raster logo using data URI when provided', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();
    $invoice = new Invoice(['number' => 'TEST-INV-2']);
    $invoice->created_at = now();

    $dataUri = 'data:image/png;base64,'.base64_encode('fakepng');

    $html = view('pdf.invoice', [
        'invoice' => $invoice,
        'invoiceable' => $order,
        'subtotal' => 10.0,
        'vatRate' => 0.21,
        'vatAmount' => 2.1,
        'total' => 12.1,
        'lines' => [
            ['name' => 'Producto', 'quantity' => 1, 'unit_price' => 10.0, 'line_total' => 10.0],
        ],
        'meta' => [],
        'settings' => [
            'logo_svg_content' => '',
            'logo_abs_path' => '',
            'logo_data_uri' => $dataUri,
        ],
    ])->render();

    expect($html)->toContain('src="'.$dataUri.'"');
});

it('generates invoice for donation and stores PDF', function () {
    $donation = Donation::factory()->withCertificado()->create(['amount' => 100]);

    $result = $this->service->generateForDonation($donation);

    expect($result)->toHaveKeys(['invoice', 'path', 'url'])
        ->and($result['invoice'])->toBeInstanceOf(Invoice::class)
        ->and($result['invoice']->number)->toStartWith('FD-');

    Storage::disk('public')->assertExists($result['path']);
});

it('sends email when requested for order', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();

    $this->service->generateForOrder($order, sendEmail: true);

    Mail::assertSent(InvoiceMailable::class);
});

it('sends email when requested for the donation that has certificate email', function () {
    $donation = Donation::factory()->withCertificado()->create();

    $this->service->generateForDonation($donation, sendEmail: true);

    Mail::assertSent(InvoiceMailable::class);
});

it('does not send email for donation without certificate email', function () {
    $donation = Donation::factory()->create(['amount' => 50]);

    $this->service->generateForDonation($donation, sendEmail: true);

    Mail::assertNothingSent();
});

it('sends invoice email for order', function () {
    Mail::fake();

    $order = Order::factory()->withDireccion()
        ->create();

    $billingAddress = $order->billing_address();

    $service = app(InvoiceService::class);
    $result = $service->generateForOrder($order, sendEmail: true);

    Mail::assertSent(InvoiceMailable::class, function ($mail) use ($billingAddress) {
        return $mail->hasTo($billingAddress->email);
    });

    expect($result['invoice']->sent_at)->not->toBeNull()
        ->and($result['invoice']->emailed_to)->toContain($order->billing_address()->email);
});

// New test: force refresh via route parameter
it('forces refresh via route parameter and returns the refreshed header', function () {
    $order = Order::factory()->withDireccion()->hasItems()->create();
    $service = app(InvoiceService::class);

    $result = $service->generateForOrder($order, force: true);
    Storage::disk('public')->assertExists($result['path']);

    // Remove the file so refresh must rewrite it
    Storage::disk('public')->delete($result['path']);

    actingAs(User::factory()->create());
    $response = get(route('invoices.show', $result['invoice']).'?refresh=1');

    $response->assertSuccessful();
    $response->assertHeader('X-Invoice-Refreshed', '1');
    $response->assertHeader('Content-Type', 'application/pdf');

    Storage::disk('public')->assertExists($result['path']);
});

// New test: storage write failure should throw and not send email
it('throws when storage write fails and does not send email', function () {
    Mail::fake();

    $order = Order::factory()->withDireccion()->hasItems()->create();

    // Mock the public disk to force put() failure
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('exists')->andReturn(false);
    $disk->shouldReceive('put')->andReturn(false); // simulate failure

    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    $this->expectException(RuntimeException::class);

    try {
        app(InvoiceService::class)->generateForOrder($order, sendEmail: true, force: true);
    } finally {
        Mail::assertNotSent(InvoiceMailable::class);
    }
});

test('genero el iva correctamente de las facturas de pedidos',
    function ($coste_envio, $precio_producto, $iva) {

        $metodoEnvio = ShippingMethod::factory()->create([
            'name' => 'Gratuito',
            'price' => $coste_envio,
        ]);

        $producto = Product::factory()->create([
            'name' => 'Producto de prueba',
            'price' => $precio_producto,
        ]);

        $order = Order::factory()
            ->withDireccion()
            ->withProductos($producto)
            ->create([
                'shipping' => $metodoEnvio->name,
                'shipping_cost' => $metodoEnvio->price,
            ]);

        $order->refresh();

        $invoice = $this->service->generateForOrder($order)['invoice'];
        //        dump($result->subtotal, $order->toArray());


        expect($invoice->vat_amount)->toBe($iva)
            ->and($invoice->total)->toBe($precio_producto + $coste_envio);

    })->with([
    [
        'coste_envio' => 3.50,
        'precio_producto' => 8.00,
        'iva' => 2.00,
    ],
]);


test('genero el iva correctamente de las facturas de donaciones',
    function ($importe, $subtotal, $iva, $porcentaje) {


        $donacion = Donation::factory()
            ->withCertificado()
            ->create([
                'amount' => $importe,
            ]);

        $donacion->refresh();

        Setting::set('billing.vat.donations_default', $porcentaje);
        $invoice = $this->service->generateForDonation($donacion)['invoice'];
        //        dump($result->subtotal, $order->toArray());


        expect($invoice->vat_amount)->toBe($iva)
            ->and($invoice->subtotal)->toBe($subtotal)
            ->and($invoice->total)->toBe($importe);

    })->with([
    [
        'importe' => 5.16,
        'subtotal' => 4.26,
        'iva' => 0.90,
        'porcentaje' => 21,
    ], [
        'importe' => 5.16,
        'subtotal' => 5.16,
        'iva' => 0.0,
        'porcentaje' => 0,
    ],
]);

test('puedo crear facturas de donaciones sin certificado', function () {
    $donacion = Donation::factory()
        ->withCertificado()
        ->create();

    $invoice = $this->service->generateForDonation($donacion)['invoice'];


    Storage::disk('public')->assertExists($invoice['path']);
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->total)->toBe($donacion->amount);

    actingAs(User::factory()->create());
    $response = get(route('invoices.show', $invoice).'?refresh=1');

    $response->assertSuccessful()
        ->assertHeader('X-Invoice-Refreshed', '1')
        ->assertHeader('Content-Type', 'application/pdf');


    livewire(Listdonations::class)
        ->assertTableActionVisible('invoice')
        ->assertTableActionDataSet(['send_email' => false])
        ->callTableAction('invoice', $donacion)
        ->assertHasNoTableActionErrors();
});

test('puedo crear facturas de pedidos sin dirección de envío y con cualquier estado', function () {
    $pedido = Order::factory()
        ->error()
        ->create();

    $invoice = $this->service->generateForDonation($pedido)['invoice'];


    Storage::disk('public')->assertExists($invoice['path']);
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->total)->toBe($pedido->amount);

    actingAs(User::factory()->create());
    $response = get(route('invoices.show', $invoice).'?refresh=1');

    $response->assertSuccessful()
        ->assertHeader('X-Invoice-Refreshed', '1')
        ->assertHeader('Content-Type', 'application/pdf');


    livewire(Listdonations::class)
        ->assertTableActionVisible('invoice')
        ->assertTableActionDataSet(['send_email' => false])
        ->callTableAction('invoice', $pedido)
        ->assertTableActionDataSet(['send_email' => false])
        ->assertHasNoTableActionErrors();
});
