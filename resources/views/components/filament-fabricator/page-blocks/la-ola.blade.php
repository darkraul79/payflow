<section class="ola">
    <div
        class="flex flex-col items-stretch gap-10 md:flex-row md:justify-between"
    >
        <div class="flex min-w-2xs flex-col">
            <h2 class="subtitle block">{{ $attributes['subtitle'] }}</h2>
            <h3 class="title mb-8 block">{{ $attributes['title'] }}</h3>
            <div
                class="card bg-azul-sky flex w-full items-center p-10 shadow-sm"
            >
                <img
                    src="{{ asset($attributes['image']) }}"
                    class="mx-auto"
                    alt="La ola"
                />
            </div>
        </div>
        <div class="flex flex-col gap-10 lg:flex-row">
            <div class="card bg-azul-swan h-full w-full lg:w-1/2">
                @foreach ($attributes['items'] as $item)
                    <x-item-ola :item="$item" />
                @endforeach
            </div>
            <div class="card bg-azul-swan h-full w-full lg:w-1/2">
                @foreach ($attributes['items2'] as $item)
                    <x-item-ola :item="$item" />
                @endforeach
            </div>
        </div>
    </div>
</section>
