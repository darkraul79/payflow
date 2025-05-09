<div
    class="flex h-full w-full flex-grow flex-col items-center justify-end font-light lg:flex-row"
>
    <flux:navbar class="mx-auto -mb-px max-lg:hidden">
        @foreach ($menu as $navItem)
            @if ($navItem->children->count() && $navItem->title != 'Home')
                <flux:dropdown>
                    <flux:navbar.item icon:trailing="chevron-down">
                        <a href="{{ $navItem->getUrl() }}">
                            {{ $navItem->title }}
                        </a>
                    </flux:navbar.item>
                    <flux:navmenu>
                        @foreach ($navItem->children as $child)
                            <flux:navmenu.item href="{{ $child->getUrl() }}">
                                {{ $child->title }}
                            </flux:navmenu.item>
                        @endforeach
                    </flux:navmenu>
                </flux:dropdown>
            @else
                <flux:navbar.item
                    href="{{ $navItem->getUrl() == '/home' ? '/' : $navItem->getUrl() }}"
                >
                    {{ $navItem->title }}
                </flux:navbar.item>
            @endif
        @endforeach
    </flux:navbar>

    <a
        href="#"
        class="bg-amarillo text-azul-mist hover:bg-azul-mist hover:text-amarillo flex h-full min-h-20 w-full items-center justify-center gap-2 p-2 text-center font-semibold lg:my-0 lg:w-[261px]"
    >
        <flux:icon.heart variant="solid" class="size-4" />
        Haz una donación
    </a>
</div>
