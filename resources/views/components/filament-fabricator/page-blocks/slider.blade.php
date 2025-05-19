@php
    function getAlignClasses($align): array
    {
        return match ($align) {
            'left' => [
                'block' => 'top-1/2 left-5 -translate-x-0 -translate-y-1/2 md:left-10 md:max-w-1/2 lg:left-24 lg:max-w-[450px]',
                'text' => 'text-left',
            ],
            'center' => [
                'block' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
                'text' => 'text-center',
            ],
            'right' => [
                'block' => 'top-1/2 right-5 -translate-x-0 -translate-y-1/2 md:right-10 md:max-w-1/2 lg:right-24 lg:max-w-[450px]',
                'text' => 'text-right',
            ],
            default => [
                'block' => 'top-1/2 left-5 -translate-x-0 -translate-y-1/2 md:left-10 md:max-w-1/2 lg:left-24 lg:max-w-[450px]',
                'text' => 'text-left',
            ],
        };
    }
@endphp

<section class="slider sliderHome full mb-4">
    <div
        id="indicators-carousel"
        class="relative overflow-hidden"
        data-carousel="static"
    >
        <!-- Carousel wrapper -->
        <div class="relative h-80 md:h-[496px]" data-carousel-inner>
            @foreach ($attributes['sliders'] as $index => $item)
                <div
                    class="hidden duration-700 ease-in-out"
                    data-carousel-item
                    id="carousel-item-{{ $index + 1 }}"
                >
                    <img
                        src="{{ asset('storage/' . $item['image'], true) }}"
                        class="h-full w-full object-cover"
                        alt="{{ $item['title'] }}"
                    />

                    <div
                        class="{{ getAlignClasses($item['align'])['block'] }} absolute max-w-2/3 transform text-white"
                    >
                        <h6
                            class="{{ getAlignClasses($item['align'])['text'] }} font-teacher mb-4 inline-block w-full text-4xl leading-8 font-bold text-pretty text-white md:leading-14 lg:text-6xl"
                        >
                            {{ $item['title'] }}
                        </h6>

                        <span
                            class="{{ getAlignClasses($item['align'])['text'] }} text-xs leading-5 md:text-base"
                        >
                            {!! $item['content'] !!}
                        </span>
                    </div>
                </div>
                <!-- Item {{ $index }} -->
            @endforeach
        </div>
        <!-- Slider indicators -->
        <div
            class="relative z-30 my-8 flex justify-center space-x-3 rtl:space-x-reverse"
        >
            @foreach ($attributes['sliders'] as $index => $item)
                <button
                    type="button"
                    class="border-azul-wave! bg-azul-sky! aria-current:bg-azul-wave! hover:bg-azul-wave/50! h-3 w-3 cursor-pointer rounded-full border"
                    aria-current="true"
                    aria-label="{{ $item['title'] }}"
                    data-carousel-slide-to="{{ $index }}"
                ></button>
            @endforeach
        </div>
    </div>
</section>
@vite(['resources/js/carousel.js'])
