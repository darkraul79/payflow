<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ToastComponent extends Component
{
    public string $message;

    public string $title = 'Titular';

    public bool $show = false;

    public bool $animate = false;

    public string $type = 'success';

    public string $boxClasses = ''; // info, success, warning, error

    public string $color;

    public function mount($type = 'error', $message = 'Mensaje', $title = 'Titular'): void
    {
        $this->type = $type;
        $this->message = $message;
        $this->color = $this->getColor();
        $this->title = $title;
        $this->boxClasses = $this->getBlockClasses();
    }

    public function getColor(): string
    {
        return match ($this->type) {
            'success' => 'border-success ring-success/50',
            'warning' => 'border-amarillo ring-amarillo/30',
            'error' => 'border-error ring-error/50',
            default => 'border-azul-sea ring-azul-sea/50',
        };
    }

    public function getBlockClasses(): string
    {
        return match ($this->type) {
            'success' => 'border-success bg-success-50',
            'warning' => 'border-amarillo bg-amarillo-50',
            'error' => 'border-error bg-error-50',
            default => 'border-azul-sky bg-sky-50',
        };
    }

    #[On('showAlert')]
    public function show($type = 'error', $message = 'Mensaje', $title = 'Titular'): void
    {
        $this->type = $type;
        $this->boxClasses = $this->getBlockClasses();
        $this->message = $message;
        $this->title = $title;
        $this->color = $this->getColor();
        $this->show = true;
        $this->animate = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->animate = false;
    }

    public function render(): View
    {
        return view('livewire.toast-component');
    }
}
