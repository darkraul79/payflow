<div>
    <div class="flex flex-col items-center justify-center gap-4 xl:flex-row">
        <div
            class="p-.5 flex w-fit flex-row items-center justify-center gap-x-2 rounded-full border border-gray-200"
        >
            <button
                class="bg-azul-gray mx-1 flex h-[34px] w-[34px] cursor-pointer items-center justify-center rounded-full bg-gray-300 text-center text-xl text-black hover:bg-gray-200"
                wire:click="substract"
            >
                -
            </button>
            <input
                id="quantity"
                name="quantity"
                type="number"
                wire:model.live="quantity"
                min="1"
                max="{{ $product->stock }}"
                class="w-16 border-0 p-2 text-center"
                placeholder="1"
            />
            <button
                class="bg-azul-gray mx-1 flex h-[34px] w-[34px] cursor-pointer items-center justify-center rounded-full bg-gray-300 text-center text-xl text-black hover:bg-gray-200"
                wire:click="add"
            >
                +
            </button>
        </div>
        <button
            class="bg-azul-mist font-inter hover:bg-azul-wave group hover:text-azul-mist mx-1 flex w-full cursor-pointer flex-nowrap items-center justify-center gap-3 rounded-full px-6 py-4 text-center text-xl text-[14px] text-white"
            wire:click="addToCarT"
        >
            <x-icon-cart
                class="group-hover:text-azul-mist h-6 w-6 text-white"
            />
            Añadir al carrito
        </button>
    </div>

    @if ($errorMessage)
        <div class="text-error w-full py-1 text-start text-xs">
            {{ $errorMessage }}
        </div>
    @endif
</div>
