<div class="product-gallery">
    <div
        id="productGallery"
        class="splide splideFoto"
        aria-label="My Awesome Gallery"
    >
        <div class="splide__track">
            <ul class="splide__list">
                @foreach ($page->getMedia('product_images') as $media)
                    <li class="splide__slide">
                        <a
                            href="{{ $media->getUrl() }}"
                            data-fslightbox="gallery"
                        >
                            {{ $media->img()->conversion('gallery') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <ul id="thumbnails" class="thumbnails">
        @foreach ($page->getMedia('product_images') as $media)
            <li class="thumbnail {{ $loop->first ? ' is-active ' : '' }}">
                {{ $media->img()->conversion('thumb') }}
            </li>
        @endforeach
    </ul>
</div>
