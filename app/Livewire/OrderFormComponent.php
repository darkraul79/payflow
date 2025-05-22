<?php

namespace App\Livewire;

use App\Services\Cart;
use Livewire\Component;

class OrderFormComponent extends Component
{
    public float $total = 0;

    public float $subtotal = 0;

    public float $envio = 0;

    public float $taxes = 0;

    public array $shipping = [
        'name' => '',
        'last_name' => '',
        'address' => '',
        'cp' => '',
        'city' => '',
        'province' => '',
        'email' => '',
        'phone' => '',
    ];

    public array $billing = [
        'name' => '',
        'last_name' => '',
        'address' => '',
        'cp' => '',
        'city' => '',
        'province' => '',
    ];

    public bool $addSendAddress = false;

    protected $rules = [
        'shipping.name' => 'required|string|max:255',
        'shipping.last_name' => 'required|string|max:255',
        'shipping.address' => 'required|string|max:255',
        'shipping.cp' => 'required|integer|max:5',
        'shipping.city' => 'required|string|max:255',
        'shipping.province' => 'required|string|max:255',
        'shipping.email' => 'required|email|max:255',
        'shipping.phone' => 'nullable|string|max:20',
    ];

    public function updatedAddSendAddress($value)
    {
        if ($value) {
            $this->rules = array_merge($this->rules, [
                'billing.name' => 'required|string|max:255',
                'billing.last_name' => 'required|string|max:255',
                'billing.address' => 'required|string|max:255',
                'billing.cp' => 'required|string|max:10',
                'billing.city' => 'required|string|max:255',
                'billing.province' => 'required|string|max:255',
            ]);
        } else {
            $this->rules = array_filter($this->rules, function ($key) {
                return !str_starts_with($key, 'billing.');
            }, ARRAY_FILTER_USE_KEY);
        }
    }

    public function mount()
    {
        if (!Cart::canCheckout()) {
            return redirect()->route('cart');
        }

    }

    public function submit()
    {
        $this->validate();

        // Procesar los datos del formulario
    }

    public function render()
    {
        return view('livewire.order-form-component');
    }
}
