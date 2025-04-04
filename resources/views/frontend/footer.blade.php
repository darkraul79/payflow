<footer class="bg-azul-sky w-full pt-8 lg:px-[72px] lg:pt-14 lg:pb-10">
    <div class="@container container">
        <div
            class="flex w-full flex-col justify-between gap-5 gap-y-8 md:flex-row lg:gap-y-0"
        >
            <div class="contact lg:min-h-[250px]">
                <div class="space-y-2 font-semibold">
                    <h5 class="mb-5 inline-flex gap-2 font-bold">
                        <img
                            src="{{ asset('images/icons/heart-hand.svg') }}"
                            class="w-4"
                            alt="{{ config('app.name') }}"
                        />
                        {{ config('app.name') }}
                    </h5>
                    <p>Teléfono: 648 986 753</p>
                    <p>Horario: 14:30 a 19:30</p>
                    <p>
                        Email:
                        <a
                            href="mailto:ayuda@fundacionelenatertre.es"
                            target="_blank"
                            title="ayuda@fundacionelenatertre.es"
                        >
                            ayuda@fundacionelenatertre.es
                        </a>
                    </p>
                </div>
            </div>
            <div class="footer-nav">
                <h6>Acerca de Nosotros</h6>
                <nav>
                    <x-footer-link text="Quiénes somos" link="#" />
                    <x-footer-link text="Qué hacemos" link="#" />
                    <x-footer-link text="Colabora" link="#" />
                    <x-footer-link text="Transparencia" link="#" />
                </nav>
            </div>
            <div class="footer-nav">
                <h6>Enlaces de ayuda</h6>
                <nav>
                    <x-footer-link text="Noticias" link="#" />
                    <x-footer-link text="Aviso legal" link="#" />
                    <x-footer-link text="Protección de datos" link="#" />
                    <x-footer-link text="Contacto" link="#" />
                </nav>
            </div>
            <div class="footer-nav">
                <h6>Memorias de actividades</h6>
                <div class="actividades">
                    @for ($i = 1; $i <= 6; $i++)
                        <a href="#" class="">
                            <img src="https://picsum.photos/80" alt="#" />
                        </a>
                    @endfor
                </div>
            </div>
        </div>

        <div class="my-8 flex w-full items-end justify-between">
            <div class="flex gap-2 lg:gap-3">
                <x-rss-icon
                    title="Facebook"
                    link="#"
                    :icon="asset('images/icons/facebook.svg')"
                />
                <x-rss-icon
                    title="X"
                    link="#"
                    :icon="asset('images/icons/x.svg')"
                />
                <x-rss-icon
                    title="Instagram"
                    link="#"
                    :icon="asset('images/icons/instagram.svg')"
                />
                <x-rss-icon
                    title="Youtube"
                    link="#"
                    :icon="asset('images/icons/youtube.svg')"
                />
            </div>
            <div class="text-end text-xs">© {{ config('app.name') }}</div>
        </div>
    </div>
</footer>
