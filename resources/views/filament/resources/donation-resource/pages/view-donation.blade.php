@php
    use Filament\Support\Enums\IconSize;
@endphp

<x-filament-panels::page>
    <div class="flex flex-col gap-6 lg:flex-row">
        <div class="flex flex-grow flex-col gap-12 lg:flex-2/3">
            @include('filament.resources.donation-resource.pages.donation')

            @include('filament.resources.payments')
        </div>
        <div class="gap-6 lg:flex-1/3 lg:flex-row">
            @include('filament.resources.donation-resource.pages.frequency')

            @include('filament.resources.order-resource.pages.order-states', ['update' => false, 'infotext' => 'Comprueba los estados por lo que ha pasado la donación.'])
            @include('filament.resources.order-resource.pages.addresses')
        </div>
    </div>
</x-filament-panels::page>
