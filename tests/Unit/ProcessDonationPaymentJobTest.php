<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\Fakes\FakeRedsysGateway;

beforeEach(function () {
    Log::spy();
    Queue::fake();
});

describe('ProcessDonationPaymentJob - Validación type_is_recurrente', function () {

    test('procesa donación con tipo RECURRENTE correctamente', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'VALID_IDENTIFIER_123'])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear (withPayment establece día 5 del próximo mes)
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        $donacion->refresh();

        expect($donacion->payments)->toHaveCount(2)
            ->and($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('rechaza donación con tipo UNICA (no recurrente)', function () {
        $donacion = Donation::factory()
            ->state([
                'type' => DonationType::UNICA->value,
                'identifier' => 'SOME_IDENTIFIER',
            ])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de la creación
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        $donacion->refresh();

        expect($donacion->payments)->toHaveCount(1); // Solo el pago inicial

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['type_is_recurrente'] === false;
            }))
            ->once();
    });

    test('verifica que type_is_recurrente es true para donaciones RECURRENTE', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'identifier' => 'ID_123',
            ])
            ->withPayment()
            ->create();

        $donacion->refresh();

        expect($donacion->type)->toBe(DonationType::RECURRENTE->value)
            ->and($donacion->type === DonationType::RECURRENTE->value)->toBeTrue();
    });

    test('verifica que type_is_recurrente es false para donaciones UNICA', function () {
        $donacion = Donation::factory()
            ->state(['type' => DonationType::UNICA->value])
            ->withPayment()
            ->create();

        $donacion->refresh();

        expect($donacion->type)->toBe(DonationType::UNICA->value)
            ->and($donacion->type === DonationType::RECURRENTE->value)->toBeFalse();
    });
});

describe('ProcessDonationPaymentJob - Validación state_is_active_or_pending', function () {

    test('procesa donación con estado ACTIVA correctamente', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_ACTIVE'])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('procesa donación con estado ACTIVA que cumple requisitos', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        // Crear donación recurrente con pago inicial (estado ACTIVA)
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_ACTIVE_2'])
            ->withPayment()
            ->create();

        // Actualizar next_payment para que sea procesable
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        // Verificar que está en estado ACTIVA (donaciones recurrentes con pago completo)
        expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
            ->and($donacion->type)->toBe(DonationType::RECURRENTE->value)
            ->and($donacion->identifier)->toBe('ID_ACTIVE_2')
            ->and($donacion->next_payment)->toBe(now()->format('Y-m-d'));

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        // Verificar que se procesó exitosamente
        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('rechaza donación con estado CANCELADO', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_CANCELLED'])
            ->withPayment()
            ->create();

        // Actualizar next_payment antes de cancelar
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->cancel();
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::CANCELADO->value);

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['state_is_active_or_pending'] === false;
            }))
            ->once();
    });

    test('rechaza donación con estado ERROR', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_ERROR'])
            ->withPayment()
            ->create();

        // Actualizar next_payment antes de marcar error
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->error_pago(['test' => 'error'], 'Test error');
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['state_is_active_or_pending'] === false;
            }))
            ->once();
    });

    test('verifica valores correctos de estados ACTIVA y PENDIENTE', function () {
        expect(OrderStatus::ACTIVA->value)->toBe('Activa')
            ->and(OrderStatus::PENDIENTE->value)->toBe('Pendiente de pago')
            ->and(OrderStatus::CANCELADO->value)->toBe('Cancelado')
            ->and(OrderStatus::ERROR->value)->toBe('ERROR')
            ->and(OrderStatus::PAGADO->value)->toBe('Pagado');
    });
});

describe('ProcessDonationPaymentJob - Validación has_identifier', function () {

    test('procesa donación con identifier válido', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'VALID_IDENTIFIER_XYZ'])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->identifier)->not->toBeNull()
            ->and($donacion->identifier)->not->toBeEmpty();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('rechaza donación sin identifier (null)', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => null])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->identifier)->toBeNull();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['has_identifier'] === false;
            }))
            ->once();
    });

    test('rechaza donación con identifier vacío', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => ''])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->identifier)->toBe('');

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['has_identifier'] === false;
            }))
            ->once();
    });
});

