<?php

namespace Darkraul79\Cartify\Tests;

use Darkraul79\Cartify\CartifyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CartifyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('cartify.tax_rate', 0.21);
        config()->set('cartify.currency', 'EUR');
        config()->set('cartify.currency_symbol', '€');
    }
}
