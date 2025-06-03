<?php

namespace App\Livewire;

use App\Events\CreateOrderEvent;
use App\Http\Classes\PaymentProcess;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\View\View;
use Livewire\Attributes\Session;
use Livewire\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FinishOrderComponent extends Component
{
    #[Session('cart')]
    public $cart;

    public string $name;

    public $payment_method = 'tarjeta';

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
    public mixed $MerchantParameters;
    public mixed $MerchantSignature;
    public mixed $SignatureVersion;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(): void
    {
        if (app()->isLocal()) {
            $this->billing = [
                'name' => 'Raúl',
                'last_name' => 'Sebasitán',
                'address' => 'Wistera Street 21',
                'cp' => '28292',
                'city' => 'Madrid',
                'province' => 'Madrid',
                'email' => 'info@raulsebastian.es',
                'phone' => '666666666',
            ];
        }

        if (!Cart::canCheckout()) {
            $this->redirectRoute('cart');
        }

        $this->cart = session()->get('cart');

    }

    public function submit(): void
    {
        $this->updateRules();
        $this->validate();

        $order = $this->orderCreate();
        Cart::clearCart();

        $order->refresh();
        $paymentProcess = new PaymentProcess($order);


        $formData = $paymentProcess->getFormRedSysData();

        $this->MerchantParameters = $formData['Ds_MerchantParameters'];
        $this->MerchantSignature = $formData['Ds_Signature'];
        $this->SignatureVersion = $formData['Ds_SignatureVersion'];

    }

    public function updateRules(): void
    {
        if ($this->addSendAddress) {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling, $this->rulesShipping);
        } else {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling);

        }
    }

    public function orderCreate()
    {
        $order = Order::create([
            'number' => generateOrderNumber(),
            'shipping' => $this->shipping['name'],
            'shipping_cost' => $this->cart['totals']['shipping_cost'],
            'subtotal' => $this->cart['totals']['subtotal'],
            'taxes' => $this->cart['totals']['taxes'],
            'amount' => $this->cart['totals']['total'],
            'payment_method' => $this->payment_method,
        ]);

        /* $order; */

        $this->createAddresses($order);
        $this->addItemsToOrder($order);

        CreateOrderEvent::dispatch($order);
        return $order;
    }

    public function createAddresses(Order $order): void
    {
        $order->addresses()->create([
            'type' => Address::BILLING,
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
                'type' => Address::SHIPPING,
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

    public function addItemsToOrder(Order $order): void
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

    public function render(): View
    {
        return view('livewire.finish-order');
    }

    protected function messages(): array
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
