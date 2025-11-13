<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductTagsCategories extends Component
{
    public $types = [
        'tags' => 'Tags',
        'categories' => 'Categorías',
    ];

    public $tags;

    public $categories;

    public function __construct(Product $product)
    {
        $this->tags = $product->tags;
        //        $this->categories = $categories;
    }

    public function render(): View
    {
        return view('components.product-tags-categories');
    }
}
