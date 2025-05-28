<div
    class="boder-gray-200 -my-12 ms-auto mb-5 flex w-fit min-w-fit flex-col flex-nowrap justify-end rounded-b-xl border border-t-0! text-sm"
>
    <div
        class="flex w-full items-center justify-end gap-x-5 border-b px-6 pt-8 pb-4"
    >
        <span class="w-2/3 text-end text-xs font-semibold text-gray-400">
            Subtotal:
        </span>
        <span class="w-1/3 text-end">
            {{ convertPrice($record->subtotal) }}
        </span>
    </div>
    <div class="flex justify-end gap-x-5 border-b px-6 py-3">
        <span class="w-2/3 text-end text-xs font-semibold text-gray-400">
            Envío:
        </span>
        <span class="w-1/3 text-end">
            {{ convertPrice($record->shipping_cost) }}
        </span>
    </div>
    <div
        class="flex items-center justify-end gap-x-5 border-b bg-gray-50 px-6 py-3 text-base"
    >
        <span
            class="w-2/3 min-w-fit text-end text-xs font-semibold text-gray-400"
        >
            Total:
        </span>
        <span class="w-1/3 min-w-fit text-end font-bold">
            {{ convertPrice($record->total) }}
        </span>
    </div>

    <div class="flex justify-end gap-x-5 px-6 py-3">
        <span class="w-2/3 min-w-fit text-end text-xs text-gray-400">
            Impuestos:
        </span>
        <span class="w-1/3 min-w-fit text-end text-xs text-gray-400">
            {{ convertPrice($record->taxes) }}
        </span>
    </div>
</div>
