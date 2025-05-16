<section
    class="items flex flex-col items-stretch justify-between gap-y-12 lg:flex-row lg:gap-y-0"
>
    @foreach ($attributes['items'] as $item)
        <x-home-item
            title="{{ $item['title'] }}"
            description="{!!  $item['description']  !!}"
            link="{{ $item['button_link'] }}"
            link-text="{{ $item['button_text'] }}"
            icon="{{ $item['icon'] }}"
        />
    @endforeach
</section>
