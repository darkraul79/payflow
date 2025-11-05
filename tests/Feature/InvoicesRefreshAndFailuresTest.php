<?php

use App\Mail\InvoiceMailable;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('forces refresh via route parameter and returns refreshed header', function () {
    Storage::fake('public');

    actingAs(User::factory()->create());

    $order = Order::factory()->withDireccion()->hasItems(1)->create();
    $service = app(InvoiceService::class);

    $result = $service->generateForOrder($order, sendEmail: false, force: true);
    Storage::disk('public')->assertExists($result['path']);

    // Remove file so refresh must rewrite it
    Storage::disk('public')->delete($result['path']);

    $response = get(route('invoices.show', $result['invoice']).'?refresh=1');

    $response->assertSuccessful();
    $response->assertHeader('X-Invoice-Refreshed', '1');
    $response->assertHeader('Content-Type', 'application/pdf');

    Storage::disk('public')->assertExists($result['path']);
});

it('throws when storage write fails and does not send email', function () {
    // We do not fake the disk entirely; we will mock the adapter call used by the service
    Mail::fake();

    $order = Order::factory()->withDireccion()->hasItems(1)->create();

    // Mock the public disk to force put() failure
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('exists')->andReturn(false);
    $disk->shouldReceive('put')->andReturn(false); // simulate failure

    // Swap the disk used by Storage::disk('public') during this call
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    $this->expectException(RuntimeException::class);

    try {
        app(InvoiceService::class)->generateForOrder($order, sendEmail: true, force: true);
    } finally {
        // Must not send the invoice email if the PDF could not be persisted
        Mail::assertNotSent(InvoiceMailable::class);
    }
});
