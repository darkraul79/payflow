@php
    function getAlignClasses($align): array
    {
        return match ($align) {
            'left' => [
                'block' => 'top-1/2 left-10 -translate-y-1/2',
                'text' => 'text-left',
            ],
            'center' => [
                'block' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
                'text' => 'text-center',
            ],
            'right' => [
                'block' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
                'text' => 'text-right',
            ],
            default => [
                'block' => 'top-1/2 left-24 -translate-x-0 -translate-y-1/2',
                'text' => 'text-left',
            ],
        };
    }
@endphp

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
                    src="{{ asset($item['image']) }}"
                    class="h-full w-full object-cover"
                    alt="{{ $item['title'] }}"
                />

                <div
                    class="{{ getAlignClasses([$item['align']])['block'] }} absolute max-w-[539px] transform text-white"
                >
                    <h6
                        class="{{ getAlignClasses([$item['align']])['text'] }} font-teacher mb-4 inline-block text-4xl leading-8 font-bold text-pretty text-white md:text-lg md:leading-14 lg:text-6xl"
                    >
                        {{ $item['title'] }}
                    </h6>

                    <span
                        class="{{ getAlignClasses([$item['align']])['text'] }} text-xs leading-5 md:text-base"
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

@vite(['resources/js/carousel.js'])
