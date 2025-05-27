<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Component;

class QuantityButtons extends Component
{
    public int $quantity;

    public Product $product;

    public bool|string $errorMessage = '';

    public bool $size;

    public function mount(Product $product, int $quantity = 1, string $size = 'normal'): void
    {
        $this->quantity = $quantity;
        $this->product = $product;

        $this->size = ($size == 'normal');

    }

    public function render(): View
    {
        return view('livewire.quantity-buttons');
    }

    public function add(): void
    {
        $this->updateQuantity($this->quantity + 1);
    }

    public function substract(): void
    {
        $this->updateQuantity($this->quantity - 1);
    }

    public function update(): void
    {
        $this->updateQuantity($this->quantity);
    }

    private function updateQuantity(int $newQuantity): void
    {
        if ($newQuantity > $this->product->stock) {
            $this->errorMessage = 'No hay suficiente stock ('.$this->product->stock.' max)';
            $this->quantity = $this->product->stock;
        } elseif ($newQuantity < 1) {
            $this->errorMessage = 'No puedes agregar menos de 1 producto';
            $this->quantity = 1;
        } else {
            $this->errorMessage = false;
            $this->quantity = $newQuantity;
        }

        $this->updateEvent();
    }

    public function updateEvent(): void
    {
        $this->dispatch('updateQuantity', $this->quantity, $this->product);
    }
}
