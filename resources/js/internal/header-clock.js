/**
 * Real-time clock and date for SIMAUL Header.
 * Updates every second in Asia/Jakarta timezone with Indonesian locale.
 * Line 1: HH:mm:ss (24-hour format)
 * Line 2: Hari, DD MMMM YYYY (Indonesian date format)
 */
let clockInterval = null;

export function initHeaderClock() {
    const timeEl = document.getElementById('realtime-clock-time');
    const dateEl = document.getElementById('realtime-clock-date');

    if (!timeEl || !dateEl) {
        return;
    }

    function updateTimeAndDate() {
        const now = new Date();

        const timeFormatter = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });

        const dateFormatter = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });

        const timeStr = timeFormatter.format(now).replace(/\./g, ':');
        const dateStr = dateFormatter.format(now);

        if (timeEl.textContent !== timeStr) {
            timeEl.textContent = timeStr;
        }
        if (dateEl.textContent !== dateStr) {
            dateEl.textContent = dateStr;
        }
    }

    if (clockInterval) {
        clearInterval(clockInterval);
        clockInterval = null;
    }

    updateTimeAndDate();
    clockInterval = setInterval(updateTimeAndDate, 1000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            updateTimeAndDate();
        }
    });
}
