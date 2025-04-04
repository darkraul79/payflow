<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HomeNumberItem extends Component
{
  public function __construct(public $number, public $title, public $icon)
  {
  }

  public function render(): View
  {
    return view('components.home-number-item');
  }
}
