@extends('layouts.frontend')


@push('scripts')
    <script src="{{asset('js/simple-lightbox.min.js')}}"></script>
@endpush


@push('css')
    @vite(['resources/css/frontend.css','resources/css/lightbox.css'])
@endpush

@section('main')
    @props([
        'page',
    ])

    <div class="flex flex-col md:flex-row donacion ">
        <div class="w-full md:w-4/6">
            <section>

                {!! $post->content !!}

                <div class="flex flex-row items-center justify-start gap-4 my-10" id="gallery">
                    @foreach($post->getMedia('gallery') as $media)
                        <a
                            href="{{ $media->getUrl() }}"
                            data-fslightbox="gallery"
                            class="card p-1 ">
                            <img
                                src="{{ $media->getUrl('thumb') }}"
                                alt="{{ $post->title }}"
                                class="w-full max-w-[150px] object-cover rounded-sm"
                            />
                        </a>

                    @endforeach ($post->getMedia())
                </div>

            </section>
        </div>
        <div class="w-full md:w-2/6 flex justify-end items-start ">
            @livewire('donacion-banner')
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const lightbox = new SimpleLightbox("#gallery a", {
                    captions: false,
                    closeText: "x",
                    scrollZoom: true,
                    preloading: true
                });
            });
        </script>
@endsection
