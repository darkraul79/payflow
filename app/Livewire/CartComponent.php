<?php

namespace App\Livewire;

use App\Services\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartComponent extends Component
{

    public $quantity = 0;

    public function mount()
    {
        $this->updateQuantity();
    }

    public function updateQuantity()
    {
        $this->quantity = Cart::getTotalQuantity();
    }

    public function render()
    {
        return view('livewire.cart-component');
    }

    #[On('updatedCart')]
    public function updatedCart()
    {
        $this->updateQuantity();
    }

    public function resetCart()
    {
        Cart::clearCart();
        $this->updateQuantity();
        $this->dispatch('showAlert', 'Carrito vaciado');

    }

}
