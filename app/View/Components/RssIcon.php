<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RssIcon extends Component
{
  public function __construct(public string $link, public string $title, public string $icon)
  {
  }

  public function render(): View
  {
    return view('components.rss-icon');
  }
}
