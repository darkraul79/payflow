<?php

namespace App\Livewire;

use App\Models\Product;
use Darkraul79\Cartify\Facades\Cart;
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
            Cart::add(
                id: $this->product->id,
                name: $this->product->name,
                quantity: 1,
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

        if (($currentQty + 1) <= $this->product->stock) {
            return false;
        }

        return true;
    }

    public function render(): View
    {

        return view('livewire.card-product');
    }
}
