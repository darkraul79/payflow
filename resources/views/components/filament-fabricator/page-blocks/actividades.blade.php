<section {{ $attributes->merge(['class' => 'actividades']) }}>
    <h2 class="subtitle text-center">{{ $attributes['title'] }}</h2>
    <h3 class="title text-center">
        {{ $attributes['subtitle'] }}
    </h3>
    <div
        class="{{ $attributes['classGrid'] }} my-12 grid grid-cols-1 space-y-8 md:gap-6 md:space-y-0 lg:gap-12"
    >
        @for ($i = 0; $i < 3; $i++)
            <x-card
                :image="asset('images/banner.webp')"
                title="II Concierto solidario de luis cobos"
                text="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                date="Diciembre 20, 2025"
                button-text="Leer más"
                button-link="#"
            />
        @endfor
    </div>
</section>
