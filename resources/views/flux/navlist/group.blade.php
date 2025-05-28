@props([
    "expandable" => false,
    "expanded" => true,
    "heading" => null,
    "href" => null,
])

<?php if ($expandable && $heading): ?>

<ui-disclosure
    {{ $attributes->class("group/disclosure") }}
    @if ($expanded === true)
        open
    @endif
    data-flux-navlist-group
>
    <button
        type="button"
        class="group/disclosure-button text-azul-mist mb-[2px] flex h-10 w-full items-center rounded-lg hover:bg-zinc-800/5 hover:text-zinc-800 lg:h-8"
    >
        <div class="ps-3 pe-4">
            <flux:icon.chevron-down
                class="hidden size-3! group-data-open/disclosure-button:block"
            />
            <flux:icon.chevron-right
                class="block size-3! group-data-open/disclosure-button:hidden"
            />
        </div>

        @if ($href)
            <a href="{{ $href }}">
                <span class="text-sm leading-none font-normal">
                    {{ $heading }}
                </span>
            </a>
        @else
            <span class="text-sm leading-none font-normal">
                {{ $heading }}
            </span>
        @endif
    </button>

    <div
        class="relative hidden space-y-[2px] ps-7 data-open:block"
        @if ($expanded === true) data-open @endif
    >
        <div class="absolute inset-y-[3px] start-0 ms-4 w-px bg-zinc-200"></div>

        {{ $slot }}
    </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div {{ $attributes->class("block space-y-[2px]") }}>
    <div class="px-3 py-2">
        <div class="text-sm leading-none font-medium text-zinc-400">
            {{ $heading }}
        </div>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>

<?php else: ?>

<div {{ $attributes->class("block space-y-[2px]") }}>
    {{ $slot }}
</div>

<?php endif; ?>
