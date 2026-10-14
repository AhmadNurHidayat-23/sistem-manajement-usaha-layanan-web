export function initNotificationBell() {
    const wrapper = document.getElementById('notification-bell-wrapper');
    const btn = document.getElementById('notification-bell-btn');
    const dropdown = document.getElementById('notification-bell-dropdown');
    const badge = document.getElementById('notification-bell-badge');
    const headerCount = document.getElementById('notification-header-count');
    const list = document.getElementById('notification-feed-list');
    const emptyState = document.getElementById('notification-feed-empty');
    const markAllBtn = document.getElementById('btn-mark-all-notifications');

    if (!wrapper || !btn || !dropdown) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let closeTimeout = null;
    let lastPollTime = Date.now();
    let hasLoadedFeed = false;

    function openDropdown() {
        if (closeTimeout) {
            clearTimeout(closeTimeout);
            closeTimeout = null;
        }
        dropdown.classList.remove('hidden');
        requestAnimationFrame(() => {
            dropdown.classList.add('is-open');
        });
        btn.setAttribute('aria-expanded', 'true');
        loadFeed();
    }

    function closeDropdown() {
        dropdown.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
        closeTimeout = setTimeout(() => {
            if (!dropdown.classList.contains('is-open')) {
                dropdown.classList.add('hidden');
            }
        }, 160);
    }

    // Toggle dropdown
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('is-open');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target) && dropdown.classList.contains('is-open')) {
            closeDropdown();
        }
    });

    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBadge(0);
                    if (list) {
                        list.querySelectorAll('.notification-item').forEach(el => {
                            el.classList.remove('bg-[#F0F5FF]');
                            el.classList.add('bg-white');
                            const dot = el.querySelector('.unread-dot');
                            if (dot) dot.remove();
                        });
                    }
                }
            })
            .catch(err => console.error('Error marking all as read:', err));
        });
    }

    function updateBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
            if (headerCount) {
                headerCount.textContent = `${count} Baru`;
                headerCount.classList.remove('hidden');
            }
        } else {
            badge.classList.add('hidden');
            if (headerCount) {
                headerCount.classList.add('hidden');
            }
        }
    }

    function renderNotifications(items) {
        if (!list) return;
        list.innerHTML = '';

        if (!items || items.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        items.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = `notification-item p-3.5 transition-colors flex gap-3 ${item.is_read ? 'bg-white' : 'bg-[#F0F5FF]'}`;
            itemDiv.id = `notif-${item.id}`;

            const badgeBg = item.status_level === 'success' ? 'bg-emerald-100 text-emerald-700' :
                           item.status_level === 'warning' ? 'bg-amber-100 text-amber-700' :
                           item.status_level === 'error' ? 'bg-rose-100 text-rose-700' :
                           'bg-blue-100 text-[#062072]';

            const actionHtml = item.action_url && item.action_label ? `
                <div class="mt-2 flex items-center justify-between gap-2">
                    <a href="${item.action_url}" class="notif-action-btn inline-flex items-center gap-1 px-2.5 py-1 bg-[#062072] hover:bg-[#04184F] text-white text-[11px] font-bold rounded shadow-2xs transition-colors" data-id="${item.id}">
                        ${item.action_label} &rarr;
                    </a>
                    ${!item.is_read ? `
                        <button type="button" class="mark-single-btn text-[10px] text-[#667085] hover:text-[#101828] cursor-pointer bg-transparent border-0 p-1" data-id="${item.id}" title="Tandai dibaca">
                            Tandai dibaca
                        </button>
                    ` : ''}
                </div>
            ` : `
                ${!item.is_read ? `
                    <div class="mt-1 flex justify-end">
                        <button type="button" class="mark-single-btn text-[10px] text-[#667085] hover:text-[#101828] cursor-pointer bg-transparent border-0 p-1" data-id="${item.id}" title="Tandai dibaca">
                            Tandai dibaca
                        </button>
                    </div>
                ` : ''}
            `;

            itemDiv.innerHTML = `
                <div class="w-8 h-8 rounded-full ${badgeBg} flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1.5">
                        <h5 class="text-xs font-bold text-[#101828] m-0 line-clamp-1">${item.title}</h5>
                        <span class="text-[10px] text-[#98A2B3] shrink-0">${item.created_at_human}</span>
                    </div>
                    <p class="text-[11px] text-[#475467] mt-0.5 m-0 leading-relaxed">${item.message}</p>
                    ${actionHtml}
                </div>
                ${!item.is_read ? '<span class="unread-dot w-2 h-2 rounded-full bg-[#062072] shrink-0 mt-1.5"></span>' : ''}
            `;

            list.appendChild(itemDiv);
        });

        // Event listener for action buttons
        list.querySelectorAll('.notif-action-btn').forEach(link => {
            link.addEventListener('click', () => {
                const id = link.getAttribute('data-id');
                if (id) markSingleAsRead(id);
            });
        });

        // Event listener for single mark as read
        list.querySelectorAll('.mark-single-btn').forEach(b => {
            b.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = b.getAttribute('data-id');
                if (id) markSingleAsRead(id);
            });
        });
    }

    function markSingleAsRead(id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const itemEl = document.getElementById(`notif-${id}`);
                if (itemEl) {
                    itemEl.classList.remove('bg-[#F0F5FF]');
                    itemEl.classList.add('bg-white');
                    const dot = itemEl.querySelector('.unread-dot');
                    if (dot) dot.remove();
                    const markBtn = itemEl.querySelector('.mark-single-btn');
                    if (markBtn) markBtn.remove();
                }
                const current = parseInt(badge?.textContent || '0', 10);
                if (current > 0) updateBadge(current - 1);
            }
        })
        .catch(err => console.error('Error marking as read:', err));
    }

    function loadFeed() {
        lastPollTime = Date.now();
        fetch('/notifications/feed', {
            headers: { 'Accept': 'application/json' },
        })
        .then(res => {
            if (!res.ok) throw new Error('Unauthorized');
            return res.json();
        })
        .then(data => {
            hasLoadedFeed = true;
            updateBadge(data.unread_count || 0);
            if (dropdown.classList.contains('is-open')) {
                renderNotifications(data.notifications || []);
            }
        })
        .catch(() => {});
    }

    // Polling cerdas (35 detik, berhenti jika tab tidak aktif)
    setInterval(() => {
        if (document.hidden || document.visibilityState === 'hidden') return;
        loadFeed();
    }, 35000);

    // Saat tab kembali aktif, refresh jika sudah lewat 30 detik
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Date.now() - lastPollTime > 30000) {
            loadFeed();
        }
    });
}
