<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public function __construct(
        public $page,
        public $type
    ) {}

    public function render(): View
    {
        return view('components.breadcrumbs', [
            'breadcrumbs' => $this->page->getBreadcrumbs(),
        ]);
    }
}
