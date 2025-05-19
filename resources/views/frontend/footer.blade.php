@php
    use App\Models\Activity;
    use App\Models\Post;
    use App\Models\Setting;
@endphp

<footer
    class="@container bg-azul-sky full-container flex pt-8 pb-4 shadow-lg lg:px-[72px] lg:pt-14 lg:pb-0 lg:pb-10"
>
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
                    @if (setting('contact.telefono'))
                        <p>Teléfono: {{ setting('contact.telefono') }}</p>
                    @endif

                    @if (setting('contact.horario'))
                        <p>Horario: {{ setting('contact.horario') }}</p>
                    @endif

                    @if (setting('contact.email'))
                        <p>
                            Email:
                            <a
                                href="mailto:{{ setting('contact.email') }}"
                                target="_blank"
                                title="{{ setting('contact.email') }}"
                            >
                                {{ setting('contact.email') }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            <livewire:nav-menu type="footer" location="footer1" />
            <livewire:nav-menu type="footer" location="footer2" />
            <div class="footer-nav">
                <h6>Memorias de actividades</h6>
                <div class="actividades">
                    @foreach (Activity::getFooterActivities() as $activity)
                        @if ($activity?->getFirstMedia('principal'))
                            <a
                                href="{{ $activity->getLink() }}"
                                title="{{ $activity->title }}"
                                class="h-[80px] w-[80px] overflow-hidden"
                            >
                                <img
                                    src="{{ $activity->getFirstMedia('principal')->getUrl('card-thumb') }}"
                                    alt="{{ $activity->title }}"
                                />
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 flex w-full items-end justify-between">
            @if (setting('rss'))
                <div class="flex gap-2 lg:gap-3">
                    @foreach (setting('rss') as $name => $url)
                        <x-rss-icon
                            title="{{ $name }}"
                            link="{{ $url }}"
                            :icon="asset('images/icons/' . $name.'.svg')"
                        />
                    @endforeach
                </div>
            @endif

            <div class="text-end text-xs">© {{ config('app.name') }}</div>
        </div>
    </div>
</footer>
