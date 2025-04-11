<section>
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        subtitle-class="mb-1"
        title-class="mb-4"
        text-class="mt-4 gap-6 leading-5 md:columns-2"
    />
    <div class="flex flex-col">
        @foreach ($items as $item)
            <div
                class="border-b-azul-cobalt mb-4 flex items-center justify-between gap-10 border-b py-5"
            >
                <div class="basis-2/3">
                    <h4 class="mb-3">
                        {{ $item['title'] }}
                    </h4>

                    {!! html_entity_decode($item['content']) !!}
                </div>
                <div class="flex basis-1/3 justify-end">
                    <x-boton
                        :buttonLink="$item['file']"
                        buttonText="Descargar"
                        :is_download="true"
                    />
                </div>
            </div>
        @endforeach
    </div>
</section>
