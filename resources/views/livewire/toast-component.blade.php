<div>
    <div
        class="border-azul-sea {{ $animate ? 'translate-x-0 border' : 'translate-x-[120%]' }} fixed right-5 bottom-5 z-[500] flex min-h-[52px] w-full max-w-xs cursor-pointer items-center space-x-4 divide-x divide-gray-200 rounded-lg bg-white text-gray-500 shadow-lg transition-all duration-300 rtl:divide-x-reverse"
        role="alert"
    >
        <div
            class="relative h-full w-full p-4"
            wire:click="close"
            @if($show)wire:click.outside="close" @endif
        >
            <div class="text-azul-sea font-inter text-sm font-normal">
                {{ $message }}
            </div>
        </div>
    </div>
</div>
