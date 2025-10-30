<div class="cartTable px-6 py-6">
    <h2>Total del carrito</h2>
    <ul>
        <li>
            <span>Subtotal</span>
            <strong>{{ convertPrice($subtotal) }}</strong>
        </li>
        <li class="flex flex-col items-start gap-2">
            <div class="flex w-full items-center justify-between">
                <span>Envío</span>
                <div class="text-xs text-red-500">
                    @error('shipping_method')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <x-radiobutton-shipping-method
                name="shipping_method"
                wire:model.live="shippingMethod"
                :default="$shipping_method"
                :options="$shipping_methods"
            />
        </li>
        <li>
            <span class="total">Total</span>
            <strong class="total">
                {{ convertPrice($total) }}
                @if ($taxes)
                    <span>
                        incluye {{ convertPrice(calculoImpuestos($subtotal)) }}
                        de impuestos
                    </span>
                @endif
            </strong>
        </li>
    </ul>
    <div class="px-4">
        <button
            class="btn btn-primary mt-4 w-full cursor-pointer rounded-full"
            wire:click="submit"
            {{ $disabled ? 'disabled="disabled"' : '' }}
        >
            Finalizar compra
        </button>
    </div>
</div>
