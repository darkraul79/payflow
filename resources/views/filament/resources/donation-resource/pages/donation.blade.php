<div class="w-full">
    <div class="flex flex-row items-center justify-between">
        <div
            class="fi-section-header-heading text-base leading-6 font-semibold text-gray-950 dark:text-white"
        >
            <div class="flex items-center gap-3">
                <x-heroicon-s-gift
                    class="fi-section-header-icon fi-color-{$iconColor} h-6 w-6 self-start text-gray-400 dark:text-gray-500"
                />
                <div>
                    Donación
                    <br />
                    <span class="text-[14px] font-normal text-gray-400">
                        {{ $record->number }}
                    </span>
                </div>
            </div>
        </div>

        <div>
            <x-filament::badge
                :color="$record->colorType()"
                :icon="$record->iconType()"
            >
                {{ $record->type }}
            </x-filament::badge>
            <span class="text-xs font-normal text-gray-400 italic">
                {{ $record->fechaHumanos() }}
            </span>
        </div>
    </div>
</div>
