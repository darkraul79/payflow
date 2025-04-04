@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @vite('resources/css/home.css')
@endpush

@section('main')
    <div class="w-full">
        @include('frontend.elements.carrousel')
    </div>

    @include('frontend.home.intro')

    @include('frontend.home.items')

    @include('frontend.home.numbers')

    @include('frontend.home.banner')

    @include('frontend.home.activities')

    @include('frontend.home.sponsors')
@endsection
