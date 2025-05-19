<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BadgeProductOffer extends Component
{


    public $visible;

    public function __construct(int|null $visible = 0)
    {
        $this->visible = $visible > 0 ? true : false;
    }

    public function render(): View
    {


        return view('components.badge-product-offer');
    }
}
