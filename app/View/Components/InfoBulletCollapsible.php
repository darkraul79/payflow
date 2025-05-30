<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InfoBulletCollapsible extends Component
{
    public function __construct(
        public $info,
    )
    {
    }

    public function render(): View
    {
        return view('components.info-bullet-collapsible');
    }
}
