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
                        @if ($item['quantity'] > 1)
                            <small>x{{ $item['quantity'] }}</small>
                        @endif
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
            <div>
                Envío
                <span class="text-azul-gray text-xs">
                    {{ $cart['shipping_method']['name'] }}
                </span>
            </div>
            <strong>
                {{ $cart['shipping_method']['price'] > 0 ? convertPrice($cart['shipping_method']['price']): 'Gratis' }}
            </strong>
        </li>
        <li>
            <span class="total">Total</span>
            <strong class="total">
                {{ convertPrice($cart['totals']['total']) }}
                @if ($cart['totals']['taxes'])
                    <span>
                        incluye
                        {{ convertPrice($cart['totals']['taxes']) }}
                        de impuestos
                    </span>
                @endif
            </strong>
        </li>
    </ul>

    <div class="my-6 border-y border-gray-200 p-4 bg-azul-gray/10">
        <h2 class="text-azul-sea text-mb-6 text-[16px] ">Método de pago</h2>
        @if($payments_methods && count($payments_methods) > 0)
            <div class="text-sm">
                <x-payments-methods :payment_methods="$payments_methods" />
            </div>
        @endif
    </div>

    <div class="px-4">
        <button
            class="btn btn-primary mt-4 w-full cursor-pointer rounded-full"
            wire:loading.attr="disabled"
            wire:click="submit"
        >
            <span wire:loading.class="inline-block" class="hidden">
                Enviando...
            </span>
            <span wire:loading.class="hidden">Realizar pedido</span>
        </button>
    </div>
</div>
