<x-filament-panels::page>
    <div class="flex flex-col gap-6 lg:flex-row">
        <div class="flex flex-grow flex-col gap-12 lg:flex-2/3">
            @include('filament.resources.donation-resource.pages.donation')

            @include('filament.resources.payments')
        </div>
        <div class="gap-6 lg:flex-1/3 lg:flex-row">
            @include('filament.resources.order-resource.pages.addresses')
        </div>
    </div>
</x-filament-panels::page>
