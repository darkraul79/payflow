@php
    use App\Models\Page;
    $p = App\Models\Page::find($page->id);
@endphp

<div
    class="bg-azul-wave font-teacher text-azul-wave z-10 mb-2 flex w-full items-end overflow-hidden bg-cover bg-center px-5 text-center max-md:pt-8 md:mx-auto md:mb-6 md:min-h-[200px]"
    style="background-image: url({{ asset('images/bg-draw.webp') }})"
>
    @if ($p->blockquotes->count())
        <blockquote
            class="mx-auto flex w-fit items-center rounded-t-2xl bg-white px-8 py-4 text-center text-2xl leading-6 font-bold text-pretty shadow-[0px_0px_12px_0px_rgba(0,0,0,0.25)] md:text-[2.5rem] md:leading-10 lg:px-40 lg:py-7"
        >
            "{{ $p->blockquotes->first()->text }}"
        </blockquote>
    @endif
</div>
