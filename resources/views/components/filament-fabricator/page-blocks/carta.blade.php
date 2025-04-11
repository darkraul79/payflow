<section class="carta mt-4 mb-8">
    <div class="flex flex-col items-start justify-between gap-8 md:flex-row">
        <img
            src="{{ asset($attributes['image']) }}"
            alt="{{ $attributes['title'] }}"
            class="max-h-40 w-full object-cover md:max-h-fit md:max-w-fit"
        />
        <div class="w-full flex-grow md:max-w-2/3">
            <h2 class="subtitle mb-1">{{ $attributes['subtitle'] }}</h2>
            <h3 class="title mb-4">{{ $attributes['title'] }}</h3>
            <div class="mt-4 gap-6 leading-5 md:columns-2">
                {!! html_entity_decode($attributes['text']) !!}
            </div>
        </div>
    </div>
</section>
