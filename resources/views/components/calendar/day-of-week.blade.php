@php
    use Illuminate\Support\Carbon;
@endphp

<div
    class="text-azul-mist -mt-px -ml-px flex flex-1 items-center justify-center md:h-12"
>
    <p class="mb-0 hidden text-xs md:block">
        {{ Str::title(Carbon::parse($day)->translatedFormat('l')) }}
    </p>

    <p class="mb-0 text-xs md:hidden">
        {{ Str::title(Carbon::parse($day)->isoFormat('dd')) }}
    </p>
</div>
