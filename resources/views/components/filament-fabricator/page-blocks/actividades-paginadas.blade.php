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
        document.addEventListener("DOMContentLoaded", () => {
            const section = document.getElementById("activities-section");

            if (!section) {
                console.error("El elemento con id \"activities-section\" no se encontró.");
                return;
            }

            const activitiesContainer = section.querySelector("div");

            // Restaurar la posición del scroll si existe en localStorage
            const savedScrollPosition = localStorage.getItem("scrollPosition");
            if (savedScrollPosition) {
                window.scrollTo(0, parseInt(savedScrollPosition, 10));
                localStorage.removeItem("scrollPosition"); // Limpiar después de restaurar
            }

            // Escuchar clics en los enlaces de paginación
            document.querySelectorAll(".pagination a").forEach((link) => {
                link.addEventListener("click", (event) => {
                    event.preventDefault(); // Evitar el comportamiento predeterminado

                    if (section && activitiesContainer) {
                        // Guardar la posición del scroll
                        localStorage.setItem("scrollPosition", section.offsetTop);

                        // Establecer una altura fija al contenedor para evitar saltos
                        activitiesContainer.style.minHeight = `${activitiesContainer.offsetHeight}px`;

                        // Desplazamiento suave al contenedor
                        section.scrollIntoView({ behavior: "smooth" });

                        // Recargar la página después de un pequeño retraso
                        setTimeout(() => {
                            window.location.href = link.getAttribute("href");
    </script>
</section>
