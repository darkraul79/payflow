<section class="actividades">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        subtitleClass="text-center"
        titleClass="text-center"
        class=""
    />
    <div
        class="{{ $attributes['classGrid'] }} lg:gap- my-12 grid grid-cols-1 items-stretch justify-center space-y-8 md:gap-6 md:space-y-0"
    >
        @foreach ($attributes['activities'] as $activity)
            <x-card
                :image="$activity->getFirstMedia('principal')?->getUrl('card-thumb')"
                :title="$activity->title"
                :text="$activity->resume"
                :date="$activity->getFormatDateBlog()"
                button-text="Leer más"
                :button-link="$activity->getUrl()"
            />
        @endforeach
    </div>
</section>
