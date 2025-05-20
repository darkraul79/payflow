<article
    class="border-azul-cobalt flex flex-col justify-between rounded-lg border bg-white px-3 pt-3 pb-1 shadow-lg transition-all duration-300 ease-out hover:scale-102"
>
    <a href="{{ $product->getLink() }}" title="{{ $product->title }}">
        {{
            $product
                ->getFirstMedia('product_images')
                ?->img()
                ->conversion('thumb')
                ->attributes([
                    'alt' => $product->name,
                    'class' => 'rounded-md object-cover w-full',
                ])
        }}
    </a>
    <div class="relative flex flex-row items-center justify-between">
        <div class="p-4">
            {{ $product->name }}
            <br />
            <span class="text-sm font-semibold">
                {!! $product->getFormatedPriceWithDiscount() !!}
            </span>
        </div>

        <button
            class="bg-azul-mist hover:bg-azul-wave hover:text-azul-mist group absolute right-0 ms-auto inline-flex w-10 cursor-pointer overflow-hidden rounded-full p-3 -indent-px transition-all duration-300 ease-in-out hover:me-0 hover:w-auto hover:shadow"
            wire:click="addToCart({{ $product }},1)"
        >
            <span class="hidden px-2 text-xs text-white group-hover:block">
                Añadir al carro
            </span>
            <x-icon-cart class="h-4 w-4 text-white" />
        </button>
    </div>
</article>
