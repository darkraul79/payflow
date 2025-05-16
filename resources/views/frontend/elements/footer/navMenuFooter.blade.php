<div class="{{ ! $menu ? 'hidden' : 'footer-nav' }}">
    @if ($menu?->name)
        <h6>{{ $menu->name }}</h6>
    @endif

    @if ($menu?->menuItems->count())
        <nav>
            @foreach ($menu?->menuItems as $item)
                <x-footer-link
                    text="{{ $item->title }}"
                    link="{{ $item->url }}"
                />
            @endforeach
        </nav>
    @endif
</div>
