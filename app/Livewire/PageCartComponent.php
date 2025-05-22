<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class PageCartComponent extends Component
{
    public array $items;

    public Collection|Product $itemsProducts;

    public float $subtotal = 0;

    public float $envio;

    public float $total = 0;

    public bool $disabled = true;

    public float $taxes = 0;

    public function mount()
    {
        $this->refreshCart();
        $this->envio = convertPriceNumber(setting('store.price_send')) ?? 3.5;
        $this->updateTotals();

    }

    public function refreshCart(): void
    {
        $this->items = Cart::getItems();
        $this->itemsProducts = Product::whereIn('id', array_keys($this->items))->with('media')->get();
    }

    public function updateTotals()
    {
        $this->subtotal = Cart::getTotalPrice();
        $this->total = $this->subtotal + $this->envio;
        $this->taxes = $this->total;
        $this->dispatch('updatedCart');
        $this->isValid();

    }

    public function isValid()
    {
        if ($this->subtotal > 0) {
            $this->disabled = false;
        } else {
            $this->disabled = true;
        }
    }

    public function render()
    {
        return view('livewire.page-cart-component');
    }

    public function removeItem($id)
    {
        Cart::removeItem($id);
        $this->refreshCart();
        $this->dispatch('showAlert', 'Producto eliminado del carrito');
        $this->updateTotals();

    }

    #[On('updateQuantity')]
    public function updateQuantity(int $quantity, Product $product): void
    {
        Cart::updateItemQuantity($product, $quantity);
        $this->refreshCart();
        $this->updateTotals();

    }

    public function submit()
    {
        if ($this->disabled) {
            $this->dispatch('showAlert', 'No hay productos en el carrito');

            return;
        }
        $this->updateCart();
        $this->redirectRoute('checkout');
    }

    private function updateCart()
    {
        Cart::setTotals(
            subtotal: $this->subtotal,
            taxes: $this->taxes,
            total: $this->total,
            shipping_cost: $this->envio
        );

    }
}
