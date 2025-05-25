<x-filament::section class="" icon="heroicon-m-shopping-bag">
    <x-slot name="heading" class="flex items-center">
        <div class="flex flex-row items-center justify-between">
            <div>Pedido</div>

            <div>
                <x-filament::badge
                    :color="$record->state?->colorEstado()"
                    :icon="$record->state?->icono()"
                >
                    {{ $record->state?->name }}
                </x-filament::badge>
            </div>
        </div>
    </x-slot>

    <x-slot name="description">
        {{ $record->number }}
    </x-slot>
    <div class="mb-5 pb-5">
        @if (count($relationManagers = $this->getRelationManagers()))
            <x-filament-panels::resources.relation-managers
                :active-manager="$this->activeRelationManager"
                :managers="$relationManagers"
                :owner-record="$record"
                :page-class="static::class"
            />
        @endif
    </div>

    @include('filament.resources.order-resource.pages.order-totals')

    {{-- Content --}}
</x-filament::section>
