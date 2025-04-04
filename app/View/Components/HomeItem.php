<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HomeItem extends Component
{
  public function __construct(public $title, public $description, public $link, public $linkText, public $icon)
  {
  }

  public function render(): View
  {
    return view('components.home-item');
  }
}
