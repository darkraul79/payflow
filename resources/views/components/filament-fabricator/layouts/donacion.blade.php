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

    <div class="flex flex-col md:flex-row donacion ">
        <div class="w-full md:me-8 lg:me-10 xl:me-30">
            <x-filament-fabricator::page-blocks :blocks="$page?->blocks" />
        </div>
        <div class="w-full  flex justify-end items-start flex-1 ">
            <div class="md:sticky md:max-w-[400px] w-full top-0 right-0 min-w-[350px]">
                <livewire:donacion-banner prefix="banner" wire:key="banner" />

            </div>
        </div>
@endsection
