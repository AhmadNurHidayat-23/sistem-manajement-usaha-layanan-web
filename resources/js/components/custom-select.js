export function initCustomSelects(root = document) {
    root.querySelectorAll('select:not([data-custom-select-initialized="true"]):not([data-no-custom="true"])')
        .forEach(s => (!s.multiple && s.getAttribute('multiple') === null) && enhanceSelect(s));
}

function getPortalRoot() {
    let p = document.getElementById('simaul-select-portal');
    if (!p) {
        p = document.createElement('div');
        p.id = 'simaul-select-portal';
        p.className = 'simaul-select-portal';
        document.body.appendChild(p);
    }
    return p;
}

function enhanceSelect(select) {
    select.setAttribute('data-custom-select-initialized', 'true');
    select.classList.add('custom-select-hidden-native');

    const wrapper = document.createElement('div');
    wrapper.className = `custom-select-container relative inline-block text-left ${select.classList.contains('w-full') || !select.className.includes('w-') ? 'w-full' : ''}`;
    select.classList.forEach(c => (c.startsWith('w-') || c.startsWith('max-w-') || c.startsWith('min-w-')) && wrapper.classList.add(c));

    const isClearable = select.hasAttribute('data-clearable') || select.id === 'employee_id';
    wrapper.innerHTML = `
        <button type="button" class="custom-select-trigger w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] flex items-center justify-between gap-2 focus:outline-none focus:border-[#062072] focus:ring-1 focus:ring-[#062072] transition-colors cursor-pointer select-none ${select.disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : ''}" aria-haspopup="listbox" aria-expanded="false" ${select.disabled ? 'disabled' : ''}>
            <span class="custom-select-label truncate text-left flex-1"></span>
            ${isClearable ? `<span class="custom-select-clear-btn p-1 text-[#98A2B3] hover:text-rose-500 rounded-full transition-colors hidden cursor-pointer" title="Kosongkan pilihan" aria-label="Kosongkan pilihan"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>` : ''}
            <svg class="w-4 h-4 text-[#667085] shrink-0 transition-transform duration-200 custom-select-chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div class="custom-select-menu fixed bg-white border border-[#E4E7EC] rounded-lg shadow-lg py-1 z-50 overflow-y-auto hidden" role="listbox"></div>
    `;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const labelSpan = wrapper.querySelector('.custom-select-label');
    const chevronSvg = wrapper.querySelector('.custom-select-chevron');
    const clearBtn = wrapper.querySelector('.custom-select-clear-btn');
    const menu = wrapper.querySelector('.custom-select-menu');
    let highlightedIndex = -1, selectCloseTimer = null;

    function renderOptions() {
        const options = Array.from(select.options);
        const selected = select.selectedOptions[0] || options[0];
        const isPl = !selected || selected.value === '' || selected.hidden || selected.dataset.placeholder === 'true' || selected.disabled;

        labelSpan.textContent = selected ? selected.text : 'Pilih...';
        labelSpan.classList.toggle('text-[#98A2B3]', isPl);
        labelSpan.classList.toggle('text-[#101828]', !isPl);
        if (clearBtn) clearBtn.classList.toggle('hidden', isPl);

        const menuOptions = options.filter(opt => !opt.hidden && opt.dataset.placeholder !== 'true');
        menu.innerHTML = menuOptions.map(opt => {
            const origIdx = options.indexOf(opt);
            const isSel = opt.selected && opt.value !== '';
            return `
                <div class="custom-select-option h-[38px] px-4 py-2 flex items-center justify-between text-[13px] text-[#101828] cursor-pointer transition-colors hover:bg-[#F5F8FC] ${isSel ? 'bg-[#DCE8FF] text-[#062072] font-medium' : ''} ${opt.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''}"
                     data-index="${origIdx}" data-value="${opt.value}" role="option" ${isSel ? 'aria-selected="true"' : ''}>
                    <span class="truncate">${opt.text}</span>
                    ${isSel ? `<svg class="w-4 h-4 text-[#062072] shrink-0 ml-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>` : ''}
                </div>`;
        }).join('');

        menu.querySelectorAll('.custom-select-option:not(.pointer-events-none)').forEach(item => {
            item.addEventListener('click', e => {
                e.stopPropagation();
                selectOption(parseInt(item.dataset.index, 10));
            });
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', e => {
            e.stopPropagation();
            e.preventDefault();
            const pl = Array.from(select.options).find(o => o.value === '' || o.dataset.placeholder === 'true');
            pl ? (pl.selected = true) : (select.selectedIndex = -1);
            select.value = '';
            renderOptions();
            closeMenu();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
            trigger.focus();
        });
    }

    function selectOption(index) {
        if (!select.options[index] || select.options[index].disabled) return;
        select.selectedIndex = index;
        renderOptions();
        closeMenu();
        select.dispatchEvent(new Event('change', { bubbles: true }));
        select.dispatchEvent(new Event('input', { bubbles: true }));
        trigger.focus();
    }

    function updateMenuPosition() {
        if (menu.classList.contains('hidden')) return;
        const rect = trigger.getBoundingClientRect();
        if (rect.width === 0 && rect.height === 0) return;

        const vh = window.innerHeight, vw = window.innerWidth;
        const spaceBelow = vh - rect.bottom, spaceAbove = rect.top;
        const optCount = menu.querySelectorAll('.custom-select-option').length;
        const estH = Math.min(240, Math.max(80, optCount * 38 + 8));
        const isUp = (spaceBelow < Math.min(220, estH + 10)) && (spaceAbove > spaceBelow);

        menu.style.position = 'fixed';
        menu.style.width = `${rect.width}px`;
        menu.style.minWidth = `${Math.min(rect.width, 180)}px`;

        let left = rect.left;
        if (left + rect.width > vw - 8) left = Math.max(8, vw - rect.width - 8);
        menu.style.left = `${Math.max(8, left)}px`;
        menu.style.right = 'auto';

        if (isUp) {
            menu.style.maxHeight = `${Math.min(240, Math.max(100, spaceAbove - 16))}px`;
            menu.style.top = 'auto';
            menu.style.bottom = `${vh - rect.top + 4}px`;
            menu.classList.add('custom-select-menu-up');
        } else {
            menu.style.maxHeight = `${Math.min(240, Math.max(100, spaceBelow - 16))}px`;
            menu.style.top = `${rect.bottom + 4}px`;
            menu.style.bottom = 'auto';
            menu.classList.remove('custom-select-menu-up');
        }
        menu.style.zIndex = '99999';
    }

    function onScrollOrResize() {
        if (menu.classList.contains('hidden')) return;
        const r = trigger.getBoundingClientRect();
        (r.bottom < 0 || r.top > window.innerHeight) ? closeMenu() : updateMenuPosition();
    }

    function openMenu() {
        if (select.disabled) return;
        if (selectCloseTimer) clearTimeout(selectCloseTimer);

        document.querySelectorAll('.custom-select-menu:not(.hidden)').forEach(m => {
            if (m !== menu) {
                m.classList.remove('is-open');
                m.classList.add('hidden');
                m._cleanupListeners?.();
                m._trigger?.setAttribute('aria-expanded', 'false');
                m._chevron?.classList.remove('rotate-180');
            }
        });

        const portal = getPortalRoot();
        if (menu.parentElement !== portal) portal.appendChild(menu);

        menu._trigger = trigger;
        menu._chevron = chevronSvg;
        menu._cleanupListeners = () => {
            window.removeEventListener('scroll', onScrollOrResize, { capture: true });
            window.removeEventListener('resize', onScrollOrResize);
        };

        menu.classList.remove('hidden');
        updateMenuPosition();
        requestAnimationFrame(() => menu.classList.add('is-open'));
        trigger.setAttribute('aria-expanded', 'true');
        chevronSvg.classList.add('rotate-180');

        window.addEventListener('scroll', onScrollOrResize, { capture: true, passive: true });
        window.addEventListener('resize', onScrollOrResize, { passive: true });

        const active = menu.querySelector('[aria-selected="true"]');
        const opts = Array.from(menu.querySelectorAll('.custom-select-option'));
        highlightedIndex = Math.max(0, opts.indexOf(active));
        active?.scrollIntoView({ block: 'nearest' });
        updateHighlight();
    }

    function closeMenu() {
        window.removeEventListener('scroll', onScrollOrResize, { capture: true });
        window.removeEventListener('resize', onScrollOrResize);
        menu.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        chevronSvg.classList.remove('rotate-180');
        highlightedIndex = -1;

        if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
            menu.classList.add('hidden');
        } else {
            selectCloseTimer = setTimeout(() => {
                if (!menu.classList.contains('is-open')) menu.classList.add('hidden');
            }, 160);
        }
    }

    function updateHighlight() {
        menu.querySelectorAll('.custom-select-option').forEach((el, idx) => {
            el.classList.toggle('bg-[#F5F8FC]', idx === highlightedIndex && !el.getAttribute('aria-selected'));
            if (idx === highlightedIndex) el.scrollIntoView({ block: 'nearest' });
        });
    }

    trigger.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.contains('is-open') ? closeMenu() : openMenu();
    });

    trigger.addEventListener('keydown', e => {
        const isOpen = !menu.classList.contains('hidden');
        const opts = Array.from(menu.querySelectorAll('.custom-select-option'));
        if (opts.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            !isOpen ? openMenu() : ((highlightedIndex = (highlightedIndex + 1) % opts.length), updateHighlight());
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            !isOpen ? openMenu() : ((highlightedIndex = (highlightedIndex - 1 + opts.length) % opts.length), updateHighlight());
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (!isOpen) openMenu();
            else if (highlightedIndex >= 0 && opts[highlightedIndex]) {
                selectOption(parseInt(opts[highlightedIndex].dataset.index, 10));
            }
        } else if (e.key === 'Escape' || e.key === 'Tab') {
            if (isOpen) closeMenu();
        }
    });

    document.addEventListener('click', e => {
        if (!wrapper.contains(e.target) && !menu.contains(e.target)) closeMenu();
    });

    select.addEventListener('change', renderOptions);
    select.addEventListener('render-custom-select', renderOptions);

    if (typeof MutationObserver !== 'undefined') {
        const obs = new MutationObserver(() => {
            if (!select.isConnected && menu.parentElement) {
                menu.remove();
                obs.disconnect();
                return;
            }
            renderOptions();
        });
        obs.observe(select, { childList: true, subtree: true, characterData: true });
    }

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    renderOptions();
}
