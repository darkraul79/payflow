<section {{ $attributes->merge(['class' => 'banner']) }}>
    <div class="fondo">
        <img
            src="{{ $attributes['image'] }}"
            alt="{{ $attributes['title'] }}"
            class="relative z-0 h-full w-full object-cover lg:absolute lg:top-0"
        />
        <div class="text-azul-sea relative z-10 lg:m-12 lg:ms-auto lg:w-1/2">
            <div class="mb-2 lg:mb-4">
                <h2 class="subtitle">{{ $attributes['title'] }}</h2>
                <h2 class="title">{{ $attributes['subtitle'] }}</h2>
            </div>

            {!! html_entity_decode($attributes['description']) !!}

            <x-boton
                :buttonText="$attributes['button_text']"
                :buttonLink="$attributes['button_link']"
            />
        </div>
    </div>
</section>
