@props([
    /**@var\mixed*/'item',
])

<h4 class="mb-4 flex items-center gap-2">
    <span
        class="bg-azul-wave inline-flex h-[38px] w-[38px] items-center rounded-full p-1.5"
    >
        <img
            src="{{ asset('storage/' . $item['icon']) }}"
            alt=""
            class="mx-auto w-[24px]"
        />
    </span>
    {{ $item['title'] }}
</h4>
{!! html_entity_decode($item['text']) !!}
