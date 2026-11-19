/**
 * Nike-inspired Service Catalog Carousel & Service Multi-Image Controller for SIMAUL Landing Page.
 */

window.serviceCarouselStates = window.serviceCarouselStates || {};

export function updateServiceCarousel(serviceId, nextIndex) {
    const container = document.querySelector(`.service-carousel[data-carousel-id="${serviceId}"]`);
    if (!container) return;

    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.carousel-dot');
    const total = slides.length;
    if (total <= 1) return;

    const index = (nextIndex + total) % total;
    window.serviceCarouselStates[serviceId] = index;

    slides.forEach((slide, idx) => {
        if (idx === index) {
            slide.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
            slide.classList.add('opacity-100', 'z-1', 'pointer-events-auto');
        } else {
            slide.classList.remove('opacity-100', 'z-1', 'pointer-events-auto');
            slide.classList.add('opacity-0', 'z-0', 'pointer-events-none');
        }
    });

    dots.forEach((dot, idx) => {
        if (idx === index) {
            dot.classList.remove('bg-white/50');
            dot.classList.add('bg-white', 'w-3');
        } else {
            dot.classList.remove('bg-white', 'w-3');
            dot.classList.add('bg-white/50');
        }
    });
}

export function nextServiceSlide(serviceId) {
    const current = window.serviceCarouselStates[serviceId] || 0;
    updateServiceCarousel(serviceId, current + 1);
}

export function prevServiceSlide(serviceId) {
    const current = window.serviceCarouselStates[serviceId] || 0;
    updateServiceCarousel(serviceId, current - 1);
}

export function goToServiceSlide(serviceId, index) {
    updateServiceCarousel(serviceId, index);
}

export function initNikeCarousels() {
    const sections = document.querySelectorAll('.nike-category-section');
    if (!sections.length) return;

    sections.forEach((section) => {
        const track = section.querySelector('.nike-carousel-track-wrapper');
        const prevBtn = section.querySelector('.nike-carousel-prev');
        const nextBtn = section.querySelector('.nike-carousel-next');

        if (!track || !prevBtn || !nextBtn) return;

        function updateButtons() {
            const tolerance = 8;
            const maxScroll = track.scrollWidth - track.clientWidth;
            prevBtn.disabled = track.scrollLeft <= tolerance;
            nextBtn.disabled = track.scrollLeft >= maxScroll - tolerance;
        }

        prevBtn.addEventListener('click', () => {
            const scrollDistance = track.clientWidth * 0.95;
            track.scrollBy({ left: -scrollDistance, behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            const scrollDistance = track.clientWidth * 0.95;
            track.scrollBy({ left: scrollDistance, behavior: 'smooth' });
        });

        track.addEventListener('scroll', updateButtons, { passive: true });
        window.addEventListener('resize', updateButtons, { passive: true });

        updateButtons();
    });
}

if (typeof window !== 'undefined') {
    window.initNikeCarousels = initNikeCarousels;
    window.updateServiceCarousel = updateServiceCarousel;
    window.nextServiceSlide = nextServiceSlide;
    window.prevServiceSlide = prevServiceSlide;
    window.goToServiceSlide = goToServiceSlide;
}
