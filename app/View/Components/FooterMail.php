<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FooterMail extends Component
{
    public function __construct(
        public $tags,
    ) {}

    public function render(): View
    {
        return view('components.footer-mail');
    }
}
