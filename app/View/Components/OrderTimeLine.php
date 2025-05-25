<?php

namespace App\View\Components;

use App\Models\Order;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderTimeLine extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Order $pedido)
    {


    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-time-line');
    }
}
