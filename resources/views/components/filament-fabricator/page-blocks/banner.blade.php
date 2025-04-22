<section {{ $attributes->merge(['class' => 'banner']) }}>
    <div class="fondo">
        <img
            src="{{ 'storage/'.$attributes['image'] }}"
            alt="{{ $attributes['title'] }}"
            class="relative z-0 h-full w-full object-cover lg:absolute lg:top-0"
        />
        <div class="text-azul-sea relative z-10 lg:m-12 lg:ms-auto lg:w-1/2">
            <x-basic
                :title="$attributes['title']"
                :subtitle="$attributes['subtitle']"
                :text="$attributes['description']"
                class="mb-2 lg:mb-4"
            />
            <x-boton
                :buttonText="$attributes['button_text']"
                :buttonLink="$attributes['button_link']"
            />
        </div>
    </div>
</section>
