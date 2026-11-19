/**
 * Instant Navigation Progress & Click Feedback Handler.
 * Provides immediate visual feedback (<5ms) when clicking navigation links,
 * eliminating perceived delay during page transitions in multi-page applications.
 */

export function initNavProgress() {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    const origin = window.location.origin;

    function getProgressBar() {
        return document.getElementById('app-top-progress');
    }

    function startProgress() {
        const progressBar = getProgressBar();
        if (!progressBar) return;
        progressBar.style.transition = 'width 200ms cubic-bezier(0.4, 0, 0.2, 1), opacity 150ms ease';
        progressBar.style.opacity = '1';
        progressBar.style.width = '75%';
    }

    function completeProgress() {
        const progressBar = getProgressBar();
        if (!progressBar) return;
        progressBar.style.transition = 'width 100ms ease-out, opacity 200ms ease 100ms';
        progressBar.style.width = '100%';
        progressBar.style.opacity = '0';
        setTimeout(() => {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0';
        }, 320);
    }

    function resetActiveLinkState() {
        document.querySelectorAll('.sidebar-link, .nav-link-item').forEach((el) => {
            el.style.opacity = '';
        });
    }

    // Tangani klik pada tautan internal aplikasi
    document.addEventListener('click', (e) => {
        // Abaikan jika pengguna menyukai reduced-motion
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const anchor = e.target.closest('a');
        if (!anchor || !anchor.href) return;

        // Abaikan klik dengan tombol modifier (Ctrl, Meta, Shift, Alt)
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        // Abaikan target bukan _self atau link eksternal
        if (anchor.target && anchor.target !== '_self') return;
        if (anchor.hasAttribute('download') || anchor.dataset.action === 'print') return;

        const href = anchor.getAttribute('href') || '';
        if (href.startsWith('javascript:') || href === '#' || href.startsWith('tel:') || href.startsWith('mailto:')) {
            return;
        }

        // Abaikan link yang membuka modal (jangan ganggu modal Buat Pesanan atau modal lainnya)
        if (anchor.hasAttribute('data-modal-open') || anchor.closest('[data-modal-open]') ||
            anchor.hasAttribute('data-modal-target') || anchor.closest('[data-modal-target]') ||
            anchor.getAttribute('role') === 'button') {
            return;
        }

        // Abaikan onclick yang memicu modal atau script lokal
        const onclickAttr = anchor.getAttribute('onclick') || '';
        if (onclickAttr.toLowerCase().includes('modal')) {
            return;
        }

        const url = anchor.href;
        if (!url.startsWith(origin)) return;

        // Abaikan tautan fragment/anchor di halaman yang sama (misal #cara-kerja, #cabang, #layanan)
        const currentUrlNoHash = window.location.href.split('#')[0];
        const targetUrlNoHash = url.split('#')[0];
        if (url.includes('#') && currentUrlNoHash === targetUrlNoHash) {
            return;
        }

        // Abaikan aksi logout atau aksi submit
        const path = new URL(url).pathname;
        if (path === '/logout' || path.startsWith('/simulate-')) return;

        // Berikan feedback visual instan pada tautan navigasi yang diklik
        const navItem = anchor.closest('.sidebar-link, .nav-link-item');
        if (navItem) {
            navItem.style.transition = 'opacity 120ms ease';
            navItem.style.opacity = '0.75';
        }

        startProgress();
    }, { capture: true, passive: true });

    // Selesaikan atau reset saat halaman tampil kembali (mis. Back/Forward cache)
    window.addEventListener('pageshow', (e) => {
        completeProgress();
        resetActiveLinkState();
    });

    // Jalankan finish saat DOM pertama kali siap jika bar sempat aktif
    completeProgress();
}
