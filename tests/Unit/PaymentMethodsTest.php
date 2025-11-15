<?php

use App\Enums\PaymentMethod;
use App\Support\PaymentMethodRepository;

it('lista todos los métodos', function () {
    $repo = new PaymentMethodRepository;
    $all = $repo->all();

    expect($all)->toHaveCount(count(PaymentMethod::cases()))
        ->and($all->first()->toArray())->toHaveKeys([
            'code',
            'label',
            'supportsRecurring',
        ]);
});

it('encuentra un método válido', function () {
    $repo = new PaymentMethodRepository;
    $data = $repo->find('bizum');

    expect($data)->not()->toBeNull()
        ->and($data->toArray()['supportsRecurring'])->toBeFalse();
});

it('devuelve null para método inválido', function () {
    $repo = new PaymentMethodRepository;
    expect($repo->find('nope'))->toBeNull();
});

it('valida existencia', function () {
    $repo = new PaymentMethodRepository;
    expect($repo->exists('bizum'))->toBeTrue()
        ->and($repo->exists('tarjeta'))->toBeTrue()
        ->and($repo->exists('xyz'))->toBeFalse();
});
