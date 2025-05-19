<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardProduct extends Component
{

    public function __construct(
        public Product $product,
    )
    {
    }


    public function render(): View
    {

        return view('components.card-product');
    }
}
