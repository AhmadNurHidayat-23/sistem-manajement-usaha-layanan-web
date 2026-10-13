export function initCustomSelects(root = document) {
    root.querySelectorAll('select:not([data-custom-select-initialized="true"]):not([data-no-custom="true"])')
        .forEach(select => {
            if (!select.multiple && select.getAttribute('multiple') === null) {
                enhanceSelect(select);
            }
        });
}

function enhanceSelect(select) {
    select.setAttribute('data-custom-select-initialized', 'true');
    select.classList.add('custom-select-hidden-native');

    const wrapper = document.createElement('div');
    wrapper.className = `custom-select-container relative inline-block text-left ${select.classList.contains('w-full') || !select.className.includes('w-') ? 'w-full' : ''}`;

    select.classList.forEach(cls => {
        if (cls.startsWith('w-') || cls.startsWith('max-w-') || cls.startsWith('min-w-')) {
            wrapper.classList.add(cls);
        }
    });

    // Render struktur trigger & menu overlay
    wrapper.innerHTML = `
        <button type="button" class="custom-select-trigger w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] flex items-center justify-between gap-2 focus:outline-none focus:border-[#062072] focus:ring-1 focus:ring-[#062072] transition-colors cursor-pointer select-none ${select.disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : ''}" aria-haspopup="listbox" aria-expanded="false" ${select.disabled ? 'disabled' : ''}>
            <span class="custom-select-label truncate text-left flex-1"></span>
            <svg class="w-4 h-4 text-[#667085] shrink-0 transition-transform duration-200 custom-select-chevron" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div class="custom-select-menu absolute left-0 right-0 top-full mt-1 bg-white border border-[#E4E7EC] rounded-lg shadow-lg py-1 z-50 max-h-60 overflow-y-auto hidden" role="listbox"></div>
    `;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const labelSpan = wrapper.querySelector('.custom-select-label');
    const chevronSvg = wrapper.querySelector('.custom-select-chevron');
    const menu = wrapper.querySelector('.custom-select-menu');

    let highlightedIndex = -1;

    // Render opsi dropdown
    function renderOptions() {
        const options = Array.from(select.options);
        const selected = select.selectedOptions[0] || options[0];
        labelSpan.textContent = selected ? selected.text : 'Pilih...';

        menu.innerHTML = options.map((opt, idx) => `
            <div class="custom-select-option h-[38px] px-4 py-2 flex items-center justify-between text-[13px] text-[#101828] cursor-pointer transition-colors hover:bg-[#F5F8FC] ${opt.selected ? 'bg-[#DCE8FF] text-[#062072] font-medium' : ''} ${opt.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''}"
                 data-index="${idx}" data-value="${opt.value}" role="option" ${opt.selected ? 'aria-selected="true"' : ''}>
                <span class="truncate">${opt.text}</span>
                ${opt.selected ? `
                    <svg class="w-4 h-4 text-[#062072] shrink-0 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                ` : ''}
            </div>
        `).join('');

        menu.querySelectorAll('.custom-select-option:not(.pointer-events-none)').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                selectOption(parseInt(item.dataset.index, 10));
            });
        });
    }

    function selectOption(index) {
        if (!select.options[index] || select.options[index].disabled) return;
        select.selectedIndex = index;
        renderOptions();
        closeMenu();

        // Dispatch event native untuk filter & form submit
        select.dispatchEvent(new Event('change', { bubbles: true }));
        select.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof select.onchange === 'function') select.onchange();
        trigger.focus();
    }

    function openMenu() {
        if (select.disabled) return;

        // Tutup menu dropdown lain
        document.querySelectorAll('.custom-select-menu:not(.hidden)').forEach(m => {
            if (m !== menu) {
                m.classList.add('hidden');
                m.closest('.custom-select-container')?.querySelector('.custom-select-chevron')?.classList.remove('rotate-180');
            }
        });

        // Penyesuaian posisi overlay (auto-flip jika ruang bawah sempit)
        const rect = trigger.getBoundingClientRect();
        const isUp = (window.innerHeight - rect.bottom < 250) && (rect.top > (window.innerHeight - rect.bottom));
        menu.style.top = isUp ? 'auto' : '100%';
        menu.style.bottom = isUp ? '100%' : 'auto';
        menu.style.marginTop = isUp ? '0' : '4px';
        menu.style.marginBottom = isUp ? '4px' : '0';

        menu.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        chevronSvg.classList.add('rotate-180');

        const active = menu.querySelector('[aria-selected="true"]');
        highlightedIndex = active ? parseInt(active.dataset.index, 10) : 0;
        active?.scrollIntoView({ block: 'nearest' });
        updateHighlight();
    }

    function closeMenu() {
        menu.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        chevronSvg.classList.remove('rotate-180');
        highlightedIndex = -1;
    }

    function toggleMenu() {
        menu.classList.contains('hidden') ? openMenu() : closeMenu();
    }

    function updateHighlight() {
        menu.querySelectorAll('.custom-select-option').forEach((el, idx) => {
            el.classList.toggle('bg-[#F5F8FC]', idx === highlightedIndex && !el.getAttribute('aria-selected'));
            if (idx === highlightedIndex) el.scrollIntoView({ block: 'nearest' });
        });
    }

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    // Navigasi keyboard
    trigger.addEventListener('keydown', (e) => {
        const isOpen = !menu.classList.contains('hidden');
        const count = select.options.length;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            !isOpen ? openMenu() : (highlightedIndex = (highlightedIndex + 1) % count, updateHighlight());
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            !isOpen ? openMenu() : (highlightedIndex = (highlightedIndex - 1 + count) % count, updateHighlight());
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            !isOpen ? openMenu() : (highlightedIndex >= 0 && selectOption(highlightedIndex));
        } else if (e.key === 'Escape' || e.key === 'Tab') {
            if (isOpen) closeMenu();
        }
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) closeMenu();
    });

    select.addEventListener('change', renderOptions);
    select.addEventListener('render-custom-select', renderOptions);

    if (typeof MutationObserver !== 'undefined') {
        const optionObserver = new MutationObserver(() => {
            renderOptions();
        });
        optionObserver.observe(select, { childList: true, subtree: true, characterData: true });
    }

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    renderOptions();
}