describe('ProcessDonationPaymentJob - Validación next_payment_is_due', function () {

    test('procesa donación con next_payment hoy', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'identifier' => 'ID_TODAY',
            ])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de la creación
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->next_payment)->toBe(now()->format('Y-m-d'));

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('procesa donación con next_payment atrasado', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'identifier' => 'ID_PAST',
            ])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de la creación
        $fechaPasada = now()->subDays(5)->format('Y-m-d');
        $donacion->update(['next_payment' => $fechaPasada]);
        $donacion->refresh();

        expect($donacion->next_payment)->toBe($fechaPasada)
            ->and($donacion->next_payment)->toBeLessThan(now()->format('Y-m-d'));

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::any())
            ->once();
    });

    test('rechaza donación con next_payment futuro', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_FUTURE'])
            ->withPayment()
            ->create();

        // Actualizar next_payment a una fecha futura
        $fechaFutura = now()->addDays(5)->format('Y-m-d');
        $donacion->update(['next_payment' => $fechaFutura]);
        $donacion->refresh();

        expect($donacion->next_payment)->toBe($fechaFutura)
            ->and($donacion->next_payment)->toBeGreaterThan(now()->format('Y-m-d'));

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['next_payment_is_due'] === false;
            }))
            ->once();
    });
});

describe('ProcessDonationPaymentJob - Método failed()', function () {

    test('marca donación como ERROR cuando el job falla', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_WILL_FAIL'])
            ->withPayment()
            ->create();

        // Actualizar next_payment (aunque no es necesario para este test)
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value);

        $job = new ProcessDonationPaymentJob($donacion);
        $exception = new RuntimeException('Test error');
        $job->failed($exception);

        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);

        Log::shouldHaveReceived('error')
            ->with('Job de pago recurrente falló definitivamente', Mockery::any())
            ->once();
    });

    test('no sobrescribe estado ERROR si ya estaba en ERROR', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_ALREADY_ERROR'])
            ->withPayment()
            ->create();

        // Actualizar next_payment antes de marcar error
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->error_pago(['previous' => 'error'], 'Previous error');
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);

        $job = new ProcessDonationPaymentJob($donacion);
        $exception = new RuntimeException('New error');
        $job->failed($exception);

        $donacion->refresh();

        // Sigue siendo ERROR
        expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);
    });

    test('registra log de error cuando failed() se ejecuta', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_LOG_TEST'])
            ->withPayment()
            ->create();

        // Actualizar next_payment (aunque no es necesario para este test)
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        $job = new ProcessDonationPaymentJob($donacion);
        $exception = new RuntimeException('Test exception message');
        $job->failed($exception);

        Log::shouldHaveReceived('error')
            ->with('Job de pago recurrente falló definitivamente', Mockery::on(function ($context) use ($donacion) {
                return $context['donation_id'] === $donacion->id
                    && $context['error_message'] === 'Test exception message';
            }))
            ->once();
    });

    test('registra error cuando failed() se ejecuta y no puede actualizar', function () {
        // Crear donación y luego eliminarla para simular error al actualizar
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_TO_DELETE'])
            ->withPayment()
            ->create();

        // Actualizar next_payment antes de eliminar
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionId = $donacion->id;
        $donacion->delete();

        $job = new ProcessDonationPaymentJob($donacion);
        $exception = new RuntimeException('Test error');

        // Ejecutar failed() - debería manejar el error silenciosamente
        $job->failed($exception);

        // Verificar que se registró el log principal del failed
        Log::shouldHaveReceived('error')
            ->with('Job de pago recurrente falló definitivamente', Mockery::on(function ($context) use ($donacionId) {
                return $context['donation_id'] === $donacionId;
            }))
            ->once();
    });
});

