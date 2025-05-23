<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CardProduct extends Component
{

    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function addToCart()
    {
        if ($this->checkStock()) {
            $this->dispatch('showAlert', 'No hay suficiente stock');

        } else {
            Cart::addItem($this->product, 1);
            $this->dispatch('updatedCart');
            $this->dispatch('showAlert', 'Producto agregado al carrito');
        }
    }

    public function checkStock(): bool
    {
        if ((Cart::getQuantityProduct($this->product->id) + 1) <= $this->product->stock) {

            return false;
        }
        return true;
    }

    public function render(): View
    {

        return view('livewire.card-product');
    }
}
