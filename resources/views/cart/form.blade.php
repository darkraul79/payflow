@extends('components.filament-fabricator.layouts.default')

@section('static')
    <section>
        <header class="mb-12">
            <h1 class="title">{{ $page->title }}</h1>
        </header>
        <livewire:order-form-component />
    </section>
@endsection
