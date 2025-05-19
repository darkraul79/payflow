@extends('layouts.frontend')

<!--suppress JSUnresolvedReference, JSUnusedLocalSymbols -->
@push('css')
    @vite(['resources/css/frontend.css', 'resources/css/lightbox.css'])
@endpush

@section('main')
    @props([
        'page',
    ])

    <section
        class="product font-poppins my flex flex-col lg:my-[74px] lg:flex-row lg:gap-8"
    >
        <div class="flex-1/2">
            @include('products.slider')

            <hr class="border- my-6 w-full border-gray-200 lg:hidden" />
        </div>
        <div class="w-full flex-1/2 pt-1">
            @include('products.product-info')
        </div>
    </section>
@endsection

@push('scripts')
    @vite(['resources/js/sliderProductGallery.js'])

    <script src="{{ asset('js/simple-lightbox.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lightbox = new SimpleLightbox('#productGallery a', {
                captions: false,
                closeText: 'x',
                scrollZoom: true,
                preloading: true,
            });
        });
    </script>
@endpush
