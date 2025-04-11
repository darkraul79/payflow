<section class="mission">
    <div
        class="flex w-full flex-col items-end justify-end gap-x-24 lg:flex-row"
    >
        <div class="w-full lg:w-2/5">
            <x-basic
                :title="$attributes['title']"
                :subtitle="$attributes['subtitle']"
                :text="$attributes['text']"
                title-class="mb-6"
                text-class="richtext"
            />
        </div>
        <div class="w-full lg:w-3/5">
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
