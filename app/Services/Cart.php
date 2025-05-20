<?php

namespace App\Services;

use App\Models\Product;

class Cart
{
    private static array $cart = [];

    public static function addItem(Product $product, $quantity = 1): void
    {
        self::init();

        if (isset(self::$cart['items'][$product->id])) {
            self::$cart['items'][$product->id]['quantity'] += $quantity;
            self::$cart['items'][$product->id]['subtotal'] = $product->getPrice() * self::$cart['items'][$product->id]['quantity'];
        } else {
            self::$cart['items'][$product->id] = [
                'name' => $product->name,
                'price' => $product->getPrice(),
                'quantity' => $quantity,
                'subtotal' => $product->getPrice() * $quantity,
            ];
        }


        self::save();
    }

    public static function init(): void
    {
        self::$cart = session()->get('cart') ?? ['items' => []];
    }

    public static function save(): void
    {
        session()->put('cart', self::$cart);
    }

    public static function updateCart(): void
    {
        self::init();

        $total = 0;
        foreach (self::getItems() as $id => $item) {
            $product = Product::find($id);
            $subtotal = $product->getPrice() * $item['quantity'];
            self::$cart['items'][$id]['subtotal'] = $subtotal;
            $total += $subtotal;
        }

        self::save();
    }

    public static function getItems(): array
    {
        self::init();

        return self::$cart['items'] ?? [];
    }

    public static function removeItem($productId): void
    {
        self::init();

        if (isset(self::$cart['items'][$productId])) {
            unset(self::$cart['items'][$productId]);
        }
        session()->flash('danger', 'Producto eliminado del carrito');
        self::save();
    }

    public static function clearCart(): void
    {
        session()->forget('cart');

        session()->flash('warning', 'Se ha vaciado el carrito');
        self::$cart = [];
    }

    public static function getTotalPrice(): float
    {
        self::init();

        $total = 0;
        foreach (self::getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public static function getTotalQuantity(): float
    {
        self::init();

        $totalQuantity = 0;
        foreach (self::getItems() as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }
}
