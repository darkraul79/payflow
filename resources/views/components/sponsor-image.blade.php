@props([
    'url' => null,
    'sponsor' => null,
    'size' => 'small',
    'url' => null,
    'image' => null,
])
<div class="rounded-2xl bg-azul-sky block p-2 sponsor-image {{ $size }}">
    @if($url)

        <a href="{{$url}}" title="{{$sponsor}}"
           class="w-full flex items-center justify-center ">
            @endif

            @if($image)
                <img src="{{ 'storage/' .$image }}" alt="{{$sponsor}}" class="w-full" />
            @else
                <img src="{{asset('images/icons/heart-hand.svg')}}" class="w-full" />
            @endif

            @if($url)
        </a>
    @endif
</div>
