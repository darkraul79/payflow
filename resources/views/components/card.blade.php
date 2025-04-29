<article
    {{
        $attributes
            ->class([
                'rounded-lg bg-white p-3 border-azul-cobalt border shadow-lg flex flex-col justify-between hover:scale-105 transition-all duration-300 ease-out',
            ])
            ->merge(['class'])
    }}
>
    @if ($image)
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            class="h-48 w-full rounded-lg object-cover"
        />
    @endif

    <div class="mx-auto my-5 px-2">
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
            {{ Str::limit($text, 120, '...') }}
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
