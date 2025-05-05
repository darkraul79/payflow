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
        <div class="w-full {{ $post->donacion ? 'md:w-4/6':'' }}">
            <section>

                {!! $post->content !!}

                @include('frontend.elements.gallery')

            </section>
        </div>

    @include('frontend.elements.sideBarDonacion')

@endsection
