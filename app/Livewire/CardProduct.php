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

    public function addToCart(): void
    {
        if ($this->checkStock()) {
            $this->dispatch('showAlert', type: 'error', title: 'No se puede agregar el producto',
                message: 'No hay suficiente stock');

        } else {
            Cart::addItem($this->product);
            $this->dispatch('updatedCart');
            $this->dispatch('showAlert', type: 'success', title: 'Producto agregado',
                message: 'El producto ha sido agregado al carrito.');
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
