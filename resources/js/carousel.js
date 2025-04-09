const items = [];
// options with default values
const options = {
    defaultPosition: 1,
    interval: 3000,

    indicators: {
        activeClasses: 'bg-red-500',
        inactiveClasses: 'bg-azul-wave',
    },
};

// instance options object
const instanceOptions = {
    id: 'indicators-carousel',
    override: true,
    indicators: {
        activeClasses: 'bg-red-500',
        inactiveClasses: 'bg-azul-wave',
    },
};

import { Carousel } from 'flowbite'; // initialize components based on data attribute selectors

let carouselEl = document.getElementById('indicators-carousel');
const carousel = new Carousel(carouselEl, items, options, instanceOptions);
window.carousel = carousel;
