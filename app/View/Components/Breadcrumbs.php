<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
  public function render(): View
  {
    return view('components.breadcrumbs', [
      'breadcrumbs' => $this->getBreadcrumbs(),
    ]);
  }

  public function getBreadcrumbs(): array
  {
    return ['Home' => route('home'), 'Pagina' => route('pagina')];
  }
}
