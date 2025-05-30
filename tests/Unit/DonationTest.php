<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Donation;

test('puedo crear donación única por defecto en factory', function () {

    $donation = Donation::factory()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::UNICA)
        ->and($donation->identifier)->toBeNull();
});

test('puedo crear donación recurrente por defecto en factory', function () {

    $donation = Donation::factory()->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::RECURRENTE)
        ->and($donation->identifier)->not->toBeNull();
});

test('puedo crear donación única con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::UNICA)
        ->and($donation->identifier)->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo crear donación recurrente con muchos pagos en factory', function () {

    $donation = Donation::factory()->hasPayments(3)->recurrente()->create();
    expect($donation->info)->toBeObject()
        ->and($donation->type)->toBe(Donation::RECURRENTE)
        ->and($donation->identifier)->not->toBeNull()
        ->and($donation->payments)->toHaveCount(3);
});

test('puedo asociar dirección de certificado a donación en factory', function () {
    $donation = Donation::factory()->withCertificado()->create();


    expect($donation->addresses->first())->toBeInstanceOf(Address::class)
        ->and($donation->addresses)->toHaveCount(1)
        ->and($donation->addresses->first()->type)->toBe(Address::CERTIFICATE)
        ->and($donation->address)->toBeInstanceOf(Address::class)
        ->and($donation->address->type)->toBe(Address::CERTIFICATE);
});
