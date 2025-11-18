<?php

namespace App\Livewire;

use App\Enums\AddressType;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentProcess;
use App\Support\PaymentMethodRepository;
use Darkraul79\Cartify\Facades\Cart;
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

    public string $payment_method;

    public array $payments_methods = [];

    public string $suffix = '';

    public $isValid = false;

    public $shipping = [
        'name' => '',
        'last_name' => '',
        'last_name2' => '',
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
        'last_name2' => '',
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
        'billing.last_name2' => 'required|string|max:255',
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
        'shipping.last_name2' => 'required|string|max:255',
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
                'last_name' => 'Sebastián',
                'last_name2' => 'Pulido',
                'address' => 'Wistera Street 21',
                'cp' => '28292',
                'city' => 'Madrid',
                'province' => 'Madrid',
                'email' => 'info@raulsebastian.es',
                'phone' => '666666666',
            ];
        }

        // Verificar que el carrito no esté vacío y tenga método de envío
        if (Cart::isEmpty() || ! session('cart_shipping_method_id')) {
            $this->redirectRoute('cart');
        }

        // Construir estructura compatible con código existente
        $rawItems = Cart::content()->toArray();
        $normalizedItems = collect($rawItems)->map(function ($item) {
            if (! isset($item['price_formated'])) {
                $item['price_formated'] = convertPrice($item['price']);
            }
            if (! isset($item['subtotal'])) {
                $item['subtotal'] = $item['price'] * $item['quantity'];
            }
            if (! isset($item['subtotal_formated'])) {
                $item['subtotal_formated'] = convertPrice($item['subtotal']);
            }
            if (! isset($item['image']) && isset($item['options']['image'])) {
                $item['image'] = $item['options']['image'];
            }

            return $item;
        })->toArray();

        $this->cart = [
            'items' => $normalizedItems,
            'totals' => session('cart_totals', [
                'subtotal' => 0,
                'taxes' => 0,
                'total' => 0,
                'shipping_cost' => 0,
            ]),
            'shipping_method' => [
                'id' => session('cart_shipping_method_id'),
                'name' => session('cart_shipping_name', ''),
                'price' => session('cart_shipping_cost', 0),
            ],
        ];

        $this->payments_methods = (new PaymentMethodRepository)->getPaymentsMethods(false)->toArray();
    }

    public function submit(): void
    {
        $this->updateRules();
        $this->validate();

        $order = $this->orderCreate();
        Cart::clear();
        session()->forget(['cart_shipping_method_id', 'cart_shipping_cost', 'cart_shipping_name', 'cart_totals']);

        $order->refresh();

        $paymentProcess = new PaymentProcess(Order::class, [
            'amount' => $order->amount,
            'id' => $order->id,
            'shipping' => $order->shipping,
            'shipping_cost' => $order->shipping_cost,
            'subtotal' => $order->subtotal,
            'taxes' => $order->taxes,
            'payment_method' => $this->payment_method,
        ]);

        $formData = $paymentProcess->getFormRedSysData();

        $this->MerchantParameters = $formData['Ds_MerchantParameters'];
        $this->MerchantSignature = $formData['Ds_Signature'];
        $this->SignatureVersion = $formData['Ds_SignatureVersion'];

        // Disparar evento para enviar el formulario
        $this->isValid = true;
        $this->dispatch('submit-redsys-form');
    }

    public function updateRules(): void
    {
        if ($this->addSendAddress) {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling, $this->rulesShipping);
        } else {
            $this->rules = array_merge($this->rules, $this->rulesGlobal, $this->rulesBilling);
        }
    }

    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    public function orderCreate()
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $order = Order::create([
            'number' => generateOrderNumber(),
            'shipping' => $this->cart['shipping_method']['name'],
            'shipping_cost' => $this->cart['shipping_method']['price'],
            'subtotal' => $this->cart['totals']['subtotal'],
            'amount' => $this->cart['totals']['total'],
            'payment_method' => $this->payment_method,
        ]);

        $this->createAddresses($order);
        $this->addItemsToOrder($order);

        return $order;
    }

    public function createAddresses(Order $order): void
    {
        $order->addresses()->create([
            'type' => AddressType::BILLING->value,
            'name' => $this->billing['name'],
            'last_name' => $this->billing['last_name'],
            'last_name2' => $this->billing['last_name2'],
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
                'type' => AddressType::SHIPPING->value,
                'name' => $this->shipping['name'],
                'last_name' => $this->shipping['last_name'],
                'last_name2' => $this->shipping['last_name2'],
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

    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    public function addItemsToOrder(Order $order): void
    {
        foreach ($this->cart['items'] as $idItem => $item) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
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
        return view('livewire.finish-order', [
            'payment_methods' => $this->payments_methods,
        ]);
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
            'payment_method.required' => 'Debes seleccionar un método de pago.',
        ];

    }
}
