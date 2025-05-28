<x-filament::section
    icon="bi-truck"
    description="Comprueba los estados por lo que ha pasado el pedido."
    heading="Seguimiento"
>
    <x-order-time-line :pedido="$record" />

    @if ($update && collect($record->getStates())->isNotEmpty())
        <form wire:submit.prevent="submit">
            {{ $this->form }}
            <div class="mt-5 mb-3">
                <x-filament::button
                    icon="heroicon-m-arrow-path"
                    type="button"
                    wire:click="submit"
                >
                    Actualizar estado
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament::section>
