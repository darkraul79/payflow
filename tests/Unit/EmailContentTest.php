<?php

use App\Mail\OrderNew;
use App\Models\Product;
use App\Notifications\OrderCreated;

it('mailable ordernew contiene número y total en los datos del view', function () {
    // Crear producto con imagen para evitar embed() con ruta vacía
    $producto = Product::factory()->imagen(public_path('storage/productos/botella-azul.webp'))->create();

    $order = creaPedido($producto);

    $mailable = new OrderNew($order);

    // Obtener los datos que el mailable pasará a la vista
    $content = $mailable->content();
    $with = $content->with ?? [];

    expect($with['number'] ?? null)->toBe($order->number)
        ->and($with['total'] ?? null)->toBe(convertPrice($order->amount));
});

it('notificacion ordercreated toMail contiene numero y total', function () {
    // Crear producto con imagen para evitar embed() con ruta vacía
    $producto = Product::factory()->imagen(public_path('storage/productos/botella-azul.webp'))->create();

    $order = creaPedido($producto);

    $notification = new OrderCreated($order);

    $mailMessage = $notification->toMail();

    // Obtener array si existe toArray, o inspeccionar subject y líneas
    if (method_exists($mailMessage, 'toArray')) {
        $data = $mailMessage->toArray();
        $subject = $data['subject'] ?? '';
        $introLines = $data['intro_lines'] ?? $data['introLines'] ?? [];

        $containsOrderNumber = str_contains($subject, $order->number) || collect($introLines)->contains(fn ($line
        ) => str_contains($line, $order->number));
        $containsTotal = collect($introLines)->contains(fn ($line) => str_contains($line, convertPrice($order->amount)));

        expect($containsOrderNumber)->toBeTrue()->and($containsTotal)->toBeTrue();
    } else {
        $render = $mailMessage->render();
        expect($render)->toContain($order->number)->and($render)->toContain(convertPrice($order->amount));
    }
});
