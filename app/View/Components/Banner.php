<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Banner extends Component
{
  public function __construct(
    public string $title,
    public string $description,
    public string $subTitle,
    public string $buttonText,
    public string $buttonLink,
    public string $image,
    public string $align = 'right',
  )
  {
  }

  public function render(): View
  {
    $css = '';
    switch ($this->align) {
      case 'left':
        $css = 'lg:me-auto';
        break;
      case 'center':
        $css = 'lg:mx-auto';
        break;
      case 'right':
        $css = 'lg:ms-auto';
        break;
    }

    return view('components.banner', ['css' => $css]);
  }
}
