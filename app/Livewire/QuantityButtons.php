<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class QuantityButtons extends Component
{
    public int $quantity;

    public Product $product;


    public bool|string $errorMessage = '';
    public bool $size;

    public function mount(Product $product, int $quantity = 1, string $size = "normal"): void
    {
        $this->quantity = $quantity;
        $this->product = $product;

        $this->size = ($size == "normal");

    }

    public function render()
    {
        return view('livewire.quantity-buttons');
    }

    public function add()
    {
        $this->quantity += 1;
        if ($this->quantity > $this->product->stock) {
            $this->errorMessage = 'No hay suficiente stock (' . $this->product->stock . ' max)';
            $this->quantity = $this->product->stock;

            return;
        }

        $this->errorMessage = false;

        $this->updateEvent();
    }

    public function updateEvent(): void
    {

        $this->dispatch('updateQuantity', $this->quantity, $this->product);

    }

    public function update()
    {
        if ($this->quantity > $this->product->stock) {
            $this->errorMessage = 'No hay suficiente stock (' . $this->product->stock . ' max)';
            $this->quantity = $this->product->stock;

            return;
        }
        if ($this->quantity < 1) {
            $this->errorMessage = 'No puedes agregar menos de 1 producto';
            $this->quantity = 1;

            return;
        }

        $this->errorMessage = false;

        $this->updateEvent();
    }

    public function substract()
    {
        $this->quantity -= 1;
        if ($this->quantity < 1) {
            $this->errorMessage = 'No puedes agregar menos de 1 producto';
            $this->quantity = 1;

            return;
        }
        $this->errorMessage = false;

        $this->updateEvent();
    }
}
