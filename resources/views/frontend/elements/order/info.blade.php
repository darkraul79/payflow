<div class="cartTable px-6 py-6">
    <h2 class="text-azul-sea">Tu pedido</h2>

    <ul class="items">
        @foreach ($cart['items'] as $item)
            <li>
                <div class="inline-flex items-center gap-2">
                    @if (isset($item['image']))
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['name'] }}"
                            class="w-full max-w-[60px] rounded-md object-cover"
                        />
                    @else
                        <div class="no-img">
                            <x-heroicon-s-photo />
                        </div>
                    @endif
                    <div class="text-azul-sea inline-flex items-center gap-4">
                        {{ $item['name'] }}
                    </div>
                </div>
                <div
                    class="flex h-full flex-row-reverse flex-wrap items-baseline text-end"
                >
                    <strong>{!! $item['price_formated'] !!}</strong>
                </div>
            </li>
        @endforeach
    </ul>

    <ul>
        <li>
            <span>Subtotal</span>
            <strong>{{ convertPrice($cart['totals']['subtotal']) }}</strong>
        </li>
        <li>
            <span>Envío</span>
            <strong>
                {{ convertPrice($cart['totals']['shipping_cost']) }}
            </strong>
        </li>
        <li>
            <span class="total">Total</span>
            <strong class="total">
                {{ convertPrice($cart['totals']['total']) }}
                @if ($cart['totals']['taxes'])
                    <span>
                        incluye
                        {{ convertPrice(calculoImpuestos($cart['totals']['taxes'])) }}
                        de impuestos
                    </span>
                @endif
            </strong>
        </li>
    </ul>

    <div class="my-6">
        <h2 class="text-azul-sea text-sm">Método de pago</h2>
        <x-input
            type="radio"
            name="payment_method"
            id="payment_method_tarjeta"
            checked
            wire:model="payment_method"
            value="tarjeta"
        ></x-input>

        <label for="payment_method_tarjeta" class="text-[12px]">
            Tarjeta bancaria
        </label>
        <x-error
            class="text-error/80 w-full text-[11px]"
            field="payment_method"
        />
    </div>

    <div class="px-4">
        <button
            class="btn btn-primary mt-4 w-full cursor-pointer rounded-full"
            wire:click="submit"
        >
            Realizar pedido
        </button>
    </div>
</div>
