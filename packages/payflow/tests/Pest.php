<?php

use Darkraul79\Payflow\PayflowServiceProvider;

uses()->group('payflow');

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeGateway', function () {
    return $this->toBeInstanceOf(Darkraul79\Payflow\Contracts\GatewayInterface::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function getPackageProviders($app): array
{
    return [
        PayflowServiceProvider::class,
    ];
}
