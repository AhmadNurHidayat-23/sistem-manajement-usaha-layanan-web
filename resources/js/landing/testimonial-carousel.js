/**
 * Infinite Editorial Testimonial Carousel for SIMAUL Landing Page.
 * Smooth horizontal auto-scroll from right to left with hover pause,
 * manual controls, touch swipe support, and prefers-reduced-motion check.
 */

export function initTestimonialCarousel() {
    const track = document.getElementById('testimonial-track');
    const wrapper = document.getElementById('testimonial-wrapper');
    const prevBtn = document.getElementById('testimonial-prev-btn');
    const nextBtn = document.getElementById('testimonial-next-btn');

    if (!track || !wrapper) return;
    if (track.dataset.carouselBound === '1') return;
    track.dataset.carouselBound = '1';

    const items = track.querySelectorAll('.testimonial-item');
    if (items.length <= 1) return;

    let animationFrameId = null;
    let isPaused = false;
    let isInteracting = false;
    let interactionTimeout = null;
    let lastTimestamp = null;
    let currentScroll = track.scrollLeft || 0;

    // Deteksi prefers-reduced-motion
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let prefersReducedMotion = mediaQuery.matches;
    mediaQuery.addEventListener?.('change', (e) => {
        prefersReducedMotion = e.matches;
    });

    // Menghitung lebar siklus satu set ulasan asli
    function getLoopThreshold() {
        const firstClone = track.querySelector('.testimonial-item[aria-hidden="true"]');
        if (firstClone && track.firstElementChild) {
            const distance = firstClone.offsetLeft - track.firstElementChild.offsetLeft;
            if (distance > 0) return distance;
        }
        return track.scrollWidth / 2;
    }

    // Kecepatan disesuaikan agar 1 siklus memakan waktu sekitar 35–45 detik (target ~40s @ 60fps)
    function getCycleSpeed() {
        const threshold = getLoopThreshold();
        if (threshold <= 0) return 0.6;
        // 40 detik * 60 frame = 2400 frames per siklus
        const calculated = threshold / 2400;
        return Math.max(0.4, Math.min(1.0, calculated));
    }

    let speed = getCycleSpeed();

    window.addEventListener(
        'resize',
        () => {
            speed = getCycleSpeed();
        },
        { passive: true }
    );

    // Animasi Continuous Auto-Scroll (kanan ke kiri)
    function step(timestamp) {
        if (!lastTimestamp) lastTimestamp = timestamp;
        const dt = Math.min(32, timestamp - lastTimestamp);
        lastTimestamp = timestamp;

        if (!isPaused && !isInteracting && !prefersReducedMotion) {
            const stepIncrement = speed * (dt / 16.67);
            currentScroll += stepIncrement;

            const threshold = getLoopThreshold();
            if (threshold > 0 && currentScroll >= threshold) {
                currentScroll -= threshold;
            }

            track.scrollLeft = currentScroll;
        }

        animationFrameId = requestAnimationFrame(step);
    }

    // Mulai animasi
    animationFrameId = requestAnimationFrame(step);

    // Intersection Observer: jeda saat keluar layar untuk hemat resource
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        isPaused = false;
                        lastTimestamp = null;
                    } else {
                        isPaused = true;
                    }
                });
            },
            { threshold: 0.05 }
        );
        observer.observe(wrapper);
    }

    // Jeda saat kursor mouse berada di atas ulasan (Hover Pause)
    wrapper.addEventListener('mouseenter', () => {
        isPaused = true;
    });

    wrapper.addEventListener('mouseleave', () => {
        isPaused = false;
        currentScroll = track.scrollLeft;
        lastTimestamp = null;
    });

    // Touch & Pointer interaction handling untuk mobile swipe
    function startInteraction() {
        isInteracting = true;
        if (interactionTimeout) clearTimeout(interactionTimeout);
    }

    function endInteraction() {
        if (interactionTimeout) clearTimeout(interactionTimeout);
        interactionTimeout = setTimeout(() => {
            const threshold = getLoopThreshold();
            if (threshold > 0) {
                while (track.scrollLeft >= threshold) {
                    track.scrollLeft -= threshold;
                }
                while (track.scrollLeft < 0) {
                    track.scrollLeft += threshold;
                }
            }
            currentScroll = track.scrollLeft;
            lastTimestamp = null;
            isInteracting = false;
        }, 1200);
    }

    track.addEventListener('touchstart', startInteraction, { passive: true });
    track.addEventListener('touchend', endInteraction, { passive: true });
    track.addEventListener('touchcancel', endInteraction, { passive: true });
    track.addEventListener('pointerdown', startInteraction, { passive: true });
    track.addEventListener('pointerup', endInteraction, { passive: true });

    // Navigasi Manual via Tombol Prev / Next
    function getScrollStep() {
        const firstItem = track.querySelector('.testimonial-item');
        if (!firstItem) return 360;
        const gap = parseFloat(window.getComputedStyle(track).gap) || 48;
        return firstItem.offsetWidth + gap;
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            startInteraction();
            const scrollStep = getScrollStep();
            const threshold = getLoopThreshold();
            if (track.scrollLeft - scrollStep < 0 && threshold > 0) {
                track.scrollLeft += threshold;
            }
            track.scrollBy({ left: -scrollStep, behavior: 'smooth' });
            endInteraction();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            startInteraction();
            const scrollStep = getScrollStep();
            const threshold = getLoopThreshold();
            if (threshold > 0 && track.scrollLeft + scrollStep >= threshold * 2) {
                track.scrollLeft -= threshold;
            }
            track.scrollBy({ left: scrollStep, behavior: 'smooth' });
            endInteraction();
        });
    }
}

if (typeof window !== 'undefined') {
    window.initTestimonialCarousel = initTestimonialCarousel;
}
