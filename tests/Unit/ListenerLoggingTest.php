<?php

use App\Events\CreateOrderEvent;
use App\Listeners\SendEmailsOrderListener;
use App\Mail\OrderNew;
use App\Models\User;
use App\Notifications\OrderCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

it('listener envia notificacion y mailable sin errores', function () {
    config(['app.env' => 'local']);
    // Forzar la cola a sync en el test
    config(['queue.default' => 'sync']);

    Notification::fake();
    Mail::fake();

    User::factory()->create(['email' => 'info@raulsebastian.es']);

    $order = creaPedido();

    $listener = new SendEmailsOrderListener;

    $listener->handle(new CreateOrderEvent($order));

    Notification::assertSentTo(User::where('email', 'info@raulsebastian.es')->get(), OrderCreated::class);

    // El mailable se envía encolado, comprobar que fue encolado
    Mail::assertQueued(OrderNew::class, function ($mail) use ($order) {
        $content = $mail->content();
        $with = $content->with ?? [];

        return ($with['number'] ?? '') === $order->number && ($with['total'] ?? '') === convertPrice($order->amount);
    });
});
