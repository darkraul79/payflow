@if ($page->getMedia('gallery')->count())
    <!--suppress JSUnresolvedReference, JSUnusedLocalSymbols -->
    <div
        class="my-10 flex flex-row flex-wrap items-center justify-start gap-4"
        id="gallery"
    >
        @foreach ($page->getMedia('gallery') as $media)
            <a
                href="{{ $media->getUrl() }}"
                data-fslightbox="gallery"
                class="card p-1"
            >
                <img
                    src="{{ $media->getUrl('thumb') }}"
                    alt="{{ $page->title }}"
                    class="w-full max-w-[150px] rounded-sm object-cover"
                />
            </a>
        @endforeach ($page->getMedia())

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lightbox = new SimpleLightbox('#gallery a', {
                    captions: false,
                    closeText: 'x',
                    scrollZoom: true,
                    preloading: true,
                    showCounter: false,
                });
            });
        </script>
    </div>
@endif
