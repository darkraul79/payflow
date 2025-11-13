<?php

namespace app\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModalDonation extends Component
{
    public function render(): View
    {
        return view('components.modal-donation');
    }
}
