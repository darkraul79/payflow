@props([
    'subtitle' => null,
    'title' => null,
    'text' => null,
    'subtitleClass' => false,
    'titleClass' => false,
    'textClass' => false,
])

<header
    {{
        $attributes->merge([
            'class' => 'mb-12',
        ])
    }}
>
    @if ($subtitle)
        <h2 class="subtitle {{ $subtitleClass }}">{{ $subtitle }}</h2>
    @endif

    @if ($title)
        <h3 class="title {{ $titleClass }}">{{ $title }}</h3>
    @endif
</header>

@if ($text)
    <div class="{{ $textClass }} text-balance">
        {!! html_entity_decode($text) !!}
    </div>
@endif
