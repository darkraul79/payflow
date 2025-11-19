<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;

test('test básico de configuración del job', function () {
    $donacion = Donation::factory()
        ->recurrente(DonationFrequency::MENSUAL->value)
        ->create();

    $job = new ProcessDonationPaymentJob($donacion);

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(120)
        ->and($job->queue)->toBe('payments');
});

test('test de validación sin ejecutar job', function () {
    $donacion = Donation::factory()
        ->recurrente(DonationFrequency::MENSUAL->value)
        ->state(['identifier' => 'TEST_ID'])
        ->withPayment()
        ->create();

    // Actualizar next_payment después de crear (withPayment establece día 5 del próximo mes)
    $donacion->update(['next_payment' => now()->format('Y-m-d')]);
    $donacion->refresh();

    expect($donacion->type)->toBe(DonationType::RECURRENTE->value)
        ->and($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->identifier)->toBe('TEST_ID')
        ->and($donacion->next_payment)->toBe(now()->format('Y-m-d'));
});
