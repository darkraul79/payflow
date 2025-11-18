<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ShippingMethod;
use Darkraul79\Cartify\Facades\Cart;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PageCartComponent extends Component
{
    public array $items;

    public Collection|Product $itemsProducts;

    public float $subtotal = 0;

    public float $envio = 0;

    public float $total = 0;

    public bool $disabled = true;

    public float $taxes = 0;

    public Collection $shipping_methods;

    public $shipping_method = null;

    public function mount(): void
    {
        $this->refreshCart();
        $this->updateTotals();

        $this->shipping_methods = ShippingMethod::forAmount($this->subtotal)->get();
    }

    public function refreshCart(): void
    {
        $rawItems = Cart::content()->toArray();
        // Transformar para asegurar compatibilidad de claves legacy
        $this->items = collect($rawItems)->map(function ($item) {
            $subtotal = $item['price'] * $item['quantity'];
            $item['subtotal'] = $subtotal;
            $item['subtotal_formated'] = convertPrice($subtotal);
            // Asegurar price_formated si no existe en options
            if (! isset($item['price_formated'])) {
                $item['price_formated'] = convertPrice($item['price']);
            }
            // Mover image desde options si aplica
            if (! isset($item['image']) && isset($item['options']['image'])) {
                $item['image'] = $item['options']['image'];
            }

            return $item;
        })->toArray();
        $itemIds = array_keys($this->items);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $this->itemsProducts = Product::whereIn('id', $itemIds)->with('media')->get();

        // Recuperar shipping method de session
        $this->shipping_method = session('cart_shipping_method_id');
        $this->envio = session('cart_shipping_cost', 0);

        $this->updateTotals();
    }

    public function updateTotals(): void
    {
        // Usar subtotal real sin impuestos del paquete
        $this->subtotal = Cart::subtotal();
        $this->total = $this->subtotal + $this->envio;
        $this->taxes = calculoImpuestos($this->total);
        session([
            'cart_totals' => [
                'subtotal' => $this->subtotal,
                'taxes' => $this->taxes,
                'total' => $this->total,
                'shipping_cost' => $this->envio,
            ],
        ]);
        $this->dispatch('updatedCart');
        $this->isValid();
    }

    public function isValid(): void
    {
        if ($this->subtotal > 0 && $this->shipping_method) {
            $this->disabled = false;
        } else {
            $this->disabled = true;
        }
    }

    public function render(): View
    {
        return view('livewire.page-cart-component');
    }

    public function removeItem($id): void
    {
        Cart::remove($id);
        $this->refreshCart();
        $this->dispatch('showAlert', type: 'success', title: 'Producto eliminado',
            message: 'Se ha eliminado el producto del carrito.');

        $this->updateTotals();
    }

    #[On('updateQuantity')]
    public function updateQuantity(int $quantity, Product $product): void
    {
        Cart::update($product->id, $quantity);
        $this->refreshCart();
        $this->updateTotals();
    }

    public function submit(): void
    {
        $this->validate();
        // Validar inline para evitar dependencia de estado previo
        if ($this->subtotal <= 0 || ! $this->shipping_method) {
            $this->dispatch('showAlert', type: 'error', title: 'Carrito vacío',
                message: 'No hay productos en el carrito');

            return;
        }
        $this->updateCart();
        $this->redirectRoute('checkout');
    }

    private function updateCart(): void
    {
        session([
            'cart_totals' => [
                'subtotal' => $this->subtotal,
                'taxes' => $this->taxes,
                'total' => $this->total,
                'shipping_cost' => $this->envio,
            ],
        ]);
    }

    public function clearCart(): void
    {
        $this->dispatch('showAlert', type: 'info', title: 'Carrito vacío',
            message: 'Has eliminado todos los productos del carrito.');

        $this->items = [];
        $this->taxes = 0;
        $this->subtotal = 0;
        $this->total = 0;
        $this->envio = 0;

        Cart::clear();
        session()->forget(['cart_shipping_method_id', 'cart_shipping_cost', 'cart_totals']);

        $this->dispatch('updatedCart');
    }

    public function updatedShippingMethod($value): void
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $metodo = ShippingMethod::find($value);
        $this->envio = $metodo ? $metodo->price : 0;
        $this->shipping_method = $value;

        // Guardar método de envío en session
        session([
            'cart_shipping_method_id' => $metodo->id,
            'cart_shipping_cost' => $metodo->price,
            'cart_shipping_name' => $metodo->name,
        ]);

        $this->updateTotals();
    }

    protected function rules(): array
    {
        return [
            'shipping_method' => 'required|exists:shipping_methods,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'shipping_method.required' => 'Debes seleccionar un método de envío.',
            'shipping_method.exists' => 'Debes seleccionar un método de envío correcto.',
        ];
    }
}
