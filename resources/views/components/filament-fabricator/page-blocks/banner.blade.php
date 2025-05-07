<section {{ $attributes->merge(['class' => 'banner']) }}>
    <div class="fondo">
        <img
            src="{{ 'storage/' . $attributes['image'] }}"
            alt="{{ $attributes['title'] }}"
            class="relative z-0 h-full w-full object-cover lg:absolute lg:top-0"
        />
        <div
            class="text-azul-sea {{ $attributes['box-alignment'] }} relative z-10 lg:m-12 lg:w-1/2"
        >
            <x-basic
                :title="$attributes['title']"
                :subtitle="$attributes['subtitle']"
                :text="$attributes['description']"
                :subtitleClass="$attributes['alignment_text']"
                :title-class="$attributes['alignment_text']"
                :textClass="$attributes['alignment_text']"
                class="mb-2 lg:mb-4"
            />
            <x-boton
                :buttonText="$attributes['button_text']"
                :buttonLink="$attributes['button_link']"
                :class="$attributes['alignment_button']"
            />
        </div>
    </div>
</section>
