<?php

namespace Darkraul79\Cartify\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelCommerce\CartManager instance(?string $name = null)
 * @method static void add(int|string $id, string $name, int $quantity = 1, float $price = 0, array $options = [])
 * @method static void remove(int|string $id)
 * @method static void update(int|string $id, int $quantity)
 * @method static Collection content()
 * @method static array|null get(int|string $id)
 * @method static bool has(int|string $id)
 * @method static void clear()
 * @method static int count()
 * @method static float subtotal()
 * @method static float total(?float $taxRate = null)
 * @method static float tax(?float $taxRate = null)
 * @method static bool isEmpty()
 * @method static Collection search(callable $callback)
 * @method static array toArray()
 * @method static void store(?int $userId = null)
 * @method static void restore(?int $userId = null)
 * @method static void merge(?int $userId = null)
 *
 * @see \LaravelCommerce\CartManager
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
