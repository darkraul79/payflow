<?php

use Darkraul79\Payflow\Contracts\GatewayInterface;
use Darkraul79\Payflow\GatewayManager;

if (! function_exists('gateway')) {
    /**
     * Get the Gateway manager instance
     */
    function gateway(?string $name = null): GatewayInterface|GatewayManager
    {
        if ($name === null) {
            return app('gateway');
        }

        return app('gateway')->gateway($name);
    }
}

if (! function_exists('convert_amount_to_redsys')) {
    /**
     * Convert amount to Redsys format (cents)
     */
    function convert_amount_to_redsys(float $amount): string
    {
        return (string) round($amount * 100);
    }
}

if (! function_exists('convert_amount_from_redsys')) {
    /**
     * Convert amount from Redsys format to float
     */
    function convert_amount_from_redsys(string $amount): float
    {
        return (float) ($amount / 100);
    }
}
