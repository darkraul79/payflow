@props([
    'stock',
    'text',
    'color',
])

<span
    {{ $attributes->merge(['class' => $color . ' flex h-fit items-center justify-center rounded-lg px-1.5 py-1 text-xs']) }}
>
    {{ $text }}
</span>
