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

    public $cart;

    public string $name;

    public $payment_method;

    public $shipping = [
        'name' => '',
        'last_name' => '',
        'address' => '',
        'cp' => '',
        'city' => '',
        'province' => '',
        'email' => '',
        'phone' => '',
    ];

    public $billing = [
        'name' => '',
        'last_name' => '',
        'address' => '',
        'cp' => '',
        'city' => '',
        'province' => '',
        'email' => '',
        'phone' => '',
    ];

    public bool $addSendAddress = false;
    public array $rulesGlobal = [
        'payment_method' => 'required',

    ];
    public array $rulesBilling = [
        'billing.name' => 'required|string|max:255',
        'billing.last_name' => 'required|string|max:255',
        'billing.company' => 'nullable|string|max:255',
        'billing.nif' => 'required|string|max:255',
        'billing.address' => 'required|string|max:255',
        'billing.cp' => 'required|string|max:10',
        'billing.city' => 'required',
        'billing.province' => 'required',
        'billing.email' => 'required|email|max:255',
        'billing.phone' => 'nullable|string|max:20',

    ];
    public array $rulesShipping = [
        'shipping.name' => 'required|string|max:255',
        'shipping.last_name' => 'required|string|max:255',
        'shipping.company' => 'nullable|string|max:255',
        'shipping.nif' => 'required|string|max:255',
        'shipping.address' => 'required|string|max:255',
        'shipping.cp' => 'required|string|max:10',
        'shipping.city' => 'required',
        'shipping.province' => 'required',
        'shipping.email' => 'required|email|max:255',
        'shipping.phone' => 'nullable|string|max:20',
    ];
    protected array $rules = [];


    public function mount()
    {
        if (!Cart::canCheckout()) {
            $this->redirectRoute('cart');
        }

        Cart::resfreshCart();

        $this->cart = session()->get('cart', []);
    }

    public function submit()
    {
        $this->updateRules();
        $this->validate();

        // Procesar los datos del formulario
    }

    public function updateRules(): void
    {
        if ($this->addSendAddress) {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling, $this->rulesShipping);
        } else {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling);

        }
    }

    public function render()
    {
        return view('livewire.order-form-component');
    }

    protected function messages()
    {
        return [
            'required' => 'El campo es obligatorio.',
            'string' => 'El campo debe ser una cadena de texto.',
            'max' => 'El campo no puede tener más de :max caracteres.',
            'email' => 'El campo debe ser una dirección de correo electrónico válida.',
            'integer' => 'El campo debe ser un número entero.',
            'numeric' => 'El campo debe ser un número.',
            'boolean' => 'El campo debe ser verdadero o falso.',
        ];
    }

}
