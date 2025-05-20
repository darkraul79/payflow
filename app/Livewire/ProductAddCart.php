<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Livewire\Component;

class ProductAddCart extends Component
{

    public Product $product;

    public bool|string $errorMessage = '';

    public int $quantity = 1;

    public function mount(Product $product): void
    {
    }

    public function render()
    {
        return view('livewire.product-add-cart');
    }

    public function add(): void
    {
        if ($this->quantity > $this->product->stock) {
            $this->errorMessage = 'No hay suficiente stock';
            return;
        }
        $this->quantity += 1;

        $this->errorMessage = false;
    }

    public function substract(): void
    {
        if ($this->quantity <= 1) {
            $this->errorMessage = 'No puedes agregar menos de 1 producto';
            return;
        }
        $this->quantity -= 1;
        $this->errorMessage = false;
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
