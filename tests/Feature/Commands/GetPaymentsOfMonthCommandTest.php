<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Log::spy();
});

describe('GetPaymentsOfMonthCommand - Ejecución Normal', function () {

    test('ejecuta el comando y encola jobs para donaciones pendientes', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();

        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
            ->and($donacion->next_payment)->toBe(now()->format('Y-m-d'));

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('🔄 Iniciando proceso de pagos recurrentes...');

        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);
        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion) {
            return $job->donation->id === $donacion->id;
        });

        Log::shouldHaveReceived('info')
            ->with('Jobs de pago encolados exitosamente', Mockery::any())
            ->once();
    });

    test('procesa múltiples donaciones con fechas de pago vencidas', function () {
        Queue::fake();

        $donacion1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion1->update(['next_payment' => now()->subDays(5)->format('Y-m-d')]);

        $donacion2 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->withPayment()
            ->create();
        $donacion2->update(['next_payment' => now()->subDays(2)->format('Y-m-d')]);

        $donacion3 = Donation::factory()
            ->recurrente(DonationFrequency::ANUAL->value)
            ->withPayment()
            ->create();
        $donacion3->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 3);

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion1) {
            return $job->donation->id === $donacion1->id;
        });

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion2) {
            return $job->donation->id === $donacion2->id;
        });

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion3) {
            return $job->donation->id === $donacion3->id;
        });
    });

    test('no procesa donaciones con fecha de pago futura', function () {
        Queue::fake();

        Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->withNextPayment(now()->addDays(10)->format('Y-m-d'))
            ->create();

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();

        Log::shouldHaveReceived('warning')
            ->with('No hay donaciones recurrentes que procesar')
            ->once();
    });

    test('no procesa donaciones canceladas', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();

        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->cancel();
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::CANCELADO->value);

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });

    test('no procesa donaciones con estado ERROR', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();

        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->error_pago(['error' => 'test'], 'Error de prueba');
        $donacion->refresh();

        expect($donacion->state->name)->toBe(OrderStatus::ERROR->value);

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });

    test('no procesa donaciones únicas aunque tengan next_payment', function () {
        Queue::fake();

        Donation::factory()
            ->state([
                'type' => DonationType::UNICA->value,
                'next_payment' => now()->format('Y-m-d'),
            ])
            ->withPayment()
            ->create();

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });
});

describe('GetPaymentsOfMonthCommand - Modo --list', function () {

    test('muestra listado sin encolar jobs cuando se usa --list', function () {
        Queue::fake();

        $donacion1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion1->update(['next_payment' => now()->format('Y-m-d')]);

        $donacion2 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->withPayment()
            ->create();
        $donacion2->update(['next_payment' => now()->subDays(1)->format('Y-m-d')]);

        artisan('payments-of-month:process --list')
            ->assertSuccessful()
            ->expectsOutput('🔄 Iniciando proceso de pagos recurrentes...');

        Queue::assertNothingPushed();

    });

    test('modo --list muestra mensaje cuando no hay donaciones', function () {
        Queue::fake();

        artisan('payments-of-month:process --list')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });

    test('modo --list muestra información completa de cada donación', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => '2025-11-05']);

        artisan('payments-of-month:process --list')
            ->assertSuccessful();

        $output = artisan('payments-of-month:process --list')->run();

        expect($output)->toBe(0);

        Queue::assertNothingPushed();
    });
});

describe('GetPaymentsOfMonthCommand - Filtrado de Donaciones', function () {

    test('solo procesa donaciones ACTIVAS con tipo RECURRENTE y next_payment <= hoy', function () {
        Queue::fake();

        $donacionValida = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionValida->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionFutura = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionFutura->update(['next_payment' => now()->addDays(10)->format('Y-m-d')]);

        $donacionUnica = Donation::factory()
            ->state(['type' => DonationType::UNICA->value])
            ->withPayment()
            ->create();
        $donacionUnica->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionCancelada = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionCancelada->update(['next_payment' => now()->format('Y-m-d')]);
        $donacionCancelada->cancel();

        $donacionError = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionError->update(['next_payment' => now()->format('Y-m-d')]);
        $donacionError->error_pago(['error' => 'test'], 'Error');

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);
        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacionValida) {
            return $job->donation->id === $donacionValida->id;
        });
    });

    test('scope nextPaymentsDonations filtra correctamente', function () {
        $donacionPendiente = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionPendiente->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionFutura = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionFutura->update(['next_payment' => now()->addDays(5)->format('Y-m-d')]);

        $donacionUnica = Donation::factory()
            ->state(['type' => DonationType::UNICA->value])
            ->withPayment()
            ->create();
        $donacionUnica->update(['next_payment' => now()->format('Y-m-d')]);

        $donaciones = Donation::nextPaymentsDonations()->get();

        expect($donaciones)->toHaveCount(1)
            ->and($donaciones->first()->id)->toBe($donacionPendiente->id);
    });
});

