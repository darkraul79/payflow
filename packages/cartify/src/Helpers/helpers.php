<?php

use Darkraul79\Cartify\CartManager;

if (! function_exists('cart')) {
    /**
     * Get the Cart manager instance
     */
    function cart(?string $instance = null): CartManager
    {
        $cart = app('cart');

        if ($instance !== null) {
            return $cart->instance($instance);
        }

        return $cart;
    }
}

if (! function_exists('format_price')) {
    /**
     * Format price with currency
     */
    function format_price(float $price, ?string $currency = null): string
    {
        $currency = $currency ?? config('cartify.currency_symbol', '€');

        return number_format($price, 2, ',', '.').' '.$currency;
    }
}

if (! function_exists('generate_order_number')) {
    /**
     * Generate order number based on config format
     */
    function generate_order_number(): string
    {
        $format = config('cartify.order_number_format', 'ORD-{year}{month}-{random}');
        $randomLength = config('cartify.order_number_random_length', 6);

        $replacements = [
            '{year}' => date('Y'),
            '{month}' => date('m'),
            '{day}' => date('d'),
            '{random}' => strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, $randomLength)),
            '{increment}' => str_pad((string) (app('db')->table('orders')->count() + 1), 6, '0', STR_PAD_LEFT),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }
}
