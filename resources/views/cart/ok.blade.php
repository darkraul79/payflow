@extends('components.filament-fabricator.layouts.default')

@section('static')
    <section>
        <header class="mb-12 text-center">
            <h1 class="title">{{ $page->title }}</h1>
        </header>
        <div class="mb-12 h-screen text-center">
            <h2 class="subtitle">Gracias por realizar tu pedido</h2>
            <p>
                Your order has been successfully placed. We will process it
                shortly.
            </p>
        </div>
    </section>
@endsection
