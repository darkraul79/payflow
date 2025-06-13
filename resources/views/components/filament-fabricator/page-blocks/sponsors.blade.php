<section
    class="patrocinio mx-auto flex flex-col items-center justify-center md:flex-row md:gap-20"
>
    <div class="w-full md:w-1/3">
        <x-basic
            :title="$attributes['title']"
            :subtitle="$attributes['subtitle']"
            :text="$attributes['description']"
        />

        <x-boton
            class="my-8"
            :buttonText="$attributes['button_text']"
            :buttonLink="$attributes['button_link']"
        />
    </div>
    <div
        class="my-6 block w-full max-w-[808px] flex-grow space-y-4 md:w-2/3 md:space-y-10"
    >
        @foreach ([
                ['start' => 1, 'end' => 4, 'size' => 'large'],
                ['start' => 5, 'end' => 9, 'size' => 'medium'],
                ['start' => 10, 'end' => 13, 'size' => null]
            ]
            as $group)
            <div class="flex flex-row items-center justify-between gap-x-5">
                @for ($i = $group['start']; $i <= $group['end']; $i++)
                    @php
                        $sponsor = $attributes['sponsors'][$i - 1] ?? null;
                    @endphp

                    @if ($sponsor)
                        <x-sponsor-image
                            :size="$group['size']"
                            :url="$sponsor->url ?? null"
                            :sponsor="$sponsor->name ?? null"
                            :image="$sponsor->getFirstMedia('sponsors')->getUrl('icon') ?? null"
                            :order="$sponsor->order"
                        />
                    @else
                        <x-sponsor-image :size="$group['size']" />
                    @endif
                @endfor
            </div>
        @endforeach
    </div>
</section>
