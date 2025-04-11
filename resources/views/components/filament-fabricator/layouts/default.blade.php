@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @if ($page->is_home)
        @vite('resources/css/home.css')
    @elseif ($page)
        @vite('resources/css/frontend.css')
    @endif
@endpush

@section('main')
    @props([
        'page',
    ])

    <x-filament-fabricator::page-blocks :blocks="$page->blocks" />
@endsection
