<!DOCTYPE html>
<!--suppress HtmlRequiredTitleElement -->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @stack('vite')
        @stack('css')
        @stack('scripts')

        @fluxAppearance
    </head>
    <body
        class="{{ Route::currentRouteName() }} flex min-h-screen flex-col bg-white"
    >
        @include('frontend.elements.header')
        <main class="@container {{ $page->slug }} full-container">
            @yield('main')
        </main>

        @include('frontend.footer')

        @fluxScripts
    </body>
</html>
