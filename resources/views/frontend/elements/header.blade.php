<flux:header
    container="true"
    class="@container full-container flex shadow-lg lg:h-[92px]"
>
    <div class="flex flex-wrap items-center justify-between">
        <x-logo-fundacion
            class="flex items-center justify-start py-4 lg:justify-center lg:py-0"
        />

        <div class="flex items-end justify-end gap-4 lg:hidden lg:gap-10">
            <livewire:cart-button-component wire:key="cart-button-mobile" />

            <flux:sidebar.toggle
                class="border-azul-sea stroke-azul-sea text-azul-sea me-4 cursor-pointer rounded-full! border"
                size="base"
                icon:variant="outline"
                icon="bars-3"
                inset="left"
            />
        </div>
    </div>

    <livewire:nav-menu type="desktop" />
</flux:header>
<div class="full-container">
    @includeIf('frontend.elements.header.sub-header', [Route::currentRouteName() != 'home'])
</div>

<livewire:nav-menu type="mobile" />
