<div class="grid grid-cols-5 gap-5 lg:mb-20">
    <div
        class="{{ count($items) ? 'col-span-5 lg:col-span-3' : 'col-span-5' }}"
    >
        @include('frontend.elements.cart.cartItemsTable')
    </div>
    @if (count($items))
        <div class="col-span-5 lg:col-span-2">
            @include('frontend.elements.cart.cartTotalsTable')
        </div>
    @endif
</div>
