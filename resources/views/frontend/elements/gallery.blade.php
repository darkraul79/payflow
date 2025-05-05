@if ($post->getMedia('gallery')->count())
    <div
        class="my-10 flex flex-row items-center justify-start gap-4"
        id="gallery"
    >
        @foreach ($post->getMedia('gallery') as $media)
            <a
                href="{{ $media->getUrl() }}"
                data-fslightbox="gallery"
                class="card p-1"
            >
                <img
                    src="{{ $media->getUrl('thumb') }}"
                    alt="{{ $post->title }}"
                    class="w-full max-w-[150px] rounded-sm object-cover"
                />
            </a>
        @endforeach ($post->getMedia())

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lightbox = new SimpleLightbox('#gallery a', {
                    captions: false,
                    closeText: 'x',
                    scrollZoom: true,
                    preloading: true,
                });
            });
        </script>
    </div>
@endif
