<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShippingMethod;
use Darkraul79\Cartify\Facades\Cart as Cartify;

/**
 * Adaptador para mantener la API histórica usada en tests mientras se delega
 * en el paquete Cartify.
 */
class Cart
{
    /**
     * Añade un producto usando la API de Cartify acumulando la cantidad.
     */
    public static function addItem(Product $product, int $quantity = 1): void
    {
        // Verificar stock antes de agregar (comportamiento previo)
        $current = self::getQuantityProduct($product->id);
        if ($current + $quantity > $product->stock) {
            return; // mismo early return que antes
        }

        Cartify::add(
            id: $product->id,
            name: $product->name,
            quantity: $quantity,
            price: $product->getPrice(),
            options: [
                'image' => $product->getFirstMediaUrl('product_images', 'thumb'),
                'price_formated' => $product->getFormatedPriceWithDiscount(),
            ]
        );
    }

    /**
     * Cantidad de un producto concreto.
     */
    public static function getQuantityProduct(int|string $productId): int
    {
        $item = Cartify::get($productId);

        return $item['quantity'] ?? 0;
    }

    /**
     * Devuelve cantidad total de artículos (sumatoria de cantidades).
     */
    public static function getTotalQuantity(): int
    {
        return Cartify::count();
    }

    /**
     * Devuelve array de items con estructura compatible.
     */
    public static function getItems(): array
    {
        // Añadir campos calculados para compatibilidad (subtotal / subtotal_formated)
        return collect(Cartify::content())->map(function ($item) {
            $subtotal = $item['price'] * $item['quantity'];
            $item['subtotal'] = $subtotal;
            $item['subtotal_formated'] = convertPrice($subtotal);

            return $item;
        })->toArray();
    }

    /**
     * Elimina un producto del carrito.
     */
    public static function removeItem(int|string $productId): void
    {
        if (Cartify::has($productId)) {
            Cartify::remove($productId);
        }
    }

    /**
     * Vacía el carrito por completo.
     */
    public static function clearCart(): void
    {
        Cartify::clear();
    }

    /**
     * Total precio bruto (sin impuestos adicionales externos).
     */
    public static function getTotalPrice(): float
    {
        return Cartify::subtotal();
    }

    /**
     * Shipping method ID actual desde sesión (mantener compatibilidad tests).
     */
    public static function getShippingMethod(): bool|int
    {
        return session('cart_shipping_method_id', false);
    }

    /**
     * Shipping method cost actual desde sesión.
     */
    public static function getShippingMethodCost(): bool|float
    {
        return session('cart_shipping_cost', false);
    }

    /**
     * Definir método de envío (para compatibilidad, sigue guardando en sesión)
     */
    public static function setShippingMethod(ShippingMethod $method): void
    {
        session([
            'cart_shipping_method_id' => $method->id,
            'cart_shipping_cost' => $method->price,
            'cart_shipping_name' => $method->name,
        ]);
    }
}
