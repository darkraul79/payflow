<div
    class="flex h-full w-full flex-grow flex-col items-center justify-end font-light lg:flex-row"
>
    @if ($menu)
        <flux:navbar class="mx-auto -mb-px max-lg:hidden">
            @foreach ($menu?->menuItems as $item)
                @if ($item?->children->count() && $item->title != 'Home')
                    <flux:dropdown>
                        <flux:navbar.item icon:trailing="chevron-down">
                            <a href="{{ $item->url }}">
                                {{ $item->title }}
                            </a>
                        </flux:navbar.item>
                        <flux:navmenu>
                            @foreach ($item->children as $child)
                                <flux:navmenu.item href="{{ $child->url }}">
                                    {{ $child->title }}
                                </flux:navmenu.item>
                            @endforeach
                        </flux:navmenu>
                    </flux:dropdown>
                @else
                    <flux:navbar.item
                        href="{{ $item->url == '/home' ? '/' : $item->url }}"
                    >
                        {{ $item->title }}
                    </flux:navbar.item>
                @endif
            @endforeach

            <livewire:cart-component />
        </flux:navbar>
    @endif

    <a
        href="#"
        class="bg-amarillo text-azul-mist hover:bg-azul-mist hover:text-amarillo flex h-full min-h-20 w-full items-center justify-center gap-2 p-2 text-center font-semibold lg:my-0 lg:w-[261px]"
    >
        <flux:icon.heart variant="solid" class="size-4" />
        Haz una donación
    </a>
</div>
