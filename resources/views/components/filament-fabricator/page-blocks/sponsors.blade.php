<section
    class="patrocinio mx-auto flex flex-col items-center justify-center md:flex-row md:gap-20"
>
    <div class="w-full md:w-1/3">
        <h2 class="subtitle">{{ $attributes['subtitle'] }}</h2>
        <h3 class="title">{{ $attributes['title'] }}</h3>

        <x-boton
            class="my-8"
            :buttonText="$attributes['button_text']"
            :buttonLink="$attributes['button_link']"
        />
    </div>
    <div
        class="my-6 block w-full max-w-[808px] flex-grow space-y-4 md:w-2/3 md:space-y-10"
    >
        <div class="flex flex-row items-center justify-between gap-x-5">
            <x-sponsor-image size="large" />
            <x-sponsor-image size="large" />
            <x-sponsor-image size="large" />
            <x-sponsor-image size="large" />
        </div>
        <div class="flex flex-row items-center justify-between gap-x-5">
            <x-sponsor-image size="medium" />
            <x-sponsor-image size="medium" />
            <x-sponsor-image size="medium" />
            <x-sponsor-image size="medium" />
            <x-sponsor-image size="medium" />
        </div>
        <div class="flex flex-row items-center justify-between gap-x-5">
            <x-sponsor-image />
            <x-sponsor-image />
            <x-sponsor-image />
            <x-sponsor-image />
        </div>
    </div>
</section>
