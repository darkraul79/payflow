<div class="w-full">
    <div class="flex flex-row items-start justify-between">
        <div class="fi-section-header-heading leading-6 text-gray-950">
            <div class="flex items-center gap-3">
                <x-heroicon-s-gift
                    class="fi-section-header-icon fi-color-{$iconColor} h-6 w-6 self-start text-gray-400 dark:text-gray-500"
                />
                <div class="">
                    <span class="text-base font-semibold">Donación</span>

                    <br />
                    <span class="text-[14px] font-normal text-gray-400">
                        {{ $record->number }}
                    </span>

                    <x-filament::badge
                        :color="$record->colorType()"
                        class="inline-flex"
                    >
                        {{ $record->type }}
                    </x-filament::badge>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-end">
            <x-filament::badge
                :color="$record->colorType()"
                :icon="$record->iconType()"
                class="inline-flex"
            >
                {{ $record->state->name }}
            </x-filament::badge>

            <x-filament::badge
                :color="$record->colorFrequency()"
                class="mx-2 mt-4 inline-flex"
            >
                {{ $record->frequency }}
            </x-filament::badge>
            <div class="my-2 w-full px-2 text-end text-xs font-normal">
                Próximo cobro:
                <span class="text-xs font-normal text-gray-500 italic">
                    {{ $record->getNextPayDateFormated() }}
                </span>
            </div>
        </div>
    </div>
    <x-info-bullet-collapsible
        class="flex-row-reverse justify-start"
        :info="$record->info"
        id="{{$record->id}}"
    >
        Información de autorización
    </x-info-bullet-collapsible>
</div>
