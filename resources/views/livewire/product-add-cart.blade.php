<div>
    <div class="flex flex-col items-start justify-center gap-4 xl:flex-row">
        @if ($product->stock > 0)
            <livewire:quantity-buttons
                :product="$product"
                wire:key="product-quantity-{{ $product->id }}"
            />
            <button
                class="bg-azul-mist font-inter hover:bg-azul-wave group hover:text-azul-mist mx-1 flex w-full cursor-pointer flex-nowrap items-center justify-center gap-3 rounded-full px-6 py-4 text-center text-xl text-[14px] text-white"
                wire:click="addToCart"
            >
                <x-icon-cart
                    class="group-hover:text-azul-mist h-6 w-6 text-white"
                />
                Añadir al carrito
            </button>
        @else
            <button
                class="bg-azul-gray font-inter hover:bg-azul-wave group hover:text-azul-mist mx-1 flex w-full cursor-pointer flex-nowrap items-center justify-center gap-3 rounded-full px-6 py-4 text-center text-xl text-[14px] text-white"
            >
                <x-icon-cart
                    class="group-hover:text-azul-mist h-6 w-6 text-white"
                />
                Agotado
            </button>
        @endif
    </div>
</div>
