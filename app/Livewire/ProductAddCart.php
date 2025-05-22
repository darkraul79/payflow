<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductAddCart extends Component
{
    public Product $product;


    public int $quantity = 1;

    public function mount(Product $product): void
    {
    }

    public function render()
    {
        return view('livewire.product-add-cart');
    }

    #[On('updateQuantity')]
    public function updateQuantity(int $quantity, Product $product): void
    {
        $this->quantity = $quantity;

    }


    public function addToCart(Product $product): void
    {
        Cart::addItem($product, $this->quantity);

        $this->dispatch('updatedCart');
        $this->dispatch('showAlert', 'Producto agregado al carrito');

    }

    public function resetCart()
    {
        Cart::clearCart();
    }
}
