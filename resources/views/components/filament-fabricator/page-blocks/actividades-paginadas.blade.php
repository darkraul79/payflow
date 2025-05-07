<section id="activities-section" class="actividades">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        subtitleClass="text-center"
        titleClass="text-center"
        class=""
    />
    <div
        class="{{ $attributes['classGrid'] }} my-12 grid grid-cols-1 items-stretch justify-center space-y-8 md:grid-cols-2 md:gap-6 md:space-y-0 lg:grid-cols-3 lg:gap-8"
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
    <div class="pagination">
        {{ $attributes['activities']->links() }}
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = document.getElementById('activities-section');

            // Restaurar la posición del scroll si existe en localStorage
            const savedScrollPosition = localStorage.getItem('scrollPosition');
            if (savedScrollPosition) {
                window.scrollTo(0, parseInt(savedScrollPosition, 10));
                localStorage.removeItem('scrollPosition'); // Limpiar después de restaurar
            }

            // Escuchar clics en los enlaces de paginación
            document.querySelectorAll('.pagination a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (section) {
                        // Guardar la posición del scroll
                        localStorage.setItem(
                            'scrollPosition',
                            section.offsetTop,
                        );
                    }
                });
            });
        });
    </script>
</section>
