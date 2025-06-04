<x-filament-panels::page>
    <div class="flex flex-col-reverse gap-6 lg:flex-row">
        <div class="lg:flex-grow">
            <div class="flex items-start justify-between gap-4">
                @include('filament.resources.order-resource.pages.addresses')
            </div>

            @include('filament.resources.order-resource.pages.order-items')
        </div>
        <div class="lg:flex-1/5">
            @include('filament.resources.order-resource.pages.order-states', ['update' => false, 'infotext' => 'Comprueba los estados por lo que ha pasado el pedido.'])

            <div class="my-6">
                @include('filament.resources.payments', ['update' => false])
            </div>
        </div>
    </div>
</x-filament-panels::page>
