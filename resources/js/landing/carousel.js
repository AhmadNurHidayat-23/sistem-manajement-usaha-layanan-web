/**
 * Controller Carousel Multi-Image Kartu Layanan Landing Page SIMAUL.
 * Setiap kartu layanan memiliki state slide independen dan mendukung loop 1-2-3-1 & 1<-3.
 */

window.serviceCarouselStates = window.serviceCarouselStates || {};

export function updateServiceCarousel(serviceId, nextIndex) {
    const container = document.querySelector(`.service-carousel[data-carousel-id="${serviceId}"]`);
    if (!container) return;

    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.carousel-dot');
    const total = slides.length;
    if (total <= 1) return;

    // Loop wrap-around: 1 -> 2 -> 3 -> 1 dan 1 <- 3
    let index = (nextIndex + total) % total;
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

if (typeof window !== 'undefined') {
    window.nextServiceSlide = nextServiceSlide;
    window.prevServiceSlide = prevServiceSlide;
    window.goToServiceSlide = goToServiceSlide;
}
