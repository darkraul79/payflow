@props([
    "buttonLink",
    "buttonText",
    "visible" => true,
    "icon" => null,
    "is_download" => false,
])

@if ($visible)
    <a
        href="{{ $buttonLink }}"
        {{ $attributes->merge(["class" => "btn btn-primary block"]) }}
        title="{{ $buttonText }}"
        @if ($is_download)
            download
        @endif
    >
        @if ($icon)
            <img
                src="{{ asset($icon) }}"
                class="icon me-2"
                alt="{{ $buttonText }}"
            />
        @endif

        {{ $buttonText }}
    </a>
@endif
