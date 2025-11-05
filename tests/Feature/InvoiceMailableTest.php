<?php

use App\Mail\InvoiceMailable;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Storage;
use Outerweb\Settings\Models\Setting;

use function Pest\Laravel\actingAs;

it('regenerates missing PDF in mailable attachments to avoid null body', function () {
    Storage::fake('public');

    // Minimal billing settings required by the PDF view
    Setting::set('billing.company', 'Fundación Test');
    Setting::set('billing.nif', 'X1234567Z');
    Setting::set('billing.email', 'facturas@example.com');
    Setting::set('billing.address', 'Calle Falsa 123');
    Setting::set('billing.postal_code', '28080');
    Setting::set('billing.city', 'Madrid');
    Setting::set('billing.country', 'España');
    Setting::set('billing.vat.orders_default', 21);

    actingAs(User::factory()->create());

    /** @var InvoiceService $service */
    $service = app(InvoiceService::class);

    $order = Order::factory()->withDireccion()->hasItems()->create();

    try {
        $result = $service->generateForOrder($order);
    } catch (Throwable $e) {
        throw new RuntimeException('Failed to generate the invoice for order: '.$e->getMessage());
    }
    $path = $result['path'];

    // Ensure it exists initially
    Storage::disk('public')->assertExists($path);

    // Simulate a missing file scenario (what the queue would see later)
    Storage::disk('public')->delete($path);

    // Build the mailable and fetch attachments (this is what the queue will do)
    $mailable = new InvoiceMailable($result['invoice']);
    $attachments = $mailable->attachments();

    // Either it regenerates and attaches, or the worst case sends without attachment.
    // Primary expectation: file is recreated and one attachment is present.
    Storage::disk('public')->assertExists($path);
    expect($attachments)->toBeArray()->and(count($attachments))->toBe(1);
});
