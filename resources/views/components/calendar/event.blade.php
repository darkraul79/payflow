<div
    @if ($eventClickEnabled)
        wire:click.stop="onEventClick('{{ $event["url"] }}')"
    @endif
    class="block h-full w-full cursor-pointer text-right"
>
    <p class="mb-0 h-full w-full text-xs font-medium">
        {{ $event["title"] }}
    </p>
</div>
