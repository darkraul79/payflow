<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ToastComponent extends Component
{
    public string $message;
    public bool $show = false;
    public bool $animate = false;


    public function mount()
    {
        $this->message = "Mensaje";
    }

    #[On('showAlert')]
    public function show($message = "Mensaje")
    {
        $this->message = $message;
        $this->show = true;
        $this->animate = true;
    }

    public function close()
    {
        $this->show = false;
        $this->animate = false;
    }


    public function render()
    {
        return view('livewire.toast-component');
    }
}
