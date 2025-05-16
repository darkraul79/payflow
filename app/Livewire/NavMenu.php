<?php

namespace App\Livewire;

use Datlechin\FilamentMenuBuilder\Models\Menu;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavMenu extends Component
{
    public $menu;
    protected $type;

    protected $view = 'frontend.elements.header.navMenuDesktop';

    public function mount(string $location = 'header', string $type = 'desktop')
    {
        $this->menu = Menu::location($location);
        $this->type = $type;
    }

    public function render(): View
    {

        switch ($this->type) {
            case 'desktop':
                $this->view = 'frontend.elements.header.navMenuDesktop';
                break;
            case 'mobile':
                $this->view = 'frontend.elements.header.navMenuMobile';
                break;
            case 'footer':
                $this->view = 'frontend.elements.footer.navMenuFooter';
                break;
        }

        return view($this->view, [
            'menu' => $this->menu,
        ]);
    }
}
