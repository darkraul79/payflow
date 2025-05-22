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

    @if (isset($static))
        @yield('static')
    @else
        <x-filament-fabricator::page-blocks :blocks="$page?->blocks" />
    @endif
@endsection
