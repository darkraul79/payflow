<div
    {{ $attributes->class(['breadcrumbs text-azul-sea flex flex-row gap-x-2 px-6 py-4 text-[12px]']) }}
>
    @foreach ($breadcrumbs as $url => $breadcrumb)
        @if ($loop->last)
            {{ $breadcrumb }}
        @else
            <a
                href="{{ $url }}"
                class="text-azul-gray hover:text-azul-sea"
                title="{{ $breadcrumb }}"
            >
                {{ $breadcrumb }}
            </a>
            <span class="text-azul-gray block">></span>
        @endif
    @endforeach
</div>
