@extends('layouts.frontend')

@section('vite')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
    @vite('resources/css/home.css')
@endpush

@section('main')
    <x-filament-fabricator.layouts.default :page="$page" />
@endsection
