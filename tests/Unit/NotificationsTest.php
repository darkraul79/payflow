<?php

use App\Models\Donation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DonationCreatedNotification;
use App\Notifications\OrderCreated;

it('OrderCreated genera un MailMessage con subject y action correctos', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();

    $notification = new OrderCreated($order);

    $mail = $notification->toMail();

    expect($mail->subject)->toBe('Nuevo Pedido '.$order->number)
        ->and($mail->actionText)->toBe('Ver pedido')
        ->and($mail->actionUrl)->toBe(
            App\Filament\Resources\OrderResource::getUrl('update', ['record' => $order->id])
        );
});

it('OrderCreated incluye resumen de artículos y total para pedidos con múltiples ítems', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'amount' => 0,
    ]);

    // Crear 3 productos y asociar items al mismo pedido
    $p1 = Product::factory()->create(['price' => 5.00, 'name' => 'Producto A']);
    $p2 = Product::factory()->create(['price' => 7.50, 'name' => 'Producto B']);
    $p3 = Product::factory()->create(['price' => 2.00, 'name' => 'Producto C']);

    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p1->id, 'quantity' => 2]); // 10.00
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p2->id, 'quantity' => 1]); // 7.50
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p3->id, 'quantity' => 3]); // 6.00

    // Recalcular totales simples para el test (sin impuestos/envío)
    $order->update([
        'subtotal' => 23.50,
        'amount' => 23.50,
    ]);
    $order->refresh();

    $notification = new OrderCreated($order->load('items.product'));

    $mail = $notification->toMail();

    // Debe incluir el total y un resumen con nombre × cantidad
    expect($mail->introLines)
        ->toContain('Importe total: '.convertPrice(23.50));

    $resumenLine = collect($mail->introLines)->first(fn ($line) => str_contains($line, 'Resumen de artículos: '));

    expect($resumenLine)
        ->not->toBeNull()
        ->and($resumenLine)
        ->toContain('Producto A × 2')
        ->and($resumenLine)
        ->toContain('Producto B × 1')
        ->and($resumenLine)
        ->toContain('Producto C × 3');
});

it('DonationCreatedNotification (única) compone el mensaje y acción correctamente', function () {
    $user = User::factory()->create();
    $donation = Donation::factory()->create(); // por defecto única

    $notification = new DonationCreatedNotification($donation);

    $mail = $notification->toMail();

    expect($mail->subject)->toBe('Nueva Donación ')
        ->and($mail->introLines[0])
        ->toContain('Hay una nueva donación '.$donation->type)
        ->and($mail->introLines[0])
        ->toContain('('.convertPrice($donation->amount).')')
        ->and($mail->actionText)->toBe('Ver donacion ')
        ->and($mail->actionUrl)->toBe(
            App\Filament\Resources\DonationResource::getUrl('view', ['record' => $donation])
        );
});

it('DonationCreatedNotification (recurrente) añade la frecuencia al texto', function () {
    $user = User::factory()->create();
    $donation = Donation::factory()->recurrente()->create();

    $notification = new DonationCreatedNotification($donation);

    $mail = $notification->toMail();

    expect($mail->introLines[0])
        ->toContain($donation->type.': '.$donation->frequency)
        ->and($mail->introLines[0])
        ->toContain('('.convertPrice($donation->amount).')');
});
