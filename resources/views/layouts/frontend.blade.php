<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @yield('vite')
        @stack('css')
        @fluxAppearance
    </head>
    <body
        class="{{ Route::currentRouteName() }} flex min-h-screen flex-col bg-white"
    >
        @include('frontend.elements.header')

        <main class="@container container">
            @yield('main')
        </main>

        @include('frontend.footer')

        @fluxScripts
    </body>
</html>
