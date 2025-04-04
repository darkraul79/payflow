<div
    id="indicators-carousel"
    class="relative overflow-hidden"
    data-carousel="static"
>
    <!-- Carousel wrapper -->
    <div class="relative h-80 md:h-96" data-carousel-inner>
        <!-- Item 1 -->
        <div class="hidden duration-700 ease-in-out" data-carousel-item>
            <img
                src="https://flowbite.com/docs/images/carousel/carousel-1.svg"
                class="h-full w-full object-cover"
                alt="Slide 1"
            />
            <span
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transform text-xl font-semibold text-white md:text-2xl dark:text-gray-800"
            >
                First Slide
            </span>
        </div>
        <!-- Item 2 -->
        <div class="hidden duration-700 ease-in-out" data-carousel-item>
            <img
                src="https://flowbite.com/docs/images/carousel/carousel-2.svg"
                class="h-full w-full object-cover"
                alt="Slide 2"
            />
        </div>
        <!-- Item 3 -->
        <div class="hidden duration-700 ease-in-out" data-carousel-item>
            <img
                src="https://flowbite.com/docs/images/carousel/carousel-3.svg"
                class="h-full w-full object-cover"
                alt="Slide 3"
            />
        </div>
    </div>
    <!-- Slider indicators -->
    <div
        class="relative z-30 my-2 flex justify-center space-x-3 rtl:space-x-reverse"
    >
        <button
            type="button"
            class="border-azul-wave bg-azul-sky h-3 w-3 rounded-full border"
            aria-current="true"
            aria-label="Slide 1"
            data-carousel-slide-to="0"
        ></button>
        <button
            type="button"
            class="border-azul-wave bg-azul-sky h-3 w-3 rounded-full border"
            aria-current="false"
            aria-label="Slide 2"
            data-carousel-slide-to="1"
        ></button>
        <button
            type="button"
            class="border-azul-wave bg-azul-sky h-3 w-3 rounded-full border"
            aria-current="false"
            aria-label="Slide 3"
            data-carousel-slide-to="2"
        ></button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script type="module">
  import { Carousel } from "flowbite";

  const carousel = new Carousel(
        carouselElement,
        items,
        options,
        instanceOptions,
    );
</script>
