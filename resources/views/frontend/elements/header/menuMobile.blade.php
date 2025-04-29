<flux:sidebar
    sticky="true"
    stashable="true"
    class="z-50 w-full border-r border-zinc-200 bg-white md:hidden rtl:border-r-0 rtl:border-l"
>
    <div class="flex flex-row items-start justify-between">
        <x-logo-fundacion />
        <flux:sidebar.toggle class="" icon="x-mark" />
    </div>
    <flux:navlist variant="outline">
        @foreach ($menu as $navItem)
            @if ($navItem->has('children') && $navItem->title != 'Home')
                <flux:navlist.group
                    expandable="true"
                    :expanded="false"
                    heading="{{ $navItem->title }}"
                >
                    @foreach ($navItem->children as $child)
                        <flux:navlist.item href="{{ $child->getUrl() }}">
                            {{ $child->title }}
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist.group>
            @else
                <flux:navlist.item
                    href="{{ $navItem->getUrl() == '/home' ? '/' : $navItem->getUrl() }}"
                >
                    {{ $navItem->title }}
                </flux:navlist.item>
            @endif
        @endforeach
    </flux:navlist>
    <flux:spacer />
</flux:sidebar>
