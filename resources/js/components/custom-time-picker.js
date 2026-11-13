/**
 * SIMAUL Compact 24-Hour Time Picker Component (HH:mm)
 * Compact, proportional, and responsive two-column time picker
 * designed specifically for modal and form layouts in SIMAUL.
 */

let activeTimePicker = null;

export function closeAllTimePickers() {
    if (activeTimePicker && typeof activeTimePicker.close === 'function') {
        activeTimePicker.close();
    }
    activeTimePicker = null;
}

export function initCustomTimePickers(root = document) {
    if (!root) return;

    const inputs = root.querySelectorAll(
        'input[type="time"]:not([data-custom-time-initialized="true"]):not([data-no-custom="true"]), input[data-custom-time-picker="true"]:not([data-custom-time-initialized="true"])'
    );

    inputs.forEach(enhanceTimePicker);
}

function padZero(num) {
    return String(num).padStart(2, '0');
}

function parseTime(val) {
    if (!val || typeof val !== 'string') return { hour: '08', minute: '00' };
    const parts = val.trim().split(':');
    let h = parseInt(parts[0], 10);
    let m = parseInt(parts[1], 10);

    if (isNaN(h) || h < 0 || h > 23) h = 8;
    if (isNaN(m) || m < 0 || m > 59) m = 0;

    return {
        hour: padZero(h),
        minute: padZero(m)
    };
}

