<form action="" class="order-form w-full">
    <div class="grid w-full grid-cols-1 gap-5 lg:mb-20 lg:grid-cols-6">
        @include('frontend.elements.order.fields', ['prefix' => 'billing.'])

        <div class="col-span-6 inline-flex items-center gap-2">
            <x-checkbox
                name="addSendAddress"
                id="addSendAddress"
                wire:model.live="addSendAddress"
                class="rounded-sm"
                required
                :checked="false"
            />
            <label for="phone" class="text-xs">
                Enviar a otra dirección
                <span>(opcional)</span>
            </label>
        </div>

        @if ($addSendAddress)
            <header class="col-span-6 mt-6 mb-12 block w-full">
                <hr class="mb-12 w-full border-t border-gray-200" />
                <h1 class="title">
                    Enviar a otra dirección
                    <dirección></dirección>
                </h1>
            </header>
            @include('frontend.elements.order.fields', ['prefix' => 'shipping.'])
        @endif
    </div>
</form>
