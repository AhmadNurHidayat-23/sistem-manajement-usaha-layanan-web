const MONTH_NAMES = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const DAY_NAMES = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

export function initCustomDatePickers(root = document) {
    root.querySelectorAll('input[type="date"]:not([data-custom-date-initialized="true"]):not([data-no-custom="true"])')
        .forEach(enhanceDatePicker);
}

function formatDateDisplay(ymd) {
    if (!ymd) return '';
    const parts = ymd.split('-');
    return parts.length === 3 ? `${parts[2].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[0]}` : ymd;
}

function formatToYMD(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function parseYMD(ymd) {
    if (!ymd) return null;
    const [y, m, d] = ymd.split('-').map(Number);
    return isNaN(y) || isNaN(m) || isNaN(d) ? null : new Date(y, m - 1, d);
}

function enhanceDatePicker(nativeInput) {
    nativeInput.setAttribute('data-custom-date-initialized', 'true');
    nativeInput.classList.add('custom-date-hidden-native');

    const wrapper = document.createElement('div');
    const hasFullWidth = nativeInput.classList.contains('w-full') || !nativeInput.className.includes('w-');
    wrapper.className = `custom-date-container relative inline-block text-left ${hasFullWidth ? 'w-full' : ''}`;

    nativeInput.classList.forEach(cls => {
        if (cls.startsWith('w-') || cls.startsWith('max-w-') || cls.startsWith('min-w-')) {
            wrapper.classList.add(cls);
        }
    });

    const heightClass = Array.from(nativeInput.classList).find(cls => cls.startsWith('h-')) || 'h-11';

    // Render struktur display input & panel kalender
    wrapper.innerHTML = `
        <div class="relative flex items-center w-full">
            <input type="text" readonly placeholder="dd/mm/yyyy" value="${formatDateDisplay(nativeInput.value)}"
                class="custom-date-trigger w-full ${heightClass} pl-3.5 pr-10 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:border-[#062072] focus:ring-1 focus:ring-[#062072] transition-colors cursor-pointer select-none ${nativeInput.disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : ''}"
                ${nativeInput.disabled ? 'disabled' : ''} ${nativeInput.required ? 'aria-required="true"' : ''}>
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#667085] flex items-center justify-center">
                <svg class="w-4 h-4 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        <div class="custom-date-panel absolute left-0 mt-1 bg-white border border-[#E4E7EC] rounded-xl shadow-lg p-3.5 z-50 w-72 sm:w-80 hidden select-none"></div>
    `;

    const displayInput = wrapper.querySelector('.custom-date-trigger');
    const panel = wrapper.querySelector('.custom-date-panel');

    let currentDate = parseYMD(nativeInput.value) || new Date();
    let viewYear = currentDate.getFullYear();
    let viewMonth = currentDate.getMonth();

    function renderCalendar() {
        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const daysInPrev = new Date(viewYear, viewMonth, 0).getDate();
        const todayYMD = formatToYMD(new Date());
        const selectedYMD = nativeInput.value;

        let cellsHtml = '';

        // Tanggal bulan sebelumnya
        for (let i = firstDay - 1; i >= 0; i--) {
            const dayNum = daysInPrev - i;
            cellsHtml += `<button type="button" data-date="${formatToYMD(new Date(viewYear, viewMonth - 1, dayNum))}" class="h-8 w-8 sm:h-8.5 sm:w-8.5 mx-auto flex items-center justify-center text-xs text-[#98A2B3] rounded-lg hover:bg-[#F5F8FC]/60 transition-colors cursor-pointer">${dayNum}</button>`;
        }

        // Tanggal bulan aktif
        for (let day = 1; day <= daysInMonth; day++) {
            const thisYMD = formatToYMD(new Date(viewYear, viewMonth, day));
            const isSelected = selectedYMD === thisYMD;
            const isToday = todayYMD === thisYMD;

            let cls = 'h-8 w-8 sm:h-8.5 sm:w-8.5 mx-auto flex items-center justify-center text-xs sm:text-[13px] rounded-lg transition-colors cursor-pointer ';
            if (isSelected) {
                cls += 'bg-[#062072] text-white font-bold hover:bg-[#04184F]';
            } else if (isToday) {
                cls += 'border border-[#062072] text-[#062072] font-semibold hover:bg-[#F5F8FC]';
            } else {
                cls += 'text-[#101828] hover:bg-[#F5F8FC] font-medium';
            }

            cellsHtml += `<button type="button" data-date="${thisYMD}" class="${cls}">${day}</button>`;
        }

        // Tanggal bulan berikutnya
        const total = firstDay + daysInMonth;
        const remaining = (total > 35 ? 42 : 35) - total;
        for (let day = 1; day <= remaining; day++) {
            cellsHtml += `<button type="button" data-date="${formatToYMD(new Date(viewYear, viewMonth + 1, day))}" class="h-8 w-8 sm:h-8.5 sm:w-8.5 mx-auto flex items-center justify-center text-xs text-[#98A2B3] rounded-lg hover:bg-[#F5F8FC]/60 transition-colors cursor-pointer">${day}</button>`;
        }

        panel.innerHTML = `
            <div class="flex items-center justify-between pb-3 mb-2.5 border-b border-[#E4E7EC]">
                <h5 class="text-sm font-bold text-[#101828] m-0">${MONTH_NAMES[viewMonth]} ${viewYear}</h5>
                <div class="flex items-center gap-1">
                    <button type="button" class="btn-prev-month p-1.5 rounded-lg text-[#062072] hover:bg-[#F5F8FC] transition-colors cursor-pointer" title="Bulan Sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="btn-next-month p-1.5 rounded-lg text-[#062072] hover:bg-[#F5F8FC] transition-colors cursor-pointer" title="Bulan Berikutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center font-semibold text-[11px] text-[#667085] mb-2">
                ${DAY_NAMES.map(d => `<div>${d}</div>`).join('')}
            </div>
            <div class="grid grid-cols-7 gap-1 text-center">
                ${cellsHtml}
            </div>
            <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-[#E4E7EC] text-xs">
                <button type="button" class="btn-clear text-[#667085] hover:text-rose-600 transition-colors font-medium cursor-pointer">Hapus</button>
                <button type="button" class="btn-today text-[#062072] hover:underline font-semibold cursor-pointer">Hari Ini</button>
            </div>
        `;

        // Event listener navigasi dan pemilihan tanggal
        panel.querySelector('.btn-prev-month').addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth = viewMonth === 0 ? (viewYear--, 11) : viewMonth - 1;
            renderCalendar();
        });

        panel.querySelector('.btn-next-month').addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth = viewMonth === 11 ? (viewYear++, 0) : viewMonth + 1;
            renderCalendar();
        });

        panel.querySelector('.btn-clear').addEventListener('click', (e) => {
            e.stopPropagation();
            setDateValue('');
        });

        panel.querySelector('.btn-today').addEventListener('click', (e) => {
            e.stopPropagation();
            setDateValue(formatToYMD(new Date()));
        });

        panel.querySelectorAll('button[data-date]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                setDateValue(btn.dataset.date);
            });
        });
    }

    function setDateValue(ymd) {
        nativeInput.value = ymd;
        displayInput.value = formatDateDisplay(ymd);
        closePanel();

        // Dispatch events agar filter dan backend tetap sinkron
        nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
        nativeInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof nativeInput.onchange === 'function') nativeInput.onchange();
        displayInput.focus();
    }

    function openPanel() {
        if (nativeInput.disabled) return;

        // Tutup kalender lain
        document.querySelectorAll('.custom-date-panel:not(.hidden)').forEach(p => {
            if (p !== panel) p.classList.add('hidden');
        });

        // Penyesuaian posisi overlay (auto-flip jika ruang bawah sempit)
        const rect = displayInput.getBoundingClientRect();
        const isUp = (window.innerHeight - rect.bottom < 320) && (rect.top > (window.innerHeight - rect.bottom));
        panel.style.top = isUp ? 'auto' : '100%';
        panel.style.bottom = isUp ? '100%' : 'auto';
        panel.style.marginTop = isUp ? '0' : '4px';
        panel.style.marginBottom = isUp ? '4px' : '0';

        const selected = parseYMD(nativeInput.value) || new Date();
        viewYear = selected.getFullYear();
        viewMonth = selected.getMonth();

        renderCalendar();
        panel.classList.remove('hidden');
    }

    function closePanel() {
        panel.classList.add('hidden');
    }

    function togglePanel() {
        panel.classList.contains('hidden') ? openPanel() : closePanel();
    }

    displayInput.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePanel();
    });

    displayInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePanel();
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePanel();
        }
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) closePanel();
    });

    const syncDisplay = () => {
        displayInput.value = formatDateDisplay(nativeInput.value);
    };
    nativeInput.addEventListener('change', syncDisplay);
    nativeInput.addEventListener('input', syncDisplay);

    nativeInput.parentNode.insertBefore(wrapper, nativeInput);
    wrapper.appendChild(nativeInput);
}
