<section class="carta mb-8">
    <div class="flex flex-col items-start justify-between gap-8 md:flex-row">
        <img
            src="{{ asset($attributes['image']) }}"
            alt="{{ $attributes['title'] }}"
            class="max-h-40 w-full object-cover md:max-h-fit md:max-w-fit"
        />
        <div class="w-full flex-grow md:max-w-2/3">
            <x-basic
                :title="$attributes['title']"
                :subtitle="$attributes['subtitle']"
                :text="$attributes['text']"
                subtitle-class="mb-1"
                title-class="mb-4"
                text-class="mt-4 gap-6 leading-5 md:columns-2"
            />
        </div>
    </div>
</section>
