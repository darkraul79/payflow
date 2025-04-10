<section
    class="numbers grid grid-cols-2 items-center justify-between gap-3 text-center md:grid-cols-3 lg:grid-cols-4"
>
    @foreach ($attributes['items'] as $item)
        <x-home-number-item
            title="{{ $item['title'] }}"
            number="{{$item['number'] }}"
            icon="{{ $item['icon'] }}"
            color="{{ $item['color']??'#d1f6ff' }}"
        />
    @endforeach
</section>
