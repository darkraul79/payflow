<div
    {{ $attributes->class(['flex flex-col justify-center gap-y-3 text-center max-w-[310px] mx-auto w-full'])->merge(['class']) }}
>
    <img
        src="{{ asset('storage/' . $icon) }}"
        class="icon mx-auto"
        alt="{{ $title }}"
    />

    <h4>{{ $title }}</h4>

    <p>
        {!! $description !!}
    </p>
    <x-boton :buttonText="$linkText" :buttonLink="$link" class="mx-auto" />
</div>
