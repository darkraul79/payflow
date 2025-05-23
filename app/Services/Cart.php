<?php

namespace App\Services;

use App\Models\Product;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Cart
{
    private static array $cart = [];

    public static function addItem(Product $product, $quantity = 1): void
    {
        self::init();

        if (isset(self::$cart['items'][$product->id])) {
            if (Product::find($product->id)->stock < self::$cart['items'][$product->id]['quantity'] + $quantity) {
                return;
            }
            self::$cart['items'][$product->id]['quantity'] += $quantity;
            self::$cart['items'][$product->id]['subtotal'] = $product->getPrice() * self::$cart['items'][$product->id]['quantity'];
            self::$cart['items'][$product->id]['subtotal_formated'] = convertPrice(self::$cart['items'][$product->id]['subtotal']);
        } else {
            self::$cart['items'][$product->id] = [
                'name' => $product->name,
                'price' => $product->getPrice(),
                'price_formated' => $product->getFormatedPriceWithDiscount(),
                'quantity' => $quantity,
                'subtotal' => $product->getPrice() * $quantity,
                'subtotal_formated' => convertPrice($product->getPrice() * $quantity),
                'image' => $product->getFirstMediaUrl('product_images', 'thumb') ?? null,
            ];
        }

        self::save();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function init(): void
    {
        self::$cart = session()->get('cart') ?? ['items' => []];
    }

    public static function save(): void
    {
        session()->put('cart', self::$cart);
    }

    public static function updateItemQuantity(Product $product, $quantity): void
    {
        self::init();

        if (isset(self::$cart['items'][$product->id])) {
            self::$cart['items'][$product->id]['quantity'] = $quantity;
            self::$cart['items'][$product->id]['subtotal'] = $product->getPrice() * $quantity;
            self::$cart['items'][$product->id]['subtotal_formated'] = convertPrice(self::$cart['items'][$product->id]['subtotal']);
        }

        self::save();
    }

    public static function updateCart(): void
    {
        self::init();

        $total = 0;
        foreach (self::getItems() as $id => $item) {
            $product = Product::find($id);
            $subtotal = $product->getPrice() * $item['quantity'];
            self::$cart['items'][$id]['subtotal'] = $subtotal;
            self::$cart['items'][$id]['img'] = $product->getFirstMediaUrl('product_images', 'thumb') ?? null;
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
        self::save();
    }

    public static function clearCart(): void
    {
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

    public static function getTotalQuantity(): int
    {
        self::init();

        $totalQuantity = 0;
        foreach (self::getItems() as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }

    public static function setTotals(float $subtotal, float $taxes, float $total, float $shipping_cost): void
    {
        self::init();

        self::$cart['totals'] = [
            'shipping_cost' => $shipping_cost,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ];

        self::save();
    }

    public static function canCheckout(): bool
    {
        self::init();

        if (
            empty(self::$cart) ||
            empty(self::$cart['items']) ||
            empty(self::$cart['totals']['subtotal']) ||
            empty(self::$cart['totals']['taxes']) ||
            empty(self::$cart['totals']['total'])
        ) {
            return false;
        }

        return true;
    }

    public static function getQuantityProduct($productId): int
    {
        self::init();

        if (isset(self::$cart['items'][$productId])) {
            return self::$cart['items'][$productId]['quantity'];
        }

        return 0;
    }
}
