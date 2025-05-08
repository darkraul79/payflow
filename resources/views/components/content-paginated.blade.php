<div>
    <div
        class="my-12 grid grid-cols-1 items-stretch justify-center space-y-8 md:grid-cols-2 md:gap-6 md:space-y-0 lg:grid-cols-3 lg:gap-8"
    >
        @foreach ($data as $activity)
            <x-card
                :image="$activity->getFirstMedia('principal')?->getUrl('card-thumb')"
                :title="$activity->title"
                :text="$activity->resume"
                :date="$activity->getFormatDateBlog()"
                button-text="Leer más"
                :button-link="$activity->getUrl()"
            />
        @endforeach
    </div>

    <div class="w-full text-center">
        {{ $data->links(data: ['scrollTo' => '#activities-section']) }}
    </div>
</div>
