<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PageCartComponent extends Component
{
    public array $items;

    public Collection|Product $itemsProducts;

    public float $subtotal = 0;

    public float $envio = 0;

    public float $total = 0;

    public bool $disabled = true;

    public float $taxes = 0;

    public function mount(): void
    {
        $this->refreshCart();
        $this->envio = convertPriceNumber(setting('store.price_send')) ?? 3.5;
        $this->updateTotals();

    }

    public function refreshCart(): void
    {

        $this->items = Cart::getItems();
        $this->itemsProducts = Product::whereIn('id', array_keys($this->items))->with('media')->get();
        Cart::setTotals(
            subtotal: $this->subtotal,
            taxes: $this->taxes,
            total: $this->total,
            shipping_cost: $this->envio
        );
    }

    public function updateTotals(): void
    {
        $this->subtotal = Cart::getTotalPrice();
        $this->total = $this->subtotal + $this->envio;
        $this->taxes = calculoImpuestos($this->subtotal);
        Cart::setTotals(
            subtotal: $this->subtotal,
            taxes: $this->taxes,
            total: $this->total,
            shipping_cost: $this->envio
        );
        $this->dispatch('updatedCart');
        $this->isValid();

    }

    public function isValid(): void
    {
        if ($this->subtotal > 0) {
            $this->disabled = false;
        } else {
            $this->disabled = true;
        }
    }

    public function render(): View
    {
        return view('livewire.page-cart-component');
    }

    public function removeItem($id): void
    {
        Cart::removeItem($id);
        $this->refreshCart();
        $this->dispatch('showAlert', type: 'success', title: 'Producto eliminado', message: 'Se ha eliminado el producto del carrito.');

        $this->updateTotals();

    }

    #[On('updateQuantity')]
    public function updateQuantity(int $quantity, Product $product): void
    {
        Cart::updateItemQuantity($product, $quantity);
        $this->refreshCart();
        $this->updateTotals();

    }

    public function submit(): void
    {
        if ($this->disabled) {
            $this->dispatch('showAlert', type: 'error', title: 'Carrito vacío', message: 'No hay productos en el carrito');

            return;
        }
        $this->updateCart();
        $this->redirectRoute('checkout');
    }

    private function updateCart(): void
    {
        Cart::setTotals(
            subtotal: $this->subtotal,
            taxes: $this->taxes,
            total: $this->total,
            shipping_cost: $this->envio
        );

    }

    public function clearCart(): void
    {
        $this->dispatch('showAlert', type: 'info', title: 'Carrito vacío', message: 'Has eliminado todos los productos del carrito.');

        $this->items = [];
        $this->taxes = 0;
        $this->subtotal = 0;
        $this->total = 0;
        $this->envio = 0;
        Cart::resetCart();

        $this->dispatch('updatedCart');

    }
}