export function enhanceTimePicker(nativeInput) {
    if (!nativeInput || nativeInput.dataset.customTimeInitialized === 'true') return;

    nativeInput.setAttribute('data-custom-time-initialized', 'true');
    nativeInput.classList.add('custom-time-hidden-native');

    const initialVal = nativeInput.value ? nativeInput.value.substring(0, 5) : '';
    let { hour: currentHour, minute: currentMinute } = parseTime(initialVal || '08:00');
    let hasValue = Boolean(initialVal);

    // Wrapper container
    const wrapper = document.createElement('div');
    const isFullWidth = nativeInput.classList.contains('w-full') || !nativeInput.className.includes('w-');
    wrapper.className = `custom-time-container relative inline-block text-left ${isFullWidth ? 'w-full' : ''}`;

    nativeInput.classList.forEach(cls => {
        if (cls.startsWith('max-w-') || cls.startsWith('min-w-')) {
            wrapper.classList.add(cls);
        }
    });

    const heightClass = Array.from(nativeInput.classList).find(cls => cls.startsWith('h-')) || 'h-10';
    const displayValue = hasValue ? `${currentHour}:${currentMinute}` : '';

    wrapper.innerHTML = `
        <div class="relative flex items-center w-full">
            <input type="text"
                   readonly
                   inputmode="numeric"
                   placeholder="HH:mm"
                   value="${displayValue}"
                   class="custom-time-trigger w-full ${heightClass} pl-3 pr-8 bg-white border border-[#D0D5DD] rounded-lg text-xs sm:text-[13px] font-semibold text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:border-[#062072] focus:ring-1 focus:ring-[#062072] transition-colors cursor-pointer select-none ${nativeInput.disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : ''}"
                   aria-haspopup="dialog"
                   aria-expanded="false"
                   aria-label="${nativeInput.getAttribute('aria-label') || 'Pilih Waktu (Jam:Menit)'}"
                   ${nativeInput.disabled ? 'disabled' : ''}
                   ${nativeInput.required ? 'aria-required="true"' : ''}>
            <button type="button"
                    tabindex="-1"
                    aria-label="Buka pilihan jam dan menit"
                    class="custom-time-icon-btn absolute right-2 top-1/2 -translate-y-1/2 text-[#667085] hover:text-[#062072] transition-colors cursor-pointer p-1 rounded">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
        </div>
        <div class="custom-time-panel absolute bg-white border border-[#E4E7EC] rounded-xl shadow-xl p-2.5 z-[70] w-48 sm:w-[196px] hidden select-none"
             role="dialog"
             aria-modal="false"
             aria-label="Pilihan Waktu 24 Jam">
            
            <!-- Header Panel: Waktu Terpilih & Label 24 Jam -->
            <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-[#E4E7EC]">
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="custom-time-preview font-bold text-xs text-[#062072] bg-[#EBF2FE] px-1.5 py-0.5 rounded font-mono">
                        ${currentHour}:${currentMinute}
                    </span>
                </div>
                <span class="text-[10px] font-semibold text-[#475467] bg-slate-100 px-1 py-0.5 rounded">
                    24 Jam
                </span>
            </div>

            <!-- Two-Column Selection: Jam & Menit -->
            <div class="grid grid-cols-2 gap-1.5 mb-1.5">
                <!-- Kolom Jam (00 - 23) -->
                <div>
                    <div class="text-[10px] font-bold text-[#667085] uppercase tracking-wider mb-1 text-center bg-[#F8FAFC] py-0.5 rounded border border-[#E4E7EC]/60">
                        Jam
                    </div>
                    <div class="custom-time-hours-list h-36 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar" role="listbox" aria-label="Daftar Jam">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Kolom Menit (00 - 59) -->
                <div>
                    <div class="text-[10px] font-bold text-[#667085] uppercase tracking-wider mb-1 text-center bg-[#F8FAFC] py-0.5 rounded border border-[#E4E7EC]/60">
                        Menit
                    </div>
                    <div class="custom-time-minutes-list h-36 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar" role="listbox" aria-label="Daftar Menit">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Preset Cepat Ringkas (4 Kolom Rapi) -->
            <div class="grid grid-cols-4 gap-1 pt-1.5 border-t border-[#E4E7EC]">
                <button type="button" data-preset="08:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">
                    08:00
                </button>
                <button type="button" data-preset="12:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">
                    12:00
                </button>
                <button type="button" data-preset="16:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">
                    16:00
                </button>
                <button type="button" data-preset="17:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">
                    17:00
                </button>
            </div>

            <!-- Tombol Selesai Rapi & Jelas -->
            <button type="button" class="custom-time-done-btn w-full mt-1.5 py-1 text-xs font-bold text-white bg-[#062072] hover:bg-[#04184F] rounded-lg transition-colors cursor-pointer shadow-2xs">
                Selesai
            </button>
        </div>
    `;

    // Insert wrapper into DOM
    nativeInput.parentNode.insertBefore(wrapper, nativeInput);
    wrapper.appendChild(nativeInput);

    const trigger = wrapper.querySelector('.custom-time-trigger');
    const iconBtn = wrapper.querySelector('.custom-time-icon-btn');
    const panel = wrapper.querySelector('.custom-time-panel');
    const preview = wrapper.querySelector('.custom-time-preview');
    const hoursList = wrapper.querySelector('.custom-time-hours-list');
    const minutesList = wrapper.querySelector('.custom-time-minutes-list');
    const doneBtn = wrapper.querySelector('.custom-time-done-btn');
    const presetBtns = wrapper.querySelectorAll('.custom-time-preset');

    // Populate Hours (00-23)
    let hoursHtml = '';
    for (let h = 0; h < 24; h++) {
        const hStr = padZero(h);
        hoursHtml += `
            <button type="button"
                    data-hour="${hStr}"
                    role="option"
                    aria-selected="false"
                    class="custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-semibold transition-colors cursor-pointer">
                ${hStr}
            </button>
        `;
    }
    hoursList.innerHTML = hoursHtml;

    // Populate Minutes (00-59)
    let minutesHtml = '';
    for (let m = 0; m < 60; m++) {
        const mStr = padZero(m);
        minutesHtml += `
            <button type="button"
                    data-minute="${mStr}"
                    role="option"
                    aria-selected="false"
                    class="custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-semibold transition-colors cursor-pointer">
                ${mStr}
            </button>
        `;
    }
    minutesList.innerHTML = minutesHtml;

    function updateActiveStates() {
        hoursList.querySelectorAll('.custom-hour-btn').forEach(btn => {
            const h = btn.getAttribute('data-hour');
            if (h === currentHour) {
                btn.className = 'custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-bold transition-colors cursor-pointer bg-[#062072] text-white shadow-2xs';
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.className = 'custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-medium text-[#101828] transition-colors cursor-pointer hover:bg-[#F5F8FC] hover:text-[#062072]';
                btn.setAttribute('aria-selected', 'false');
            }
        });

        minutesList.querySelectorAll('.custom-minute-btn').forEach(btn => {
            const m = btn.getAttribute('data-minute');
            if (m === currentMinute) {
                btn.className = 'custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-bold transition-colors cursor-pointer bg-[#062072] text-white shadow-2xs';
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.className = 'custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-medium text-[#101828] transition-colors cursor-pointer hover:bg-[#F5F8FC] hover:text-[#062072]';
                btn.setAttribute('aria-selected', 'false');
            }
        });

        if (preview) {
            preview.textContent = `${currentHour}:${currentMinute}`;
        }
    }

    function scrollToSelected() {
        const activeHourBtn = hoursList.querySelector(`[data-hour="${currentHour}"]`);
        if (activeHourBtn) {
            activeHourBtn.scrollIntoView({ block: 'nearest' });
        }
        const activeMinuteBtn = minutesList.querySelector(`[data-minute="${currentMinute}"]`);
        if (activeMinuteBtn) {
            activeMinuteBtn.scrollIntoView({ block: 'nearest' });
        }
    }

    function applyTimeToInput(h, m, notify = true) {
        currentHour = padZero(h);
        currentMinute = padZero(m);
        const timeStr = `${currentHour}:${currentMinute}`;

        nativeInput.value = timeStr;
        trigger.value = timeStr;
        hasValue = true;

        updateActiveStates();

        if (notify) {
            nativeInput.dispatchEvent(new Event('input', { bubbles: true }));
            nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function adjustPosition() {
        panel.classList.remove('right-0', 'left-0', 'bottom-full', 'top-full', 'mb-1', 'mt-1');

        const rect = wrapper.getBoundingClientRect();
        const modalCard = wrapper.closest('[role="dialog"] > div, [id^="modal-"] > div') || document.body;
        const modalRect = modalCard.getBoundingClientRect();
        const panelWidth = 196;
        const panelHeight = 250;

        // Smart horizontal alignment:
        // If wrapper is in the right-side of the modal or near viewport edge, align right-0
        const distFromModalRight = modalRect.right - rect.right;
        const distFromModalLeft = rect.left - modalRect.left;

        if (rect.left + panelWidth > modalRect.right - 8 || rect.left + panelWidth > window.innerWidth - 12 || (distFromModalRight < distFromModalLeft && distFromModalRight < 120)) {
            panel.classList.add('right-0');
        } else {
            panel.classList.add('left-0');
        }

        // Smart vertical alignment:
        const spaceBelowInWindow = window.innerHeight - rect.bottom;
        const spaceBelowInModal = modalRect.bottom - rect.bottom;
        const spaceAboveInWindow = rect.top;

        if ((spaceBelowInModal < panelHeight || spaceBelowInWindow < panelHeight) && spaceAboveInWindow > panelHeight) {
            panel.classList.add('bottom-full', 'mb-1');
        } else {
            panel.classList.add('top-full', 'mt-1');
        }
    }

    function openPanel() {
        if (nativeInput.disabled) return;

        closeAllTimePickers();
        if (typeof window.closeAllDatePickers === 'function') {
            window.closeAllDatePickers();
        }

        const cur = parseTime(nativeInput.value || trigger.value || '08:00');
        currentHour = cur.hour;
        currentMinute = cur.minute;

        updateActiveStates();
        adjustPosition();

        panel.classList.remove('hidden');
        requestAnimationFrame(() => {
            panel.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            scrollToSelected();
        });

        activeTimePicker = {
            close: closePanel,
            wrapper: wrapper
        };
    }

    function closePanel() {
        if (panel.classList.contains('hidden')) return;

        panel.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');

        setTimeout(() => {
            if (!panel.classList.contains('is-open')) {
                panel.classList.add('hidden');
            }
        }, 160);

        if (activeTimePicker && activeTimePicker.wrapper === wrapper) {
            activeTimePicker = null;
        }
    }

    // Toggle on trigger or icon click
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        if (panel.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    iconBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (panel.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    // Selecting hours
    hoursList.addEventListener('click', (e) => {
        const btn = e.target.closest('.custom-hour-btn');
        if (!btn) return;
        e.stopPropagation();

        const selectedH = btn.getAttribute('data-hour');
        applyTimeToInput(selectedH, currentMinute, true);
    });

    // Selecting minutes
    minutesList.addEventListener('click', (e) => {
        const btn = e.target.closest('.custom-minute-btn');
        if (!btn) return;
        e.stopPropagation();

        const selectedM = btn.getAttribute('data-minute');
        applyTimeToInput(currentHour, selectedM, true);
    });

    // Preset buttons
    presetBtns.forEach(pBtn => {
        pBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const preset = pBtn.getAttribute('data-preset');
            if (preset) {
                const [h, m] = preset.split(':');
                applyTimeToInput(h, m, true);
            }
        });
    });

    // Done button
    if (doneBtn) {
        doneBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!hasValue) {
                applyTimeToInput(currentHour, currentMinute, true);
            }
            closePanel();
        });
    }

    // Keyboard navigation on trigger
    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePanel();
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (panel.classList.contains('is-open')) {
                closePanel();
            } else {
                openPanel();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            let h = (parseInt(currentHour, 10) + 1) % 24;
            applyTimeToInput(h, currentMinute, true);
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            let h = (parseInt(currentHour, 10) + 23) % 24;
            applyTimeToInput(h, currentMinute, true);
        }
    });

    // Sync when native input is modified programmatically
    nativeInput.addEventListener('change', () => {
        const newVal = nativeInput.value ? nativeInput.value.substring(0, 5) : '';
        if (newVal) {
            const parsed = parseTime(newVal);
            currentHour = parsed.hour;
            currentMinute = parsed.minute;
            trigger.value = `${currentHour}:${currentMinute}`;
            hasValue = true;
            updateActiveStates();
        }
    });

    updateActiveStates();
}

if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
        if (activeTimePicker && !activeTimePicker.wrapper.contains(e.target)) {
            closeAllTimePickers();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllTimePickers();
        }
    });
}

if (typeof window !== 'undefined') {
    window.initCustomTimePickers = initCustomTimePickers;
    window.closeAllTimePickers = closeAllTimePickers;
    window.enhanceTimePicker = enhanceTimePicker;
}
