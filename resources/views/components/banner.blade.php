<section {{ $attributes->class(['banner']) }}>
    <div class="fondo">
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            class="relative z-0 h-full w-full object-cover lg:absolute lg:top-0"
        />
        <div
            class="text-azul-sea {{ $css }} relative z-10 lg:m-12 lg:ms-auto lg:w-1/2"
        >
            <div class="mb-2 lg:mb-4">
                <h3>{{ $title }}</h3>
                <h2>{{ $subTitle }}</h2>
            </div>

            {!! $description !!}

            @if ($buttonLink && $buttonText)
                <a
                    href="{{ $buttonLink }}"
                    class="btn btn-primary block"
                    title="{{ $buttonText }}"
                >
                    {{ $buttonText }}
                </a>
            @endif
        </div>
    </div>
</section>
