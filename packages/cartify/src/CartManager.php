<?php

namespace Darkraul79\Cartify;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartManager
{
    protected string $sessionKey = 'cart.items';

    protected ?string $instanceName = null;

    /**
     * Set cart instance name
     */
    public function instance(?string $name = null): self
    {
        $this->instanceName = $name;

        return $this;
    }

    /**
     * Add item to cart
     */
    public function add(int|string $id, string $name, int $quantity = 1, float $price = 0, array $options = []): void
    {
        $cart = $this->content();

        $item = [
            'id' => $id,
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
            'options' => $options,
        ];

        if ($cart->has($id)) {
            $existingItem = $cart->get($id);
            $item['quantity'] += $existingItem['quantity'];
        }

        $cart->put($id, $item);

        Session::put($this->getSessionKey(), $cart->toArray());
    }

    /**
     * Get cart content
     */
    public function content(): Collection
    {
        return collect(Session::get($this->getSessionKey(), []));
    }

    /**
     * Get specific item from cart
     */
    public function get(int|string $id): ?array
    {
        return $this->content()->get($id);
    }

    /**
     * Get session key for current instance
     */
    protected function getSessionKey(): string
    {
        return $this->instanceName ? "{$this->sessionKey}.{$this->instanceName}" : $this->sessionKey;
    }

    /**
     * Check if item exists in cart
     */
    public function has(int|string $id): bool
    {
        return $this->content()->has($id);
    }

    /**
     * Get cart as array
     */
    public function toArray(): array
    {
        return $this->content()->toArray();
    }

    /**
     * Update item quantity
     */
    public function update(int|string $id, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($id);

            return;
        }

        $cart = $this->content();

        if ($cart->has($id)) {
            $item = $cart->get($id);
            $item['quantity'] = $quantity;
            $cart->put($id, $item);

            Session::put($this->getSessionKey(), $cart->toArray());
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(int|string $id): void
    {
        $cart = $this->content();
        $cart->forget($id);

        Session::put($this->getSessionKey(), $cart->toArray());
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        Session::forget($this->getSessionKey());
    }

    /**
     * Get cart total with tax
     */
    public function total(?float $taxRate = null): float
    {
        $taxRate = $taxRate ?? config('cartify.tax_rate', 0);
        $subtotal = $this->subtotal();

        return $subtotal + ($subtotal * $taxRate);
    }

    /**
     * Get cart subtotal
     */
    public function subtotal(): float
    {
        return $this->itemsOnly()->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
    }

    /**
     * Retorna solo los items válidos (con price y quantity) filtrando metadatos.
     */
    protected function itemsOnly(): Collection
    {
        return $this->content()->filter(function ($item) {
            return is_array($item)
                && array_key_exists('price', $item)
                && array_key_exists('quantity', $item);
        });
    }

    /**
     * Get tax amount
     */
    public function tax(?float $taxRate = null): float
    {
        $taxRate = $taxRate ?? config('cartify.tax_rate', 0);

        return $this->subtotal() * $taxRate;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Get total items count
     */
    public function count(): int
    {
        return $this->itemsOnly()->sum('quantity');
    }

    /**
     * Search cart items
     */
    public function search(callable $callback): Collection
    {
        return $this->itemsOnly()->filter($callback);
    }

    /**
     * Store cart for authenticated user
     */
    public function store(?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        if ($userId) {
            Session::put("{$this->sessionKey}.stored.{$userId}", $this->content()->toArray());
        }
    }

    /**
     * Restore cart for authenticated user
     */
    public function restore(?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        if ($userId && Session::has("{$this->sessionKey}.stored.{$userId}")) {
            Session::put($this->getSessionKey(), Session::get("{$this->sessionKey}.stored.{$userId}"));
            Session::forget("{$this->sessionKey}.stored.{$userId}");
        }
    }

    /**
     * Merge current cart with stored cart
     */
    public function merge(?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        if ($userId && Session::has("{$this->sessionKey}.stored.{$userId}")) {
            $storedCart = collect(Session::get("{$this->sessionKey}.stored.{$userId}"));
            $currentCart = $this->content();

            foreach ($storedCart as $id => $item) {
                if ($currentCart->has($id)) {
                    $existingItem = $currentCart->get($id);
                    $item['quantity'] += $existingItem['quantity'];
                }
                $currentCart->put($id, $item);
            }

            Session::put($this->getSessionKey(), $currentCart->toArray());
            Session::forget("{$this->sessionKey}.stored.{$userId}");
        }
    }
}
