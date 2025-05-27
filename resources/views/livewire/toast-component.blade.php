<div>
    <div
        class="{{ $boxClasses }} {{ $animate ? 'translate-x-0 border' : 'translate-x-[120%]' }} fixed right-5 bottom-5 z-[500] flex min-h-[52px] w-full max-w-md cursor-pointer items-center space-x-4 divide-x divide-gray-200 overflow-hidden rounded-xl shadow-lg transition-all duration-300 rtl:divide-x-reverse"
        role="alert"
    >
        <div
            class="relative flex h-full w-full items-center gap-x-6 px-5 py-5"
            wire:click="close"
            @if($show)wire:click.outside="close" @endif
        >
            <div class="{{ $color }} rounded-full border-8 bg-white ring-8">
                @if ($type === 'success')
                    <x-bi-check-lg class="text-azul-marino h-6 w-6" />
                @elseif ($type === 'warning')
                    <x-bi-exclamation-lg class="text-azul-marino h-6 w-6" />
                @elseif ($type === 'error')
                    <x-bi-exclamation-octagon
                        class="text-azul-marino h-6 w-6"
                    />
                @else
                    <x-bi-info class="text-azul-sea h-6 w-6" />
                @endif
            </div>
            <div class="font-inter text-black">
                <strong class="mb-2 block text-sm font-semibold">
                    {{ $title }}
                </strong>
                <span class="block text-xs font-normal">
                    {{ $message }}
                </span>
            </div>

            <x-bi-x class="absolute top-2 right-2 h-8 w-8" />
        </div>
    </div>
</div>
