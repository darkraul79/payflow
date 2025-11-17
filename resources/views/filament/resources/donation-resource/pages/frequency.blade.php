@php
    use App\Models\Donation;
@endphp

@if ($record->type == \App\Enums\DonationType::RECURRENTE->value)
    <x-filament::section
        :compact="true"
        heading="Frecuencia"
        :icon="$record->iconType()"
        :icon-color="$record->colorFrequency()"
        icon-size="md"
        :header-end="$record->frequency"
        class="mb-6"
    >
        <div class="my-2 w-full px-2 text-end text-xs font-normal">
            Próximo cobro:
            <span class="text-xs font-normal text-gray-500 italic">
                {{ $record->getNextPayDateFormated() }}
            </span>
        </div>
    </x-filament::section>
@endif
