@props([
    'visible',
    'text' => 'Rebaja',
])

@if ($visible)
    <span
        {{ $attributes->merge(['class' => 'text-error bg-error/10 flex h-fit items-center justify-center rounded-lg px-1.5 py-1 text-xs']) }}
    >
        {{ $text }}
    </span>
@endif
