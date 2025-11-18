<?php

use Darkraul79\Cartify\CartifyServiceProvider;

uses()->group('cartify');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeCart', function () {
    return $this->toBeInstanceOf(Darkraul79\Cartify\CartManager::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function getPackageProviders($app): array
{
    return [
        CartifyServiceProvider::class,
    ];
}
