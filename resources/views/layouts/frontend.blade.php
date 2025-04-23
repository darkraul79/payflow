<!DOCTYPE html>
<!--suppress HtmlRequiredTitleElement -->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="{{ Route::currentRouteName() }} flex min-h-screen flex-col">
        @include('frontend.elements.header')
        <main
            class="@container {{ $page ? $page->slug : '' }} full-container bg-white"
        >
            @yield('main')
        </main>

        @include('frontend.footer')

        @fluxScripts
    </body>
</html>
