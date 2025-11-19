<?php

it('gateway helper returns gateway manager', function () {
    $gateway = gateway();

    expect($gateway)->toBeInstanceOf(Darkraul79\Payflow\PayflowManager::class);
});

it('gateway helper can get specific gateway', function () {
    $redsys = gateway('redsys');

    expect($redsys)->toBeGateway()
        ->and($redsys->getName())->toBe('redsys');
});

it('convert_amount_to_redsys works correctly', function () {
    expect(convert_amount_to_redsys(100.50))->toBe('10050')
        ->and(convert_amount_to_redsys(1.00))->toBe('100')
        ->and(convert_amount_to_redsys(0.50))->toBe('50');
});

it('convert_amount_from_redsys works correctly', function () {
    expect(convert_amount_from_redsys('10050'))->toBe(100.50)
        ->and(convert_amount_from_redsys('100'))->toBe(1.00)
        ->and(convert_amount_from_redsys('50'))->toBe(0.50);
});
