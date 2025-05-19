<div>
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
