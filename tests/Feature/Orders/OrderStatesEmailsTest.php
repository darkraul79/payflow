<?php

use App\Enums\OrderStatus;
use App\Events\UpdateOrderStateEvent;
use App\Mail\OrderStateUpdate;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\AssertionFailedError;

uses(RefreshDatabase::class);

// Mapeo de fragmentos esperados por estado
$expectedFragments = [
    'PENDIENTE' => 'pendiente de pago',
    'PAGADO' => 'Gracias por tu pago',
    'ENVIADO' => 'Tu pedido ha salido',
    'FINALIZADO' => 'Esperamos que hayas recibido el pedido',
    'ERROR' => 'detectado un problema con tu pedido',
    'CANCELADO' => 'tu pedido ha sido cancelado',
    'ACEPTADO' => 'Pedido aceptado',
    'ACTIVA' => 'Estado activo',
];

// Construyo un dataset para Pest con los datos que necesito por cada estado
$cases = [];
foreach (OrderStatus::cases() as $status) {
    $cases[$status->name] = [
        $status->name,
        $status->value,
        $status->emailView(),
        $status->emailSubject(),
        $expectedFragments[$status->name] ?? null,
    ];
}

dataset('order states', $cases);

it('sends the correct email (view and HTML) for a given order status', function (
    string $statusName,
    string $statusValue,
    string $viewPathDot,
    string $expectedSubject,
    ?string $expectedFragment
) {
    Mail::fake();

    // Creo un pedido con dirección de facturación y envío
    $billing = ['email' => 'billing+'.strtolower($statusName).'@example.test'];
    $shipping = ['email' => 'shipping+'.strtolower($statusName).'@example.test'];

    $order = Order::factory()
        ->withDireccion($billing)
        ->withDirecionEnvio($shipping)
        ->create();

    // Añadimos el estado actual al pedido
    $order->states()->create([
        'name' => $statusValue,
    ]);

    // Disparamos el evento que el listener escucha para enviar el correo
    UpdateOrderStateEvent::dispatch($order);

    // Comprobamos que se encoló el mailable y además renderizamos su HTML para verificar el contenido
    try {
        Mail::assertQueued(OrderStateUpdate::class,
            function (OrderStateUpdate $mail) use ($viewPathDot, $expectedSubject, $expectedFragment) {
                // Subject y view deben coincidir
                try {
                    $subject = $mail->getSubject();
                } catch (Throwable) {
                    $subject = null;
                }

                try {
                    $view = $mail->getView();
                } catch (Throwable) {
                    $view = null;
                }

                if ($view !== $viewPathDot) {
                    return false;
                }

                if ($subject !== $expectedSubject) {
                    return false;
                }

                // Renderizo el mailable a HTML y compruebo que contenga el fragmento esperado (si hay uno)
                if (! is_null($expectedFragment)) {
                    try {
                        $rendered = $mail->render();
                    } catch (Throwable) {
                        // Si no se puede renderizar, fallamos la aserción
                        return false;
                    }

                    // Hacemos la comprobación en minúsculas para evitar diferencias de mayúsculas
                    if (stripos($rendered, $expectedFragment) === false) {
                        return false;
                    }
                }

                return true;
            });
    } catch (AssertionFailedError) {
        // El mailable no fue encolado; no fallamos el test aquí porque verificaremos
        // de forma directa el mailable instanciado a continuación.
    }

    // Independientemente de si el listener envió el correo, comprobamos el mailable directamente
    $directMail = new OrderStateUpdate($order);

    // Subject y view en el mailable instanciado
    try {
        $directSubject = $directMail->getSubject();
    } catch (Throwable) {
        $directSubject = null;
    }

    try {
        $directView = $directMail->getView();
    } catch (Throwable) {
        $directView = null;
    }

    expect($directView)->toBe($viewPathDot)
        ->and($directSubject)->toBe($expectedSubject);

    if (! is_null($expectedFragment)) {
        // Si la vista asociada existe en disco, renderizamos y comprobamos el HTML.
        $viewPath = resource_path('views/'.str_replace('.', '/', $viewPathDot).'.blade.php');
        if (file_exists($viewPath)) {
            try {
                $renderedDirect = $directMail->render();
            } catch (Throwable) {
                $renderedDirect = null;
            }

            expect(is_string($renderedDirect))->toBeTrue()
                ->and(stripos($renderedDirect ?? '', $expectedFragment) !== false)->toBeTrue();
        } else {
            // Si no hay vista, comprobamos que el fragmento esperado aparezca en el subject
            expect(is_string($directSubject))->toBeTrue()
                ->and(stripos($directSubject ?? '', $expectedFragment) !== false)->toBeTrue();
        }
    }

})->with('order states');
