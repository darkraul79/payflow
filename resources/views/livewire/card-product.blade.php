<article
    class="border-azul-cobalt flex flex-col justify-between rounded-lg border bg-white px-3 pt-3 pb-1 shadow-lg transition-all duration-300 ease-out hover:scale-102"
>
    <a
        href="{{ $product->getLink() }}"
        class="relative"
        title="{{ $product->title }}"
        wire:ignore
    >
        {{
            $product
                ->getFirstMedia('product_images')
                ?->img()
                ->conversion('thumb')
                ->attributes([
                    'alt' => $product->name,
                    'class' => 'rounded-md object-cover w-full' . ($product->stock < 1 ? ' opacity-50 ' : ' '),
                ])
        }}
        <div class="absolute top-0 left-0 flex flex-col gap-1">
            @if ($product->offer_price)
                <x-badge-product-offer
                    class="bg-error! w-fit rounded-sm text-white!"
                    text="Oferta {{ $product->discount_porcentaje() }} %"
                    :visible="$product->offer_price"
                />
            @endif

            @if ($product->stock < 1)
                <x-badge-product-stock
                    class="w-fit rounded-sm"
                    stock="{{$product->stock}}"
                />
            @endif
        </div>
    </a>
    <div class="relative flex flex-row items-center justify-between">
        <div class="p-4">
            {{ $product->name }}
            <br />
            <span class="text-sm font-semibold">
                {!! $product->getFormatedPriceWithDiscount() !!}
            </span>
        </div>

        @if ($product->stock > 0)
            <button
                class="bg-azul-mist hover:bg-azul-wave hover:text-azul-mist group absolute right-0 ms-auto inline-flex w-10 cursor-pointer overflow-hidden rounded-full p-3 -indent-px transition-all duration-300 ease-in-out hover:me-0 hover:w-auto hover:shadow"
                wire:click="addToCart({{ $product }})"
            >
                <span class="hidden px-2 text-xs text-white group-hover:block">
                    Añadir al carro
                </span>
                <x-icon-cart class="h-4 w-4 text-white" />
            </button>
        @else
            <button
                class="bg-azul-gray hover:bg-azul-wave hover:text-azul-mist group absolute right-0 ms-auto inline-flex w-10 cursor-pointer overflow-hidden rounded-full p-3 -indent-px text-white transition-all duration-300 ease-in-out hover:me-0 hover:w-auto hover:shadow"
            >
                <span class="hidden px-2 text-xs text-white group-hover:block">
                    Agotado
                </span>

                <x-icon-cart class="h-4 w-4 text-white" />
            </button>
        @endif
    </div>
</article>
