<?php

namespace App\Livewire;

use App\Services\Cart;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CartButtonComponent extends Component
{
    public int $quantity = 0;

    public function mount(): void
    {
        $this->updateQuantity();
    }

    public function updateQuantity(): void
    {
        $this->quantity = Cart::getTotalQuantity();
    }

    public function render(): View
    {
        return view('livewire.cart-component');
    }

    #[On('updatedCart')]
    public function updatedCart(): void
    {
        $this->updateQuantity();
    }

    public function resetCart(): void
    {
        $this->dispatch('showAlert', type: 'info', title: 'Carrito vacío',
            message: 'Has eliminado todos los productos del carrito.');
        Cart::clearCart();
        $this->updateQuantity();

    }
}
