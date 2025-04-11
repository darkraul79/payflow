<flux:header
    container="true"
    class="@container flex md:container md:mx-auto lg:h-[92px]"
>
    <div class="flex flex-wrap items-center justify-between">
        <x-logo-fundacion
            class="flex items-center justify-start py-4 lg:justify-center lg:py-0"
        />

        <flux:sidebar.toggle
            class="border-azul-sea stroke-azul-sea text-azul-sea me-4 rounded-full! border lg:hidden"
            size="base"
            icon:variant="outline"
            icon="bars-3"
            inset="left"
        />
    </div>

    <div
        class="flex h-full w-full flex-grow flex-col items-center justify-end font-light lg:flex-row"
    >
        <flux:navbar class="-mb-px max-lg:hidden">
            @foreach (App\Models\Page::published()->firstLevel()->get() as $navItem)
                @if ($navItem->children->isNotEmpty())
                    <flux:dropdown>
                        <flux:navbar.item icon:trailing="chevron-down">
                            {{ $navItem->title }}
                        </flux:navbar.item>
                        <flux:navmenu>
                            @foreach ($navItem->children as $child)
                                <flux:navmenu.item
                                    href="{{ $child->getUrl() }}"
                                >
                                    {{ $child->title }}
                                </flux:navmenu.item>
                            @endforeach
                        </flux:navmenu>
                    </flux:dropdown>
                @else
                    <flux:navbar.item href="{{ $navItem->getUrl() }}">
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
</flux:header>
<div class="container">
    @includeIf('sub-header', [Route::currentRouteName() != 'home'])
</div>
<flux:sidebar
    sticky="true"
    stashable="true"
    class="dark:border-b-azul-gray z-50 w-full border-r border-zinc-200 bg-zinc-50 md:hidden rtl:border-r-0 rtl:border-l dark:bg-zinc-900"
>
    <div class="flex flex-row items-start justify-between">
        <x-logo-fundacion />
        <flux:sidebar.toggle class="" icon="x-mark" />
    </div>
    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" href="#" current>Home</flux:navlist.item>
        <flux:navlist.item icon="inbox" badge="12" href="#">
            Inbox
        </flux:navlist.item>
        <flux:navlist.item icon="document-text" href="#">
            Documents
        </flux:navlist.item>
        <flux:navlist.item icon="calendar" href="#">Calendar</flux:navlist.item>
        <flux:navlist.group expandable="true" heading="Favorites">
            <flux:navlist.item href="#">Marketing site</flux:navlist.item>
            <flux:navlist.item href="#">Android app</flux:navlist.item>
            <flux:navlist.item href="#">Brand guidelines</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>
    <flux:spacer />
    <flux:navlist variant="outline">
        <flux:navlist.item icon="cog-6-tooth" href="#">
            Settings
        </flux:navlist.item>
        <flux:navlist.item icon="information-circle" href="#">
            Help
        </flux:navlist.item>
    </flux:navlist>
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:profile
            avatar="https://fluxui.dev/img/demo/user.png"
            name="Olivia Martin"
        />
        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                <flux:menu.radio>Truly Delta</flux:menu.radio>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <flux:menu.item icon="arrow-right-start-on-rectangle">
                Logout
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
