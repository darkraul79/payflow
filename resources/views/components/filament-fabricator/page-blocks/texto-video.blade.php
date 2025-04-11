<section class="mission">
    <div class="flex w-full gap-x-24">
        <div class="w-2/5">
            <h2 class="subtitle">{{ $attributes['subtitle'] }}</h2>
            <h3 class="title mb-6">{{ $attributes['title'] }}</h3>

            <div class="richtext">
                {!! html_entity_decode($attributes['text']) !!}
            </div>
        </div>
        <div class="w-3/5">
            <iframe
                width="100%"
                height="315"
                src="{{ $attributes['video'] }}"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</section>
