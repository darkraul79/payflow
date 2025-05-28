import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

// document.addEventListener('DOMContentLoaded', () => {
var splide = new Splide('#productGallery', {
    // width: 530,
    pagination: false,
    arrows: false,
});

var thumbnails = document.getElementsByClassName('thumbnail');
var current;

for (var i = 0; i < thumbnails.length; i++) {
    initThumbnail(thumbnails[i], i);
}

function initThumbnail(thumbnail, index) {
    thumbnail.addEventListener('click', function () {
        splide.go(index);
    });
}

splide.on('mounted move', function () {
    var thumbnail = thumbnails[splide.index];

    if (thumbnail) {
        if (current) {
            current.classList.remove('is-active');
        }

        thumbnail.classList.add('is-active');
        current = thumbnail;
    }
});

splide.mount();
