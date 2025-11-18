<?php

namespace App\Services;

use Darkraul79\Cartify\Facades\Cart;

/**
 * Normaliza los items del carrito a la estructura legacy esperada por vistas y tests.
 */
final class CartNormalizer
{
    /**
     * Retorna los items normalizados del carrito.
     *
     * @return array<int|string, array{
     *     price: int|float,
     *     quantity: int,
     *     subtotal: int|float,
     *     subtotal_formated: string,
     *     price_formated: string,
     *     image?: string,
     *     options?: array
     * }>
     */
    public static function items(): array
    {
        $raw = Cart::content()->toArray();
        if (empty($raw)) {
            return [];
        }

        $normalized = [];
        foreach ($raw as $key => $item) {
            $normalized[$key] = self::normalizeItem((array) $item);
        }

        return $normalized;
    }

    /**
     * Normaliza un item individual del carrito.
     *
     * @param  array{price?: int|float, quantity?: int|float, subtotal?: int|float, subtotal_formated?: string, price_formated?: string, image?: string, options?: array}  $item
     * @return array{price: int|float, quantity: int, subtotal: int|float, subtotal_formated: string, price_formated: string, image?: string, options?: array}
     */
    private static function normalizeItem(array $item): array
    {
        $price = $item['price'] ?? 0;
        $quantity = (int) ($item['quantity'] ?? 0);
        $subtotal = (float) ($item['subtotal'] ?? ($price * $quantity));

        // Asignaciones idempotentes
        $item['price'] = $price;
        $item['quantity'] = $quantity;
        $item['subtotal'] = $subtotal;
        $item['subtotal_formated'] = $item['subtotal_formated'] ?? convertPrice($subtotal);
        $item['price_formated'] = $item['price_formated'] ?? convertPrice($price);

        if (! isset($item['image']) && isset($item['options']) && isset($item['options']['image'])) {
            $item['image'] = $item['options']['image'];
        }

        return $item;
    }

    /**
     * Devuelve totales normalizados del carrito.
     *
     * @return array{subtotal: float, shipping_cost: float, taxes: float, total: float}
     */
    public static function totals(): array
    {
        $subtotal = (float) Cart::subtotal();
        $shipping = ShippingSession::cost();
        $total = $subtotal + $shipping;
        $taxes = calculoImpuestos($total);

        return [
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'taxes' => $taxes,
            'total' => $total,
        ];
    }
}
