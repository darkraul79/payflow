import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

// Inicializar Splide
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.splide').forEach((element) => {
        const perPage = element.dataset.perPage || 2;
        new Splide('.splide', {
            type: 'slide',
            perPage: parseInt(perPage, 10),
            perMove: 1,
            gap: '1rem',
        }).mount();
    });
});