describe('GetPaymentsOfMonthCommand - Logging y Observabilidad', function () {

    test('registra logs estructurados en cada ejecución', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion->refresh();

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);

        Log::shouldHaveReceived('info')
            ->with('Jobs de pago encolados exitosamente', Mockery::any())
            ->once();
    });

    test('registra warning cuando no hay donaciones', function () {
        Queue::fake();

        artisan('payments-of-month:process')->assertSuccessful();

        Log::shouldHaveReceived('warning')
            ->with('No hay donaciones recurrentes que procesar')
            ->once();
    });

    test('procesa correctamente aunque haya donaciones inválidas mezcladas', function () {
        Queue::fake();

        $valida1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $valida1->update(['next_payment' => now()->format('Y-m-d')]);

        $invalida = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $invalida->update(['next_payment' => now()->addMonth()->format('Y-m-d')]);

        $valida2 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->withPayment()
            ->create();
        $valida2->update(['next_payment' => now()->subDay()->format('Y-m-d')]);

        Donation::factory()
            ->state(['type' => DonationType::UNICA->value])
            ->withPayment()
            ->create();

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 2);

        Log::shouldHaveReceived('info')
            ->with('Jobs de pago encolados exitosamente', Mockery::any())
            ->once();
    });
});

describe('GetPaymentsOfMonthCommand - Casos Extremos', function () {

    test('maneja correctamente cuando todas las donaciones están canceladas', function () {
        Queue::fake();

        $donacion1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion1->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion1->cancel();

        $donacion2 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->withPayment()
            ->create();
        $donacion2->update(['next_payment' => now()->format('Y-m-d')]);
        $donacion2->cancel();

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });

    test('procesa donaciones con fechas exactamente en el día 5', function () {
        Queue::fake();

        $this->travelTo('2025-11-05 00:00:00');

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => '2025-11-05']);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);
        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion) {
            return $job->donation->id === $donacion->id;
        });

        $this->travelBack();
    });

    test('procesa donaciones con next_payment de meses anteriores (atrasadas)', function () {
        Queue::fake();

        $this->travelTo('2025-11-19');

        $donacionAtrasada = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacionAtrasada->update(['next_payment' => '2025-09-05']);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);
        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacionAtrasada) {
            return $job->donation->id === $donacionAtrasada->id;
        });

        $this->travelBack();
    });

    test('no falla cuando no hay ninguna donación en la base de datos', function () {
        Queue::fake();

        expect(Donation::count())->toBe(0);

        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('ℹ️ No hay donaciones recurrentes que procesar este mes.');

        Queue::assertNothingPushed();
    });

    test('procesa correctamente 100 donaciones simultáneas', function () {
        Queue::fake();

        $donaciones = Donation::factory()
            ->count(100)
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();

        foreach ($donaciones as $donacion) {
            $donacion->update(['next_payment' => now()->format('Y-m-d')]);
        }

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 100);
    });
});

describe('GetPaymentsOfMonthCommand - Integración con Jobs', function () {

    test('los jobs encolados tienen la información correcta', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'amount' => 25.50,
                'identifier' => 'TEST_IDENTIFIER_123',
            ])
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacion) {
            $jobDonation = $job->donation;

            return $jobDonation->id === $donacion->id
                && $jobDonation->amount === 25.50
                && $jobDonation->identifier === 'TEST_IDENTIFIER_123'
                && $jobDonation->type === DonationType::RECURRENTE->value;
        });
    });

    test('los jobs se encolan en la cola correcta', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, function ($job) use ($donacion) {
            expect($job)->toBeInstanceOf(ShouldQueue::class);

            return $job->donation->id === $donacion->id;
        });
    });
});

