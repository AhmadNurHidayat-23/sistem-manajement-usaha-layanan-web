/**
 * Markup and Helper utilities for Custom Time Picker
 */

export function padZero(num) {
    return String(num).padStart(2, '0');
}

export function parseTime(val) {
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

export function buildHoursHtml() {
    let hoursHtml = '';
    for (let h = 0; h < 24; h++) {
        const hStr = padZero(h);
        hoursHtml += `<button type="button" data-hour="${hStr}" role="option" aria-selected="false" class="custom-hour-btn w-full h-6 flex items-center justify-center rounded text-xs font-semibold transition-colors cursor-pointer">${hStr}</button>`;
    }
    return hoursHtml;
}

export function buildMinutesHtml() {
    let minutesHtml = '';
    for (let m = 0; m < 60; m++) {
        const mStr = padZero(m);
        minutesHtml += `<button type="button" data-minute="${mStr}" role="option" aria-selected="false" class="custom-minute-btn w-full h-6 flex items-center justify-center rounded text-xs font-semibold transition-colors cursor-pointer">${mStr}</button>`;
    }
    return minutesHtml;
}

export function buildPickerHtml(options) {
    const { heightClass, displayValue, ariaLabel, disabled, required, currentHour, currentMinute } = options;
    return `
        <div class="relative flex items-center w-full">
            <input type="text"
                   readonly
                   inputmode="numeric"
                   placeholder="HH:mm"
                   value="${displayValue}"
                   class="custom-time-trigger w-full ${heightClass} pl-3 pr-8 bg-white border border-[#D0D5DD] rounded-lg text-xs sm:text-[13px] font-semibold text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:border-[#062072] focus:ring-1 focus:ring-[#062072] transition-colors cursor-pointer select-none ${disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : ''}"
                   aria-haspopup="dialog"
                   aria-expanded="false"
                   aria-label="${ariaLabel || 'Pilih Waktu (Jam:Menit)'}"
                   ${disabled ? 'disabled' : ''}
                   ${required ? 'aria-required="true"' : ''}>
            <button type="button"
                    tabindex="-1"
                    aria-label="Buka pilihan jam dan menit"
                    class="custom-time-icon-btn absolute right-2 top-1/2 -translate-y-1/2 text-[#667085] hover:text-[#062072] transition-colors cursor-pointer p-1 rounded">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
        </div>
        <div class="custom-time-panel absolute bg-white border border-[#E4E7EC] rounded-xl shadow-xl p-2.5 z-[70] w-48 sm:w-[196px] hidden select-none"
             role="dialog"
             aria-modal="false"
             aria-label="Pilihan Waktu 24 Jam">
            <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-[#E4E7EC]">
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="custom-time-preview font-bold text-xs text-[#062072] bg-[#EBF2FE] px-1.5 py-0.5 rounded font-mono">
                        ${currentHour}:${currentMinute}
                    </span>
                </div>
                <span class="text-[10px] font-semibold text-[#475467] bg-slate-100 px-1 py-0.5 rounded">24 Jam</span>
            </div>
            <div class="grid grid-cols-2 gap-1.5 mb-1.5">
                <div>
                    <div class="text-[10px] font-bold text-[#667085] uppercase tracking-wider mb-1 text-center bg-[#F8FAFC] py-0.5 rounded border border-[#E4E7EC]/60">Jam</div>
                    <div class="custom-time-hours-list h-36 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar" role="listbox" aria-label="Daftar Jam"></div>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-[#667085] uppercase tracking-wider mb-1 text-center bg-[#F8FAFC] py-0.5 rounded border border-[#E4E7EC]/60">Menit</div>
                    <div class="custom-time-minutes-list h-36 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar" role="listbox" aria-label="Daftar Menit"></div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-1 pt-1.5 border-t border-[#E4E7EC]">
                <button type="button" data-preset="08:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">08:00</button>
                <button type="button" data-preset="12:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">12:00</button>
                <button type="button" data-preset="16:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">16:00</button>
                <button type="button" data-preset="17:00" class="custom-time-preset py-0.5 text-[10px] font-bold text-[#062072] bg-[#F5F8FC] hover:bg-[#EBF2FE] rounded text-center transition-colors cursor-pointer border border-[#E4E7EC]/80">17:00</button>
            </div>
            <button type="button" class="custom-time-done-btn w-full mt-1.5 py-1 text-xs font-bold text-white bg-[#062072] hover:bg-[#04184F] rounded-lg transition-colors cursor-pointer shadow-2xs">Selesai</button>
        </div>
    `;
}

