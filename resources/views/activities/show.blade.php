@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @vite('resources/css/frontend.css')
@endpush

@section('main')
    @props([
        'page',
    ])

    <div class="flex flex-col md:flex-row donacion ">
        <div class="w-full md:w-4/6">
            <section>
                {!! $post->content !!}
            </section>
        </div>
        <div class="w-full md:w-2/6 flex justify-end items-start ">
            @livewire('donacion-banner')
        </div>
@endsection
