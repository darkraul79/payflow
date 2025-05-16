<flux:header
    container="true"
    class="@container full-container flex shadow-lg lg:h-[92px]"
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

    <livewire:nav-menu type="desktop" />
</flux:header>
<div class="full-container">
    @includeIf('frontend.elements.header.sub-header', [Route::currentRouteName() != 'home'])
</div>

<livewire:nav-menu type="mobile" />
