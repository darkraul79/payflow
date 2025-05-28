@extends('components.filament-fabricator.layouts.default')

@section('static')
    <section>
        <header class="mb-12 text-center">
            <h1 class="title">{{ $page->title }}</h1>
        </header>
        <div class="mb-12 h-40 text-center">
            <h2 class="subtitle">Ha ocurrido un error al procesar el pedido</h2>
            <p>Por favor ponte en contacto con nosotros.</p>
        </div>
    </section>
@endsection
