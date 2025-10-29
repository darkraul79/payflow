<div>
    @if ($type === 'Product')
        <div
            class="mb-4 flex w-full items-center justify-end gap-2 text-sm text-gray-600"
        >
            <label for="sortBy" class="">Ordenar por:</label>
            <x-heroicon-c-bars-3-bottom-left class="h-5 w-5" />
            <select
                wire:model.live="sortBy"
                class="rounded border-gray-300 text-xs"
                id="sortBy"
            >
                <option value="created_at,desc">Novedades</option>
                <option value="price,asc">Precio: Menor a mayor</option>
                <option value="price,desc">Precio: Mayor a menor</option>
                <option value="name,asc">Nombre</option>
            </select>
        </div>
    @endif

    <div
        class="{{ $gridClass }} my-12 grid grid-cols-1 items-stretch justify-center space-y-8"
    >
        @foreach ($data as $activity)
            @if ($type === 'Product')
                <x-card-product :product="$activity" />
            @else
                <x-card
                    :image="$activity->getFirstMedia('principal')?->getUrl('card-thumb')"
                    :title="$activity->title"
                    :text="$activity->resume"
                    :date="$activity->getFormatDateBlog()"
                    button-text="Leer más"
                    :button-link="$activity->getLink()"
                />
            @endif
        @endforeach
    </div>

    <div class="w-full text-center">
        {{ $data->links(data: ['scrollTo' => '#activities-section']) }}
    </div>
</div>
