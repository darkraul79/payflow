@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @vite('resources/css/home.css')
@endpush

@section('main')
    @foreach ($page->blocks as $block)
        @switch($block['type'])
            @case('slider')
                <x-filament-fabricator.page-blocks.slider
                    :sliders="$block['data']['sliders']"
                />

                @break
            @case('texto-dos-columnas')
                <x-filament-fabricator.page-blocks.texto-dos-columnas
                    :title="$block['data']['title']"
                    :subtitle="$block['data']['subtitle']"
                    text="{!! $block['data']['text'] !!}"
                />

                @break
            @case('items')
                <x-filament-fabricator.page-blocks.items
                    :items="$block['data']['items']"
                />

                @break
        @endswitch
    @endforeach

    @include('frontend.home.numbers')

    @include('frontend.home.banner')

    @include('frontend.home.activities')

    @include('frontend.home.sponsors')
@endsection
