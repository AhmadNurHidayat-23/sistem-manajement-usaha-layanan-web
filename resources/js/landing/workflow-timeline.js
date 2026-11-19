/**
 * Workflow Animated Horizontal / Vertical Timeline - Black Theme & Continuous Loop.
 * Handles scroll-triggered progressive line animation with smooth repetition.
 */

export function initWorkflowTimeline() {
    const section = document.getElementById('cara-kerja');
    const container = document.getElementById('workflow-timeline');

    if (!section || !container) {
        return;
    }

    if (container.dataset.initialized === 'true') {
        return;
    }
    container.dataset.initialized = 'true';

    const trackBg = container.querySelector('.workflow-track-bg');
    const trackFill = document.getElementById('workflow-track-fill');
    const steps = Array.from(container.querySelectorAll('.workflow-step'));
    const nodes = steps.map((step) => step.querySelector('.workflow-node'));

    if (!trackBg || !trackFill || nodes.length < 5) {
        return;
    }

    let isSectionVisible = false;
    let cycleStartTime = null;
    let rafId = null;

    // Timing constants
    const TRAVEL_DURATION = 5000;   // 5 seconds progress 01 -> 05
    const HOLD_DURATION = 1200;     // 1.2 seconds pause when all points active
    const FADE_DURATION = 400;      // 0.4 seconds smooth transition back to start
    const TOTAL_CYCLE = TRAVEL_DURATION + HOLD_DURATION + FADE_DURATION;

    /**
     * Compute geometry of nodes relative to container
     */
    function getTimelineMetrics() {
        const cRect = container.getBoundingClientRect();
        const firstRect = nodes[0].getBoundingClientRect();
        const lastRect = nodes[nodes.length - 1].getBoundingClientRect();

        const isHorizontal = window.innerWidth >= 768;

        const startX = (firstRect.left + firstRect.width / 2) - cRect.left;
        const startY = (firstRect.top + firstRect.height / 2) - cRect.top;
        const endX = (lastRect.left + lastRect.width / 2) - cRect.left;
        const endY = (lastRect.top + lastRect.height / 2) - cRect.top;

        const totalDist = isHorizontal ? Math.max(endX - startX, 1) : Math.max(endY - startY, 1);

        const thresholds = nodes.map((node) => {
            const nRect = node.getBoundingClientRect();
            if (isHorizontal) {
                const nodeX = (nRect.left + nRect.width / 2) - cRect.left;
                return Math.max(0, Math.min(1, (nodeX - startX) / totalDist));
            } else {
                const nodeY = (nRect.top + nRect.height / 2) - cRect.top;
                return Math.max(0, Math.min(1, (nodeY - startY) / totalDist));
            }
        });

        return { isHorizontal, startX, startY, totalDist, thresholds };
    }

    /**
     * Apply geometric styles and progress
     */
    function render(progress) {
        const { isHorizontal, startX, startY, totalDist, thresholds } = getTimelineMetrics();

        if (isHorizontal) {
            trackBg.style.left = `${startX}px`;
            trackBg.style.top = `${startY}px`;
            trackBg.style.width = `${totalDist}px`;
            trackBg.style.height = '4px';
            trackBg.style.transform = 'translateY(-50%)';

            trackFill.style.left = `${startX}px`;
            trackFill.style.top = `${startY}px`;
            trackFill.style.width = `${totalDist * progress}px`;
            trackFill.style.height = '4px';
            trackFill.style.transform = 'translateY(-50%)';
        } else {
            trackBg.style.left = `${startX}px`;
            trackBg.style.top = `${startY}px`;
            trackBg.style.width = '4px';
            trackBg.style.height = `${totalDist}px`;
            trackBg.style.transform = 'translateX(-50%)';

            trackFill.style.left = `${startX}px`;
            trackFill.style.top = `${startY}px`;
            trackFill.style.width = '4px';
            trackFill.style.height = `${totalDist * progress}px`;
            trackFill.style.transform = 'translateX(-50%)';
        }

        // Activate step nodes based on line reaching each node
        thresholds.forEach((thresh, idx) => {
            const node = nodes[idx];
            if (!node) return;

            if (progress >= Math.max(0, thresh - 0.015)) {
                if (!node.classList.contains('is-completed')) {
                    node.classList.add('is-completed');
                    node.classList.add('is-active');
                }
            } else {
                node.classList.remove('is-completed');
                node.classList.remove('is-active');
            }
        });
    }

    /**
     * Continuous loop frame handler
     */
    function loopFrame(now) {
        if (!isSectionVisible) {
            return;
        }

        if (!cycleStartTime) {
            cycleStartTime = now;
        }

        const elapsed = now - cycleStartTime;

        if (elapsed <= TRAVEL_DURATION) {
            // Fase 1: Garis hitam bergerak dari titik 01 menuju 05
            trackFill.style.opacity = '1';
            const progress = Math.max(0, Math.min(1, elapsed / TRAVEL_DURATION));
            render(progress);
        } else if (elapsed <= TRAVEL_DURATION + HOLD_DURATION) {
            // Fase 2: Jeda saat seluruh garis dan kelima titik aktif
            trackFill.style.opacity = '1';
            render(1);
        } else if (elapsed < TOTAL_CYCLE) {
            // Fase 3: Transisi halus kembali ke awal
            const fadeOutElapsed = elapsed - (TRAVEL_DURATION + HOLD_DURATION);
            const fadeRatio = Math.max(0, Math.min(1, fadeOutElapsed / FADE_DURATION));
            trackFill.style.opacity = String(1 - fadeRatio);
        } else {
            // Fase 4: Restart siklus secara otomatis
            cycleStartTime = now;
            trackFill.style.opacity = '1';
            nodes.slice(1).forEach((node) => {
                node.classList.remove('is-completed');
                node.classList.remove('is-active');
            });
            nodes[0].classList.add('is-completed');
            nodes[0].classList.add('is-active');
            render(0);
        }

        rafId = requestAnimationFrame(loopFrame);
    }

    // Periksa preferensi reduced motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        render(1);
        nodes.forEach((n) => {
            n.classList.add('is-completed');
            n.classList.add('is-active');
        });
        return;
    }

    // Inisialisasi awal pada posisi 0
    render(0);

    // IntersectionObserver untuk memulai/menjeda animasi looping
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        if (!isSectionVisible) {
                            isSectionVisible = true;
                            cycleStartTime = performance.now();
                            nodes[0].classList.add('is-completed');
                            nodes[0].classList.add('is-active');
                            render(0);
                            if (rafId) cancelAnimationFrame(rafId);
                            rafId = requestAnimationFrame(loopFrame);
                        }
                    } else {
                        isSectionVisible = false;
                        if (rafId) {
                            cancelAnimationFrame(rafId);
                            rafId = null;
                        }
                    }
                });
            },
            {
                threshold: 0.15,
                rootMargin: '0px 0px -40px 0px',
            }
        );
        observer.observe(section);
    } else {
        isSectionVisible = true;
        rafId = requestAnimationFrame(loopFrame);
    }

    // Resize handler
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (resizeTimer) cancelAnimationFrame(resizeTimer);
        resizeTimer = requestAnimationFrame(() => {
            render(0.5);
        });
    }, { passive: true });
}
