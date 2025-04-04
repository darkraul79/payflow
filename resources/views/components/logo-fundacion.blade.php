<a
    href="{{ route('home') }}"
    {{ $attributes->class([''])->merge(['class']) }}
>
    <img
        src="{{ asset('images/logo-fundacion-horizontal.svg') }}"
        class="h-auto w-full max-w-[261px]"
        alt="{{ config('app.name') }}"
    />
</a>
