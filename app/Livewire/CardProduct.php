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
        Cart::addItem($this->product, 1);
        $this->dispatch('updatedCart');
        $this->dispatch('showAlert', 'Producto agregado al carrito');
    }


    public function render(): View
    {

        return view('livewire.card-product');
    }
}
