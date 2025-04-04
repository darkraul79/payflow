<section class="actividades">
    <h3 class="text-center">Últimas actividades</h3>
    <h2 class="text-center">
        Actividades importantes que te podrían interesar
    </h2>

    <div
        class="my-12 grid grid-cols-1 space-y-8 md:grid-cols-2 md:gap-6 md:space-y-0 lg:grid-cols-3 lg:gap-12"
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
