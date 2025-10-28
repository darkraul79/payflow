@if ($page->donacion)
    <div class="w-full items-start justify-end md:w-2/6">
        <div class="top-0 right-0 md:sticky md:max-w-[400px]">
            <livewire:donacion-banner prefix="sidebar" wire:key="sidebar" />
        </div>
    </div>
@endif
