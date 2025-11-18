<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CartNormalizer;
use App\Services\ShippingSession;
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
        $this->items = CartNormalizer::items();
        $itemIds = array_keys($this->items);
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $this->itemsProducts = Product::whereIn('id', $itemIds)->with('media')->get();
        $this->shipping_method = ShippingSession::id();
        $this->envio = ShippingSession::cost();
        $this->updateTotals();
    }

    public function updateTotals(): void
    {
        $totals = CartNormalizer::totals();
        $this->subtotal = $totals['subtotal'];
        $this->envio = $totals['shipping_cost'];
        $this->total = $totals['total'];
        $this->taxes = $totals['taxes'];

        // Usar dot notation para no sobrescribir cart.items
        session([
            'cart.shipping_method.id' => ShippingSession::id(),
            'cart.shipping_method.price' => ShippingSession::cost(),
            'cart.total_shipping' => ShippingSession::cost(),
            'cart.totals.subtotal' => $this->subtotal,
            'cart.totals.taxes' => $this->taxes,
            'cart.totals.total' => $this->total,
            'cart.totals.shipping_cost' => $this->envio,
        ]);

        session([
            'cart_totals.subtotal' => $this->subtotal,
            'cart_totals.taxes' => $this->taxes,
            'cart_totals.total' => $this->total,
            'cart_totals.shipping_cost' => $this->envio,
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
            'cart_totals.subtotal' => $this->subtotal,
            'cart_totals.taxes' => $this->taxes,
            'cart_totals.total' => $this->total,
            'cart_totals.shipping_cost' => $this->envio,
        ]);
    }

    public function updatedShippingMethod($value): void
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $metodo = ShippingMethod::find($value);
        $this->envio = $metodo ? $metodo->price : 0;
        $this->shipping_method = $value;

        if ($metodo) {
            ShippingSession::set($metodo);
        } else {
            ShippingSession::clear();
        }

        $this->updateTotals();
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
        ShippingSession::clear();
        session()->forget([
            'cart.shipping_method',
            'cart.total_shipping',
            'cart.totals',
            'cart_totals',
        ]);

        $this->dispatch('updatedCart');
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
