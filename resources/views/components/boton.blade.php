@props([
    'buttonLink',
    'buttonText',
    'visible' => true,
    'icon' => null,
])

@if ($visible)
    <a
        href="{{ $buttonLink }}"
        {{ $attributes->merge(['class' => 'btn btn-primary block']) }}
        title="{{ $buttonText }}"
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
