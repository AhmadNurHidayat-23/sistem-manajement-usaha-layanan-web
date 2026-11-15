/**
 * SIMAUL Compact 24-Hour Time Picker Component (HH:mm)
 * Compact, proportional, and responsive two-column time picker
 * designed specifically for modal and form layouts in SIMAUL.
 */

import {
    padZero,
    parseTime,
    buildHoursHtml,
    buildMinutesHtml,
    buildPickerHtml
} from './custom-time-picker-template.js';

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

export function enhanceTimePicker(nativeInput) {
    if (!nativeInput || nativeInput.dataset.customTimeInitialized === 'true') return;

    nativeInput.setAttribute('data-custom-time-initialized', 'true');
    nativeInput.classList.add('custom-time-hidden-native');

    const initialVal = nativeInput.value ? nativeInput.value.substring(0, 5) : '';
    let { hour: currentHour, minute: currentMinute } = parseTime(initialVal || '08:00');
    let hasValue = Boolean(initialVal);

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

    wrapper.innerHTML = buildPickerHtml({
        heightClass,
        displayValue,
        ariaLabel: nativeInput.getAttribute('aria-label'),
        disabled: nativeInput.disabled,
        required: nativeInput.required,
        currentHour,
        currentMinute
    });

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

    hoursList.innerHTML = buildHoursHtml();
    minutesList.innerHTML = buildMinutesHtml();

    function updateActiveStates() {
        hoursList.querySelectorAll('.custom-hour-btn').forEach(btn => {
            const h = btn.getAttribute('data-hour');
            const isActive = h === currentHour;
            btn.className = isActive
                ? 'custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-bold transition-colors cursor-pointer bg-[#062072] text-white shadow-2xs'
                : 'custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-medium text-[#101828] transition-colors cursor-pointer hover:bg-[#F5F8FC] hover:text-[#062072]';
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        minutesList.querySelectorAll('.custom-minute-btn').forEach(btn => {
            const m = btn.getAttribute('data-minute');
            const isActive = m === currentMinute;
            btn.className = isActive
                ? 'custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-bold transition-colors cursor-pointer bg-[#062072] text-white shadow-2xs'
                : 'custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-medium text-[#101828] transition-colors cursor-pointer hover:bg-[#F5F8FC] hover:text-[#062072]';
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (preview) {
            preview.textContent = `${currentHour}:${currentMinute}`;
        }
    }

    function scrollToSelected() {
        hoursList.querySelector(`[data-hour="${currentHour}"]`)?.scrollIntoView({ block: 'nearest' });
        minutesList.querySelector(`[data-minute="${currentMinute}"]`)?.scrollIntoView({ block: 'nearest' });
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

        const distFromModalRight = modalRect.right - rect.right;
        const distFromModalLeft = rect.left - modalRect.left;

        if (rect.left + panelWidth > modalRect.right - 8 || rect.left + panelWidth > window.innerWidth - 12 || (distFromModalRight < distFromModalLeft && distFromModalRight < 120)) {
            panel.classList.add('right-0');
        } else {
            panel.classList.add('left-0');
        }

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

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        if (panel.classList.contains('is-open')) closePanel();
        else openPanel();
    });

    iconBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (panel.classList.contains('is-open')) closePanel();
        else openPanel();
    });

    hoursList.addEventListener('click', (e) => {
        const btn = e.target.closest('.custom-hour-btn');
        if (!btn) return;
        e.stopPropagation();
        applyTimeToInput(btn.getAttribute('data-hour'), currentMinute, true);
    });

    minutesList.addEventListener('click', (e) => {
        const btn = e.target.closest('.custom-minute-btn');
        if (!btn) return;
        e.stopPropagation();
        applyTimeToInput(currentHour, btn.getAttribute('data-minute'), true);
    });

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

    if (doneBtn) {
        doneBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!hasValue) applyTimeToInput(currentHour, currentMinute, true);
            closePanel();
        });
    }

    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePanel();
        else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (panel.classList.contains('is-open')) closePanel();
            else openPanel();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            applyTimeToInput((parseInt(currentHour, 10) + 1) % 24, currentMinute, true);
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            applyTimeToInput((parseInt(currentHour, 10) + 23) % 24, currentMinute, true);
        }
    });

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
        if (e.key === 'Escape') closeAllTimePickers();
    });
}

if (typeof window !== 'undefined') {
    window.initCustomTimePickers = initCustomTimePickers;
    window.closeAllTimePickers = closeAllTimePickers;
    window.enhanceTimePicker = enhanceTimePicker;
}
