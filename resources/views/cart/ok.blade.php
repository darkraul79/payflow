@extends('components.filament-fabricator.layouts.default')

@section('static')
    <section
        class="mx-auto flex min-h-[450px] flex-col items-center justify-center"
    >
        <header class="mb-12 flex flex-row items-center gap-5 text-center">
            <div
                class="border-success bg-success-50 inline-block rounded-full border-8 bg-white ring-8"
            >
                <x-bi-check-lg class="text-azul-marino h-6 w-6" />
            </div>
            <h1 class="title">{{ $page->title }}</h1>
        </header>
        <div class="mb-12 text-center">
            <h2 class="subtitle">¡Gracias por tu compra!</h2>
            <p>Juntos hacemos que la ola solidaria no deje de avanzar.</p>
        </div>
    </section>
@endsection
