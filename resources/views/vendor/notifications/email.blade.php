{{--@formatter:off--}}
<x-mail::message>
{{-- Greeting --}}

@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
    # Opps!,
@else
    # Hola!,
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}
@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
$color = match ($level) {
    'success', 'error' => $level,
    default => 'primary',
};
?>

<x-mail::button :url="$actionUrl" :color="$color">
    {{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}
@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)

@endisset
</x-mail::message>
