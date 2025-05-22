<div>
    <flux:sidebar
        sticky="true"
        stashable="true"
        class="z-50 w-full max-w-xl border-r border-zinc-200 bg-white lg:hidden rtl:border-r-0 rtl:border-l"
    >
        <div class="flex flex-row items-start justify-between">
            <x-logo-fundacion />
            <flux:sidebar.toggle class="" icon="x-mark" />
        </div>
        @if ($menu)
            <flux:navlist variant="outline">
                @foreach ($menu?->menuItems as $navItem)
                    @if ($navItem?->children->count() && $navItem->title != 'Home')
                        <flux:navlist.group
                            expandable="true"
                            :expanded="false"
                            href="{{ $navItem->url }}"
                            heading="{{ $navItem->title }}"
                        >
                            @foreach ($navItem->children as $child)
                                <flux:navlist.item href="{{ $child->url }}">
                                    {{ $child->title }}
                                </flux:navlist.item>
                            @endforeach
                        </flux:navlist.group>
                    @else
                        <flux:navlist.item
                            href="{{ $navItem->url == '/home' ? '/' : $navItem->url }}"
                        >
                            {{ $navItem->title }}
                        </flux:navlist.item>
                    @endif
                @endforeach
            </flux:navlist>
        @endif
        <hr class="border- my-3 w-full border-gray-200 ">


        <flux:spacer />
        <flux:navlist variant="outline">
            <a
                href="#"
                class="bg-amarillo text-azul-mist hover:bg-azul-mist hover:text-amarillo flex h-full min-h-20 w-full items-center justify-center gap-2 p-2 text-center font-semibold lg:my-0 lg:w-[261px]"
            >
                <flux:icon.heart variant="solid" class="size-4" />
                Haz una donación
            </a>
        </flux:navlist>
    </flux:sidebar>
</div>
