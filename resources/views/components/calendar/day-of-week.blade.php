@php
    use Illuminate\Support\Carbon;
@endphp

<div
    class="text-azul-mist -mt-px -ml-px flex flex-1 items-center justify-center font-semibold md:h-12"
>
    <p class="mb-0 hidden text-sm md:mb-1 md:block">
        {{ Str::title(Carbon::parse($day)->translatedFormat('l')) }}
    </p>

    <p class="mb-0 text-sm md:mb-1 md:hidden">
        {{ Str::title(Carbon::parse($day)->isoFormat('dd')) }}
    </p>
</div>