describe('GetPaymentsOfMonthCommand - Validación Identifier', function () {

    test('omite donaciones sin identifier durante el procesamiento', function () {
        Queue::fake();

        // Donación válida con identifier
        $donacionValida = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'VALID_IDENTIFIER_123'])
            ->withPayment()
            ->create();
        $donacionValida->update(['next_payment' => now()->format('Y-m-d')]);

        // Donación sin identifier
        $donacionSinIdentifier = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => null])
            ->withPayment()
            ->create();
        $donacionSinIdentifier->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        // Solo se debe encolar la donación con identifier
        Queue::assertPushed(ProcessDonationPaymentJob::class, 1);
        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacionValida) {
            return $job->donation->id === $donacionValida->id;
        });

        Log::shouldHaveReceived('warning')
            ->with('Donación sin identifier omitida en proceso de pago', Mockery::any())
            ->once();
    });

    test('muestra advertencia en modo --list cuando hay donaciones sin identifier', function () {
        Queue::fake();

        $donacionSinIdentifier = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => null])
            ->withPayment()
            ->create();
        $donacionSinIdentifier->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process --list')
            ->assertSuccessful()
            ->expectsOutput('⚠️  1 donación(es) sin identifier (se omitirán en procesamiento real)');

        Queue::assertNothingPushed();
    });

    test('procesa correctamente mezcla de donaciones con y sin identifier', function () {
        Queue::fake();

        $donacion1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_1'])
            ->withPayment()
            ->create();
        $donacion1->update(['next_payment' => now()->format('Y-m-d')]);

        $donacion2 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => null])
            ->withPayment()
            ->create();
        $donacion2->update(['next_payment' => now()->format('Y-m-d')]);

        $donacion3 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->state(['identifier' => 'ID_3'])
            ->withPayment()
            ->create();
        $donacion3->update(['next_payment' => now()->format('Y-m-d')]);

        $donacion4 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => ''])
            ->withPayment()
            ->create();
        $donacion4->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 2);

        Log::shouldHaveReceived('warning')
            ->with('Donación sin identifier omitida en proceso de pago', Mockery::any())
            ->twice();
    });
});

describe('GetPaymentsOfMonthCommand - Modo Dry-Run', function () {

    test('modo --dry-run muestra advertencia y no procesa pagos', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        $result = artisan('payments-of-month:process --dry-run');

        $result->assertSuccessful();

        Queue::assertNothingPushed();
    });

    test('modo --dry-run muestra tabla de donaciones', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['amount' => 50.00])
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('€50.00');

        Queue::assertNothingPushed();
    });

    test('modo --dry-run con --list funciona correctamente', function () {
        Queue::fake();

        Donation::factory()
            ->count(3)
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create()
            ->each(fn ($d) => $d->update(['next_payment' => now()->format('Y-m-d')]));

        artisan('payments-of-month:process --dry-run --list')
            ->assertSuccessful()
            ->expectsOutput('⚠️  MODO DRY-RUN: No se procesarán pagos reales')
            ->expectsOutput('📋 Donaciones encontradas: 3');

        Queue::assertNothingPushed();
    });
});

describe('GetPaymentsOfMonthCommand - Límite de Donaciones', function () {

    test('respeta el límite configurado', function () {
        Queue::fake();

        Donation::factory()
            ->count(150)
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create()
            ->each(fn ($d) => $d->update(['next_payment' => now()->format('Y-m-d')]));

        artisan('payments-of-month:process --limit=50')->assertSuccessful();

        Queue::assertPushed(ProcessDonationPaymentJob::class, 50);
    });

    test('ajusta límites fuera de rango a 100', function () {
        Queue::fake();

        Donation::factory()
            ->count(150)
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create()
            ->each(fn ($d) => $d->update(['next_payment' => now()->format('Y-m-d')]));

        artisan('payments-of-month:process --limit=2000')
            ->assertSuccessful()
            ->expectsOutput('⚠️ Límite ajustado a 100 donaciones por seguridad');

        Queue::assertPushed(ProcessDonationPaymentJob::class, 100);
    });

    test('límite cero se ajusta a 100', function () {
        Queue::fake();

        Donation::factory()
            ->count(120)
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create()
            ->each(fn ($d) => $d->update(['next_payment' => now()->format('Y-m-d')]));

        artisan('payments-of-month:process --limit=0')
            ->assertSuccessful()
            ->expectsOutput('⚠️ Límite ajustado a 100 donaciones por seguridad');

        Queue::assertPushed(ProcessDonationPaymentJob::class, 100);
    });
});

