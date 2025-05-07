import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

// Función para inicializar Splide
const initializeSplide = (element) => {
    const perPage = window.innerWidth < 700 ? 1 : element.dataset.perPage || 2;
    return new Splide(element, {
        type: 'slide',
        perPage: parseInt(perPage, 10),
        perMove: 1,
        gap: '1rem',
        pagination: false,
    }).mount();
};

// Inicializar Splide y manejar redimensionamiento
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.splide').forEach((element) => {
        let splideInstance = initializeSplide(element);

        window.addEventListener('resize', () => {
            splideInstance.destroy(); // Destruir la instancia actual
            splideInstance = initializeSplide(element); // Crear una nueva instancia
        });
    });
});
