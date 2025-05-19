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

    <div class="flex flex-col md:flex-row donacion {{ $page?->donacion ? 'md:gap-x-10':'' }}">
        <div class="w-full {{ $page?->donacion ? 'md:w-4/6':'' }}">
            <section>

                {!! $page?->content !!}

                @include('frontend.elements.gallery')

            </section>
        </div>

    @include('frontend.elements.sideBarDonacion')

@endsection
