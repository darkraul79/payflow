<section class="sponsorship">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
    />
    <div
        class="mt-8 mb-16 grid gap-5 md:grid-cols-2 md:gap-10 lg:grid-cols-3 lg:gap-12"
    >
        @foreach ($items as $item)
            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front card overflow-hidden">
                        <img
                            src="{{ asset('storage/' . $item['image'], true) }}"
                            alt="{{ $item['name'] }}"
                            class="mb-2 w-full rounded-lg object-cover object-center"
                        />
                        <h4 class="title2 text-black">{{ $item['name'] }}</h4>
                        <span class="position">{{ $item['position'] }}</span>
                    </div>
                    <div class="flip-card-back card">
                        <h4 class="title2">{{ $item['name'] }}</h4>
                        <span class="position mb-6 block">
                            {{ $item['position'] }}
                        </span>
                        {!! html_entity_decode($item['bio']) !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
