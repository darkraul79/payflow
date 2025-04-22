<div
    {{ $attributes->merge(['class' => 'item-number w-full max-w-[300px] md:max-w-[270px] rounded-xl px-4 lg:px-[60px] py-6 flex flex-col items-stretch space-y-3 mx-auto']) }}
    style="background-color: {{ $color }}"
>
    <img
        src="{{ asset('storage/' . $icon) }}"
        class="icon mx-auto"
        alt="{{ $title }}"
    />

    <strong
        class="number-animation text-4xl font-semibold"
        data-number="{{ $number }}"
    >
        0
    </strong>

    <h5 class="leading-4 text-balance">{{ $title }}</h5>
</div>
