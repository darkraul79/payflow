<?php

namespace App\Livewire;

use App\Models\Product;
use Darkraul79\Cartify\Facades\Cart;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductAddCart extends Component
{
    public Product $product;

    public int $quantity = 1;

    public function mount(Product $product): void {}

    public function render(): View
    {
        return view('livewire.product-add-cart');
    }

    #[On('updateQuantity')]
    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function addToCart(): void
    {
        if ($this->checkStock()) {
            $this->dispatch('showAlert', type: 'warning', title: 'No se puede agregar al carrito',
                message: 'No hay suficiente stock');
        } else {
            Cart::add(
                id: $this->product->id,
                name: $this->product->name,
                quantity: $this->quantity,
                price: $this->product->getPrice(),
                options: [
                    'image' => $this->product->getFirstMediaUrl('product_images', 'thumb'),
                    'price_formated' => $this->product->getFormatedPriceWithDiscount(),
                ]
            );

            $this->dispatch('updatedCart');
            $this->dispatch('showAlert', type: 'success', title: 'Producto agregado',
                message: 'El producto ha sido agregado al carrito.');
        }
    }

    public function checkStock(): bool
    {
        $currentQty = Cart::has($this->product->id) ? Cart::get($this->product->id)['quantity'] : 0;

        if (($currentQty + $this->quantity) <= $this->product->stock) {
            return false;
        }

        return true;
    }

    public function resetCart(): void
    {
        Cart::clear();
    }
}
