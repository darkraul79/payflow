<div
    class="bg-azul-wave font-teacher z-10 mb-2 flex w-full items-end overflow-hidden bg-cover bg-center p-5 max-md:pt-8 md:mx-auto md:mb-6 md:min-h-[200px] lg:p-[72px]"
    style="background-image: url({{ asset('images/bg-draw.webp') }})"
>
    <div
        class="flex w-full flex-col-reverse items-center justify-between bg-white md:flex-row"
    >
        <div class="p-6 text-[20px] font-semibold md:w-1/2">
            <h2 class="subtitle">Actividades</h2>
            <h1 class="title">{{ $post->title }}</h1>
            <p class="my-10">
                @if (@isset($post->address))
                    {{ $post->address }}

                    <br />
                @endif

                @if (@isset($post->address))
                    {{ $post->getFormatDate() }}
                    <br />
                @endif

                @if (@isset($post->address))
                    {{ $post->getFormatDateTime() }}
                @endif
            </p>
            <button
                class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 flex cursor-pointer flex-row items-center gap-2 px-6 py-4 font-semibold"
            >
                <flux:icon.heart variant="solid" class="size-4" />
                Haz una donación
            </button>
        </div>
        @if ($post->getMedia())
            <div class="flex justify-end md:w-1/2">
                <img
                    src="{{ $post->getMedia('principal')->first()->getUrl() }}"
                    alt="{{ $post->title }}"
                />
            </div>
        @endif
    </div>
</div>
