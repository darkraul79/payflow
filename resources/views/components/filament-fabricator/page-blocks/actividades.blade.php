<section class="actividades">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        :subtitleClass="$attributes['alignment']?? 'text-left'"
        :titleClass="$attributes['alignment']?? 'text-left'"
        class=""
    />
    <div class="splide" data-per-page="{{ $attributes['number'] ?? 2 }}">
        <div class="splide__track">
            <ul class="splide__list">
                @foreach ($attributes['activities'] as $activity)
                    <li class="splide__slide">
                        <x-card
                            :image="$activity->getFirstMedia('principal')?->getUrl('card-thumb')"
                            :title="$activity->title"
                            :text="$activity->getResume()"
                            :date="$activity->getFormatDateBlog()"
                            button-text="Leer más"
                            :button-link="$activity->getLink()"
                        />
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="splide__arrows"></div>
    </div>
</section>

@pushonce('vite')
    @vite(['resources/js/slider.js'])
@endpushonce
