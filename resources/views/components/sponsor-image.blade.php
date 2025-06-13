@props([
    'url' => null,
    'sponsor' => null,
    'size' => 'small',
    'url' => null,
    'image' => null,
    'order' => null,
])
<div class="rounded-2xl bg-azul-sky block border-azul-sky border sponsor-image {{ $size }} {{ $image? '':'p-2' }}">
    @if($url)

        <a href="{{$url}}" title="{{$sponsor}}"
           class="w-full flex items-center justify-center ">
            @endif

            @if($image)
                <img src="{{ $image }}" alt="{{$sponsor}}" class="w-full rounded-xl order-{{$order}}" />
            @else
                <img src="{{asset('images/icons/heart-hand.svg')}}" class="w-full" alt="" />
            @endif

            @if($url)
        </a>
    @endif
</div>
