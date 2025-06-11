<a
    href="{{ route('home') }}"
    {{ $attributes->class(['max-w-[180px] md:max-w-[200px] lg:max-w-none'])->merge(['class']) }}
>
    <img
        src="{{ asset('images/logo-fundacion-horizontal.svg') }}"
        class="h-auto w-full max-w-[261px]"
        alt="{{ config('app.name') }}"
    />
</a>
