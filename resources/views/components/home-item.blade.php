<div
    {{ $attributes->class(['flex flex-col justify-center gap-y-3 text-center max-w-[310px] mx-auto w-full'])->merge(['class']) }}
>
    <img src="{{ asset($icon) }}" class="icon mx-auto" alt="{{ $title }}" />

    <h4>{{ $title }}</h4>

    <p>
        {!! $description !!}
    </p>
    <a href="{{ $link }}" class="btn btn-primary btn-small mx-auto my-4">
        {{ $linkText }}
    </a>
</div>
