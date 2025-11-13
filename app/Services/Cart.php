<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShippingMethod;
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
        } else {
            self::$cart['items'][$product->id] = [
                'name' => $product->name,
                'price' => $product->getPrice(),
                'price_formated' => $product->getFormatedPriceWithDiscount(),
                'quantity' => $quantity,
                'image' => self::getImage($product),
            ];
        }
        self::updateProductSubtotal($product);

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

    public static function getImage(Product|array|null $product): ?string
    {
        return $product->getFirstMediaUrl('product_images', 'thumb') ?? null;
    }

    public static function updateProductSubtotal(Product $product): void
    {
        self::$cart['items'][$product->id]['subtotal'] = $product->getPrice() * self::$cart['items'][$product->id]['quantity'];
        self::$cart['items'][$product->id]['subtotal_formated'] = convertPrice(self::$cart['items'][$product->id]['subtotal']);
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
            self::updateProductSubtotal($product);
        }

        self::save();
    }

    public static function updateCart(): void
    {
        self::init();

        foreach (self::getItems() as $id => $item) {
            $product = Product::find($id);
            self::updateProductSubtotal($product);
            self::$cart['items'][$id]['img'] = self::getImage($product);
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
        self::save();
    }

    public static function resetCart(): void
    {
        session()->forget('cart');
        self::init();
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
            empty(self::$cart['totals']['total']) ||
            empty(self::$cart['shipping_method'])
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

    public static function resfreshCart(): void
    {
        foreach (self::getItems() as $idProduct => $item) {

            $product = Product::find($idProduct);
            self::$cart['items'][$idProduct]['name'] = $product->name;
            self::$cart['items'][$idProduct]['price'] = $product->getPrice();
            self::$cart['items'][$idProduct]['price_formated'] = $product->getFormatedPriceWithDiscount();
            self::updateProductSubtotal($product);
            self::$cart['items'][$idProduct]['img'] = self::getImage($product);

        }
        self::$cart['totals']['subtotal'] = 0;
        self::$cart['totals']['taxes'] = 0;
        self::$cart['totals']['total'] = 0;
        self::$cart['totals']['shipping_cost'] = 0;
    }

    public static function setShippingMethod(ShippingMethod $method): void
    {
        self::init();

        self::$cart['shipping_method'] = [
            'id' => $method->id,
            'price' => $method->price,
            'name' => $method->name,
        ];

        self::save();
    }

    public static function getShippingMethod()
    {
        self::init();

        if (isset(self::$cart['shipping_method']['id'])) {
            return self::$cart['shipping_method']['id'];
        }

        return false;
    }

    public static function getShippingMethodCost()
    {
        self::init();

        if (isset(self::$cart['shipping_method']['price'])) {
            return self::$cart['shipping_method']['price'];
        }

        return false;
    }
}