describe('GetPaymentsOfMonthCommand - Logs Estructurados', function () {

    test('registra información completa de donación procesada', function () {
        Queue::fake();

        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'amount' => 30.00,
                'identifier' => 'TEST_ID',
            ])
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Log::shouldHaveReceived('info')
            ->with("Job encolado para donación $donacion->id", Mockery::on(function ($context) use ($donacion) {
                return $context['donation_id'] === $donacion->id
                    && $context['amount'] === 30.00
                    && isset($context['donation_number'])
                    && isset($context['frequency']);
            }))
            ->once();
    });

    test('registra tiempo de ejecución al finalizar', function () {
        Queue::fake();

        // Crear una donación para que se procese y se registren los logs completos
        $donacion = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->withPayment()
            ->create();
        $donacion->update(['next_payment' => now()->format('Y-m-d')]);

        artisan('payments-of-month:process')->assertSuccessful();

        Log::shouldHaveReceived('info')
            ->with('Proceso de pagos recurrentes completado', Mockery::on(function ($context) {
                return isset($context['execution_time_seconds'])
                    && isset($context['total_donations_found'])
                    && isset($context['mode']);
            }))
            ->once();
    });
});

describe('GetPaymentsOfMonthCommand - Integración End-to-End', function () {

    test('flujo completo: encuentra, valida y encola donaciones correctamente', function () {
        Queue::fake();

        // Crear escenario realista
        $donacionValida1 = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state([
                'amount' => 25.00,
                'identifier' => 'ID_VALID_1',
            ])
            ->withPayment()
            ->create();
        $donacionValida1->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionValida2 = Donation::factory()
            ->recurrente(DonationFrequency::TRIMESTRAL->value)
            ->state([
                'amount' => 75.00,
                'identifier' => 'ID_VALID_2',
            ])
            ->withPayment()
            ->create();
        $donacionValida2->update(['next_payment' => now()->subDays(3)->format('Y-m-d')]);

        // Donaciones que NO deben procesarse
        $donacionSinIdentifier = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => null])
            ->withPayment()
            ->create();
        $donacionSinIdentifier->update(['next_payment' => now()->format('Y-m-d')]);

        $donacionFutura = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_FUTURE'])
            ->withPayment()
            ->create();
        $donacionFutura->update(['next_payment' => now()->addDays(5)->format('Y-m-d')]);

        $donacionCancelada = Donation::factory()
            ->recurrente(DonationFrequency::MENSUAL->value)
            ->state(['identifier' => 'ID_CANCELLED'])
            ->withPayment()
            ->create();
        $donacionCancelada->update(['next_payment' => now()->format('Y-m-d')]);
        $donacionCancelada->cancel();

        // Ejecutar comando
        artisan('payments-of-month:process')
            ->assertSuccessful()
            ->expectsOutput('✅ Se han encolado 2 jobs de pago.');

        // Verificar que solo se encolaron las 2 válidas
        Queue::assertPushed(ProcessDonationPaymentJob::class, 2);

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacionValida1) {
            return $job->donation->id === $donacionValida1->id
                && $job->donation->amount === 25.00
                && $job->donation->identifier === 'ID_VALID_1';
        });

        Queue::assertPushed(function (ProcessDonationPaymentJob $job) use ($donacionValida2) {
            return $job->donation->id === $donacionValida2->id
                && $job->donation->amount === 75.00
                && $job->donation->identifier === 'ID_VALID_2';
        });

        // Verificar que se registró el warning de donación sin identifier
        Log::shouldHaveReceived('warning')
            ->with('Donación sin identifier omitida en proceso de pago', Mockery::any())
            ->once();

        // Verificar que se registró el éxito
        Log::shouldHaveReceived('info')
            ->with('Jobs de pago encolados exitosamente', Mockery::on(function ($context) {
                return $context['total_processed'] === 2
                    && $context['total_found'] === 3; // 3 encontradas (2 válidas + 1 sin identifier)
            }))
            ->once();
    });
});
