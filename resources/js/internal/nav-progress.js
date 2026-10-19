/**
 * Instant Navigation Progress & Click Feedback Handler.
 * Provides immediate visual feedback (<5ms) when clicking navigation links,
 * eliminating the perceived delay during page transitions in multi-page applications.
 */

export function initNavProgress() {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    const progressBar = document.getElementById('app-top-progress');
    const origin = window.location.origin;

    function startProgress() {
        if (!progressBar) return;
        progressBar.style.transition = 'width 200ms cubic-bezier(0.4, 0, 0.2, 1), opacity 150ms ease';
        progressBar.style.opacity = '1';
        progressBar.style.width = '75%';
    }

    function completeProgress() {
        if (!progressBar) return;
        progressBar.style.transition = 'width 100ms ease-out, opacity 200ms ease 100ms';
        progressBar.style.width = '100%';
        progressBar.style.opacity = '0';
        setTimeout(() => {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0';
        }, 320);
    }

    // Tangani klik pada tautan internal aplikasi
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a');
        if (!anchor || !anchor.href) return;

        // Abaikan klik modifier (Ctrl, Meta, Shift, Alt)
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        // Abaikan target bukan _self atau link eksternal
        if (anchor.target && anchor.target !== '_self') return;
        if (anchor.hasAttribute('download') || anchor.dataset.action === 'print') return;

        const url = anchor.href;
        if (!url.startsWith(origin)) return;

        // Abaikan tautan fragment/anchor di halaman yang sama
        if (url.includes('#') && url.split('#')[0] === window.location.href.split('#')[0]) return;

        // Abaikan aksi logout atau aksi submit
        const path = new URL(url).pathname;
        if (path === '/logout' || path.startsWith('/simulate-')) return;

        // Berikan feedback visual instan pada tautan sidebar yang diklik
        const sidebarLink = anchor.closest('.sidebar-link');
        if (sidebarLink) {
            sidebarLink.style.transition = 'opacity 100ms ease';
            sidebarLink.style.opacity = '0.7';
        }

        startProgress();
    }, { capture: true, passive: true });

    // Selesaikan atau reset saat halaman tampil kembali (mis. Back/Forward cache)
    window.addEventListener('pageshow', (e) => {
        if (progressBar) {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0';
            progressBar.style.opacity = '0';
        }
    });

    // Jalankan finish saat DOM pertama kali siap jika bar sempat aktif
    completeProgress();
}
