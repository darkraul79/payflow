<div class="cartTable px-6 py-6">
    <h2>Total del carrito</h2>
    <ul>
        <li>
            <span>Subtotal</span>
            <strong>{{ convertPrice($subtotal) }}</strong>
        </li>
        <li>
            <span>Envío</span>
            <strong>{{ convertPrice($envio) }}</strong>
        </li>
        <li>
            <span class="total">Total</span>
            <strong class="total">
                {{ convertPrice($total) }}
                @if ($taxes)
                    <span>
                        incluye {{ convertPrice(calculoImpuestos($taxes)) }} de
                        impuestos
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
