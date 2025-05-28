<div>
    <div
        class="p-.5 mx-.5 flex w-full flex-row items-center justify-between rounded-full border border-gray-200"
    >
        <button
            class="{{ $size ? 'h-[34px] w-[34px] text-xl' : 'h-[21px] w-[21px]' }} bg-azul-gray mx-1 flex cursor-pointer items-center justify-center rounded-full bg-gray-300 text-center text-black hover:bg-gray-200"
            wire:click="substract"
        >
            -
        </button>
        <input
            id="quantity"
            name="quantity"
            type="number"
            wire:change="update"
            wire:model.live="quantity"
            min="1"
            max="{{ $product->stock }}"
            class="{{ $size ? 'py-2' : 'py-1 text-xs' }} item-number w-auto border-0 px-0 text-center"
            placeholder="1"
        />
        <button
            class="{{ $size ? 'h-[34px] w-[34px] text-xl' : 'h-[21px] w-[21px]' }} bg-azul-gray mx-1 flex cursor-pointer items-center justify-center rounded-full bg-gray-300 text-center text-black hover:bg-gray-200"
            wire:click="add"
        >
            +
        </button>
    </div>
    @if ($errorMessage)
        <div class="text-error/70 w-full py-1 text-center text-[11px]">
            {{ $errorMessage }}
        </div>
    @endif
</div>
