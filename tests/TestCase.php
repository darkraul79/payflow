<?php

namespace Darkraul79\Payflow\Tests;

use Darkraul79\Payflow\PayflowServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PayflowServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('payflow.default', 'redsys');
        config()->set('payflow.gateways.redsys', [
            'key' => 'test-key',
            'merchant_code' => '999008881',
            'terminal' => '1',
            'currency' => '978',
            'transaction_type' => '0',
            'trade_name' => 'Test Store',
            'environment' => 'test',
            'version' => 'HMAC_SHA256_V1',
        ]);
    }
}
