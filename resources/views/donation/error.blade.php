@extends('components.filament-fabricator.layouts.default')

@section('static')
    <section
        class="mx-auto flex min-h-[450px] flex-col items-center justify-center"
    >
        <header class="mb-12 flex flex-row items-center gap-5 text-center">
            <div
                class="border-error  inline-block rounded-full border-8 bg-white ring-8"
            >
                <x-bi-exclamation-lg class="text-azul-marino h-6 w-6" />
            </div>
            <h1 class="title">{{ $page->title }}</h1>
        </header>
        <div class="mb-12 text-center">
            <h2 class="subtitle">No se ha podido completar tu donación.</h2>
            <p>
                Aún estás a tiempo de impulsar la ola solidaria. Vuelve a
                intentarlo o contáctanos si necesitas ayuda.
            </p>
        </div>
    </section>
@endsection