describe('ProcessDonationPaymentJob - Validación completa (todos los checks)', function () {

    test('procesa donación que cumple TODOS los requisitos', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: true));

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->state([
                'identifier' => 'ALL_VALID_CHECKS',
                'amount' => 50.00,
            ])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        // Verificar todos los requisitos
        expect($donacion->type)->toBe(DonationType::RECURRENTE->value)
            ->and($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
            ->and($donacion->identifier)->not->toBeEmpty()
            ->and($donacion->next_payment)->toBeLessThanOrEqual(now()->format('Y-m-d'));

        $paymentsCountBefore = $donacion->payments->count();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        $donacion->refresh();

        expect($donacion->payments)->toHaveCount($paymentsCountBefore + 1);

        Log::shouldHaveReceived('info')
            ->with('Pago recurrente procesado exitosamente', Mockery::on(function ($context) use ($donacion) {
                return $context['donation_id'] === $donacion->id
                    && isset($context['payment_id'])
                    && $context['success'] === true;
            }))
            ->once();
    });

    test('rechaza donación que falla múltiples checks', function () {
        // Crear donación que falla múltiples requisitos
        $donacion = Donation::factory()
            ->create([
                'type' => DonationType::UNICA->value, // Falla type
                'identifier' => null, // Falla identifier
                'next_payment' => now()->addDays(10)->format('Y-m-d'), // Falla next_payment (futura)
            ]);

        // Crear un pago para que tenga un estado
        $donacion->payments()->create([
            'number' => generatePaymentNumber($donacion),
            'amount' => $donacion->amount,
            'info' => [],
        ]);

        $donacion->cancel(); // Falla state (CANCELADO)
        $donacion->refresh();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        // Verificar que se registró warning por donación inválida
        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::any())
            ->once();
    });

    test('rechaza donación que falla check de type', function () {
        // Crear donación UNICA (no RECURRENTE) con todo lo demás válido
        $donacion = Donation::factory()
            ->create([
                'type' => DonationType::UNICA->value, // Solo este falla
                'identifier' => 'VALID_ID',
                'next_payment' => now()->format('Y-m-d'),
            ]);

        // Crear pago para que tenga un estado válido
        $donacion->payments()->create([
            'number' => generatePaymentNumber($donacion),
            'amount' => $donacion->amount,
            'info' => [],
        ]);

        $donacion->refresh();

        $job = new ProcessDonationPaymentJob($donacion);
        $job->handle();

        // Verificar que se registró warning por type inválido
        Log::shouldHaveReceived('warning')
            ->with('Donación no válida para procesar pago', Mockery::on(function ($context) {
                return $context['is_valid_check']['type_is_recurrente'] === false;
            }))
            ->once();
    });
});

describe('ProcessDonationPaymentJob - Manejo de excepciones', function () {

    test('registra información de attempts en logs de error', function () {
        app()->instance(RedsysGateway::class, new FakeRedsysGateway(ok: false)); // Simular fallo

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_FOR_ATTEMPTS'])
            ->withPayment()
            ->create();

        // Actualizar next_payment después de crear
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        $job = new ProcessDonationPaymentJob($donacion);

        try {
            $job->handle();
        } catch (Throwable) {
            // Capturar la excepción si se lanza
        }

        // Verificar que el log incluye información del attempt
        Log::shouldHaveReceived('info')
            ->with('Iniciando procesamiento de pago recurrente', Mockery::on(function ($context) use ($donacion) {
                return $context['donation_id'] === $donacion->id
                    && isset($context['attempt']);
            }))
            ->once();
    });
});

describe('ProcessDonationPaymentJob - Configuración del job', function () {

    test('tiene configuración correcta de reintentos', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->create();

        $job = new ProcessDonationPaymentJob($donacion);

        expect($job->tries)->toBe(3)
            ->and($job->timeout)->toBe(120)
            ->and($job->backoff())->toBe([30, 60, 120]);
    });

    test('usa la cola correcta', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->create();

        $job = new ProcessDonationPaymentJob($donacion);

        expect($job->queue)->toBe('payments');
    });

    test('usa middleware WithoutOverlapping correctamente', function () {
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->create();

        $job = new ProcessDonationPaymentJob($donacion);
        $middleware = $job->middleware();

        expect($middleware)->toHaveCount(1)
            ->and($middleware[0])->toBeInstanceOf(WithoutOverlapping::class);
    });
});
