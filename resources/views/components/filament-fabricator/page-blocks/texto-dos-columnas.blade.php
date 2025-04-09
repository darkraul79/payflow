@aware(['page'])

<section class="mission flex flex-col items-center gap-4 lg:flex-row lg:gap-20">
    <div>
        <h2 class="subtitle">{{ $attributes['subtitle'] }}</h2>
        <h3 class="title">{{ $attributes['title'] }}</h3>
    </div>
    <div>
        {!! $attributes['text'] !!}
    </div>
</section>
