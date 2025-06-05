@php
    use Filament\Support\Enums\IconSize;
@endphp

<div class="w-full">
    <div class="flex flex-row items-start justify-between">
        <div class="fi-section-header-heading leading-6 text-gray-950">
            <div class="flex items-center gap-3">
                <x-heroicon-s-gift
                    class="fi-section-header-icon fi-color-{$iconColor} h-6 w-6 self-start text-gray-400 dark:text-gray-500"
                />
                <div class="">
                    <span class="text-base font-semibold">Donación</span>
                    -
                    <span class="text-azul-mist text-base font-semibold italic">
                        {{ $record->getFormatedAmount() }}
                    </span>
                    <br />
                    <span class="text-[14px] font-normal text-gray-400">
                        {{ $record->number }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-end">
            <x-filament::badge
                :color="$record->colorType()"
                :icon="$record->iconType()"
                class="inline-flex"
            >
                {{ $record->type }}
            </x-filament::badge>
        </div>
    </div>
    <div
        class="mt-4 w-full border-t border-gray-300 pt-3 text-xs text-gray-500"
    >
        <x-info-bullet-collapsible
            class="flex-row-reverse justify-start"
            :info="$record->info"
            id="{{$record->id}}"
        >
            <i>Autorización</i>
        </x-info-bullet-collapsible>
    </div>
</div>
