<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BadgeProductStock extends Component
{
    public string $color;

    public $stock;

    public string $text;

    public function __construct(int $stock)
    {
        $this->stock = $stock;
    }

    public function render(): View
    {
        $this->text = $this->stock > 0 ? 'Disponible' : 'Agotado';
        $this->color = $this->stock > 0 ? 'text-azul-wave bg-azul-sky' : 'text-white bg-azul-gray';

        return view('components.badge-product-stock');
    }
}
