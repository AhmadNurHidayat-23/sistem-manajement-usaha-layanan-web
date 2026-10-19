/**
 * Smart Instant Prefetcher for SIMAUL Internal Navigation.
 * Pre-fetches internal pages on link hover / touch with 65ms debounce,
 * enabling near-instantaneous page transitions when switching menus.
 */

const prefetchedUrls = new Set();

export function initLinkPrefetch() {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    // Do not prefetch on low-end devices or data-saver mode
    if (navigator.connection && (navigator.connection.saveData || /2g/.test(navigator.connection.effectiveType))) {
        return;
    }

    const currentOrigin = window.location.origin;
    let hoverTimeout = null;

    function shouldPrefetch(anchor) {
        if (!anchor || !anchor.href) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download') || anchor.dataset.action === 'print') return false;

        const url = anchor.href;
        if (prefetchedUrls.has(url)) return false;
        if (!url.startsWith(currentOrigin)) return false;

        // Exclude logout, exports, payment gateway sandbox, and action endpoints
        const path = new URL(url).pathname;
        if (path === '/logout' ||
            path.includes('/export/') ||
            path.includes('/payment/') ||
            path.includes('/simulate-')) {
            return false;
        }

        return true;
    }

    function prefetchUrl(url) {
        if (prefetchedUrls.has(url)) return;
        prefetchedUrls.add(url);

        // Try link prefetch tag first, fallback to low-priority fetch
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    function onPointerEnter(e) {
        const anchor = e.target.closest('a');
        if (!shouldPrefetch(anchor)) return;

        const targetUrl = anchor.href;
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(() => {
            prefetchUrl(targetUrl);
        }, 65);
    }

    function onPointerLeave() {
        clearTimeout(hoverTimeout);
    }

    document.addEventListener('mouseover', onPointerEnter, { passive: true });
    document.addEventListener('mouseout', onPointerLeave, { passive: true });
    document.addEventListener('touchstart', onPointerEnter, { passive: true });
}
