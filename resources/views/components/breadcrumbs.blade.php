<div
    {{ $attributes->class(['breadcrumbs text-azul-sea flex flex-row gap-x-2 px-6 py-4 text-[12px]']) }}
>
    @foreach ($breadcrumbs as $breadcrumb)
        @if ($loop->last)
            {{ $breadcrumb['title'] }}
        @else
            <a
                href="{{ $breadcrumb['url'] }}"
                class="text-azul-gray hover:text-azul-sea"
                title="{{ $breadcrumb['title'] }}"
            >
                {{ $breadcrumb['title'] }}
            </a>
            <span class="text-azul-gray block">></span>
        @endif
    @endforeach
</div>
