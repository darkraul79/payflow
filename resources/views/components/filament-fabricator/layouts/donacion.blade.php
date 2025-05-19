@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @if ($page?->is_home_page)
        @vite('resources/css/home.css')
    @elseif ($page)
        @vite('resources/css/frontend.css')
    @endif
@endpush

@section('main')
    @props([
        'page',
    ])

    <div class="flex flex-col md:flex-row donacion md:gap-x-10">
        <div class="w-full md:w-4/6">
            <x-filament-fabricator::page-blocks :blocks="$page?->blocks" />
        </div>
        <div class="w-full md:w-2/6 flex justify-end items-start ">
            @livewire('donacion-banner')
        </div>
@endsection
