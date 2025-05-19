@props([
    'types',
    'categories',
    'tags',
])

<div {{ $attributes }}>
    @foreach ($types as $index => $type)
        @if (collect($$index)->isNotEmpty())
            <div class="my-6 flex flex-row gap-2">
                <strong class="font-[400]">{{ Str::title($type) }}:</strong>
                <div class="">
                    @foreach ($$index as $item)
                        {{-- <a href="{{ route('products.category', $item->slug) }}"> --}}
                        <span class="text-azul-gray hover:text-azul-sea">
                            {{ $item->name }}
                        </span>
                        <!--                </a>-->
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
