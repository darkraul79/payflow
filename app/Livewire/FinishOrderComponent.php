<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Product;
use App\Services\Cart;
use Livewire\Component;

class FinishOrderComponent extends Component
{
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
        'billing.nif' => 'nullable|string|max:255',
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
        'shipping.nif' => 'nullable|string|max:255',
        'shipping.address' => 'required|string|max:255',
        'shipping.cp' => 'required|string|max:10',
        'shipping.city' => 'required',
        'shipping.province' => 'required',
        'shipping.email' => 'required|email|max:255',
        'shipping.phone' => 'nullable|string|max:20',
    ];


    public array $rules = [];

    public function mount()
    {
        if (!Cart::canCheckout()) {
            $this->redirectRoute('cart');
        }

        Cart::resfreshCart();

        $this->cart = session()->get('cart');


    }

    public function submit()
    {
        $this->updateRules();
        $this->validate();

        $this->orderCreate();

        Cart::resetCart();

        $this->redirectRoute('checkout.response');

    }

    public function updateRules(): void
    {
        if ($this->addSendAddress) {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling, $this->rulesShipping);
        } else {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling);

        }
    }

    public function orderCreate(): void
    {
        $order = Order::create([
            'number' => generateOrderNumber(),
            'shipping' => $this->shipping['name'],
            'shipping_cost' => $this->cart['totals']['shipping_cost'],
            'subtotal' => $this->cart['totals']['subtotal'],
            'taxes' => $this->cart['totals']['taxes'],
            'total' => $this->cart['totals']['total'],
            'payment_method' => $this->payment_method,
        ]);

        /* $order; */

        $this->createAddresses($order);
        $this->addItemsToOrder($order);
    }

    public function createAddresses(Order $order): void
    {
        $order->addresses()->create([
            'type' => OrderAddress::BILLING,
            'name' => $this->billing['name'],
            'last_name' => $this->billing['last_name'],
            'company' => $this->billing['company'] ?? null,
            'nif' => $this->billing['nif'] ?? null,
            'address' => $this->billing['address'],
            'province' => $this->billing['province'],
            'city' => $this->billing['city'],
            'cp' => $this->billing['cp'],
            'email' => $this->billing['email'],
            'phone' => $this->billing['phone'] ?? null,
        ]);

        if ($this->addSendAddress) {
            $order->addresses()->create([
                'type' => OrderAddress::SHIPPING,
                'name' => $this->shipping['name'],
                'last_name' => $this->shipping['last_name'],
                'company' => $this->shipping['company'] ?? null,
                'nif' => $this->shipping['nif'] ?? null,
                'address' => $this->shipping['address'],
                'province' => $this->shipping['province'],
                'city' => $this->shipping['city'],
                'cp' => $this->shipping['cp'],
                'email' => $this->shipping['email'],
                'phone' => $this->shipping['phone'] ?? null,
            ]);
        }
    }

    public function addItemsToOrder(Order $order)
    {
        foreach ($this->cart['items'] as $idItem => $item) {
            $order->items()->create([
                'product_id' => $idItem,
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
                'data' => Product::find($idItem)->toArray(),
            ]);

        }
    }

    public function render()
    {
        return view('livewire.finish-order');
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
