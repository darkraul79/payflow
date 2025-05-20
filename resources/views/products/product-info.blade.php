<div class="flex flex-row flex-wrap gap-2">
    <h1 class="text-azul-sea font-[400 text-[34px] leading-[38px]">
        {{ $page->title }}
    </h1>
    <x-badge-product-stock class="" stock="{{$page->stock}}" />
</div>

<div
    class="text-azul-mist my-6 flex flex-row-reverse items-center justify-end gap-2 text-start text-2xl font-[500]"
>
    <x-badge-product-offer
        class="text-azul-mist mx-1"
        :visible="$page->offer_price"
    />
    {!! $page->getFormatedPriceWithDiscount(inverse: true) !!}
</div>
<hr class="border- mb-6 w-full border-gray-200" />

<div class="pt-12">
    {!! $page->description !!}
</div>

<hr class="border- my-6 w-full border-gray-200" />

<livewire:product-add-cart :product="$page" />

<hr class="border- my-6 w-full border-gray-200" />

<x-product-tags-categories :product="$page" />
