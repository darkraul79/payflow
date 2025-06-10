<article
    {{
        $attributes
            ->class([
                'rounded-lg bg-white p-3 border-azul-cobalt border shadow-md flex flex-col justify-between  transition-all duration-300 ease-out',
            ])
            ->merge(['class'])
    }}
>
    @if ($image)
        <a href="{{ $buttonLink }}" title="{{ $buttonText }}">
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                class="h-48 w-full rounded-lg object-cover"
            />
        </a>
    @endif

    <div class="my-5 w-full px-2">
        <span class="inline-flex gap-2 rounded-lg italic">
            <img
                src="{{ asset('images/icons/calendar.svg') }}"
                class="stroke-azul-sea w-4"
                alt="{{ $title }}"
            />
            {{ $date }}
        </span>
        <h4 class="my-1">{{ $title }}</h4>
        <p>
            {!! Str::limit($text, 120) !!}
        </p>
    </div>

    @if ($buttonLink && $buttonText)
        <a
            href="{{ $buttonLink }}"
            class="btn btn-primary block"
            title="{{ $buttonText }}"
        >
            {{ $buttonText }}
        </a>
    @endif
</article>
