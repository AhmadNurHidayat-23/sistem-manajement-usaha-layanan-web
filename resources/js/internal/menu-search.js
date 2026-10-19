/**
 * Menu Search handler for SIMAUL Header.
 * Simple, pill-shaped input without dropdown.
 * On Enter: navigates directly to matching menu accessible by current role.
 * If not found: shows a brief, subtle indicator message without opening popups.
 */

export function initMenuSearch(root = document) {
    const wrapper = root.getElementById?.('menu-search-wrapper') ?? document.getElementById('menu-search-wrapper');
    if (!wrapper || wrapper.dataset.boundSearch) return;
    wrapper.dataset.boundSearch = '1';

    const input = wrapper.querySelector('#menu-search-input');
    const clearBtn = wrapper.querySelector('#menu-search-clear');
    const feedback = wrapper.querySelector('#menu-search-feedback');

    if (!input) return;

    let menus = [];
    try {
        menus = JSON.parse(wrapper.dataset.menus || '[]');
    } catch {
        menus = [];
    }

    let feedbackTimeout = null;

    function showFeedback(text = 'Menu tidak ditemukan') {
        if (!feedback) return;
        feedback.textContent = text;
        feedback.classList.remove('hidden');

        if (feedbackTimeout) clearTimeout(feedbackTimeout);
        feedbackTimeout = setTimeout(() => {
            feedback.classList.add('hidden');
        }, 2200);
    }

    function hideFeedback() {
        if (!feedback) return;
        feedback.classList.add('hidden');
        if (feedbackTimeout) clearTimeout(feedbackTimeout);
    }

    function findMatch(query) {
        const q = query.trim().toLowerCase();
        if (!q) return null;

        // 1. Exact title match
        const exact = menus.find(m => m.title.toLowerCase() === q);
        if (exact) return exact;

        // 2. Starts with title match
        const startsWith = menus.find(m => m.title.toLowerCase().startsWith(q));
        if (startsWith) return startsWith;

        // 3. Substring in title
        const inTitle = menus.find(m => m.title.toLowerCase().includes(q));
        if (inTitle) return inTitle;

        // 4. Substring in keywords
        const inKeywords = menus.find(m => (m.keywords || '').toLowerCase().includes(q));
        if (inKeywords) return inKeywords;

        // 5. Substring in section
        const inSection = menus.find(m => m.section.toLowerCase().includes(q));
        if (inSection) return inSection;

        return null;
    }

    input.addEventListener('input', (e) => {
        hideFeedback();
        const hasText = e.target.value.trim().length > 0;
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', !hasText);
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = input.value.trim();
            if (!val) return;

            const match = findMatch(val);
            if (match && match.url) {
                window.location.href = match.url;
            } else {
                showFeedback();
                input.classList.add('ring-2', 'ring-rose-400', 'border-rose-400');
                setTimeout(() => {
                    input.classList.remove('ring-2', 'ring-rose-400', 'border-rose-400');
                }, 1000);
            }
        } else if (e.key === 'Escape') {
            hideFeedback();
            input.blur();
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            input.value = '';
            clearBtn.classList.add('hidden');
            hideFeedback();
            input.focus();
        });
    }

    // Global keyboard shortcut: Ctrl+K or Cmd+K or / (when not typing in other inputs)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            input.focus();
            input.select();
        } else if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });
}
