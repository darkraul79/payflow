<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TitleSection extends Component
{

    public $title;

    public function __construct($model)

    {
        switch (getTypeContent($model)) {
            case 'Product':
                $this->title = $model->getParents()->first()->title;
                break;

            case 'Page':
                if ($model->isHomePage) {
                    break;
                } else {
                    $this->title = $model->title;
                }
                break;
            case 'News':
            case 'Proyect':
            case 'Activity':
                $this->title = false;
                break;
            default:
                $this->title = $model->title;
        }
    }

    public function render(): View
    {
        return view('components.title-section');
    }
}
