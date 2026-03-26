/**
 * public/assets/js/admin.js
 * CI4 Universal Boilerplate — Admin Panel JavaScript
 */

'use strict';

// ── CSRF Helper ────────────────────────────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Wrapper around fetch() that always sends CSRF header & JSON.
 */
async function apiRequest(url, method = 'GET', data = null) {
    const opts = {
        method,
        headers: {
            'Content-Type':  'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN':  CSRF_TOKEN,
        },
    };
    if (data && method !== 'GET') opts.body = JSON.stringify(data);

    const res  = await fetch(url, opts);
    const json = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(json.message ?? `HTTP ${res.status}`);
    return json;
}

// ── Sidebar ────────────────────────────────────────────────────
const sidebar        = document.getElementById('sidebar');
const mainWrapper    = document.getElementById('mainWrapper');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarClose   = document.getElementById('sidebarClose');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const SIDEBAR_KEY    = 'sidebarCollapsed';

function isMobile() { return window.innerWidth < 992; }

function openMobileSidebar() {
    sidebar?.classList.add('show');
    sidebarOverlay?.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
    sidebar?.classList.remove('show');
    sidebarOverlay?.classList.remove('show');
    document.body.style.overflow = '';
}

function toggleDesktopSidebar() {
    const collapsed = sidebar?.classList.toggle('collapsed');
    localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0');
}

sidebarToggle?.addEventListener('click', () => {
    isMobile() ? openMobileSidebar() : toggleDesktopSidebar();
});
sidebarClose?.addEventListener('click', closeMobileSidebar);
sidebarOverlay?.addEventListener('click', closeMobileSidebar);

// Restore sidebar state on desktop
if (!isMobile() && localStorage.getItem(SIDEBAR_KEY) === '1') {
    sidebar?.classList.add('collapsed');
}

// ── Dark Mode ──────────────────────────────────────────────────
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');
const THEME_KEY   = 'adminTheme';

function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    }
}

applyTheme(localStorage.getItem(THEME_KEY) ?? 'light');

themeToggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const next    = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, next);
    applyTheme(next);
});

// ── Notifications ──────────────────────────────────────────────
const notificationList = document.getElementById('notificationList');
const markAllReadBtn   = document.getElementById('markAllRead');

async function loadNotifications() {
    if (!notificationList) return;
    try {
        const res = await apiRequest('/api/v1/notifications');
        const items = res.data ?? [];
        if (!items.length) {
            notificationList.innerHTML = '<div class="text-center text-muted py-4 small">No notifications</div>';
            return;
        }
        notificationList.innerHTML = items.map(n => `
            <div class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                <div class="notif-title">${escHtml(n.title)}</div>
                <div class="text-muted mt-1">${escHtml(n.message)}</div>
                <div class="text-muted mt-1" style="font-size:.72rem">${timeAgo(n.created_at)}</div>
            </div>
        `).join('');

        notificationList.querySelectorAll('.notification-item').forEach(el => {
            el.addEventListener('click', async () => {
                const id = el.dataset.id;
                await apiRequest(`/api/v1/notifications/${id}/read`, 'POST');
                el.classList.remove('unread');
            });
        });
    } catch (e) {
        notificationList.innerHTML = '<div class="text-center text-danger py-3 small">Failed to load</div>';
    }
}

markAllReadBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    await apiRequest('/api/v1/notifications/read-all', 'POST');
    loadNotifications();
});

// Load on dropdown open
document.querySelector('[data-bs-toggle="dropdown"]')?.addEventListener('shown.bs.dropdown', loadNotifications);

// ── Confirm Delete ─────────────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        const msg = btn.dataset.confirm || 'Are you sure?';
        if (!confirm(msg)) e.preventDefault();
    });
});

// ── Auto-dismiss alerts ────────────────────────────────────────
document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert?.close();
    }, 5000);
});

// ── Search with debounce ───────────────────────────────────────
function debounce(fn, ms = 300) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

document.querySelectorAll('[data-search-form]').forEach(input => {
    input.addEventListener('input', debounce(() => {
        input.closest('form')?.submit();
    }, 400));
});

// ── Utilities ─────────────────────────────────────────────────
function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return 'just now';
    if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    return `${Math.floor(diff/86400)}d ago`;
}

// ── Toast helper ───────────────────────────────────────────────
window.showToast = function(message, type = 'success') {
    const id      = 'toast-' + Date.now();
    const colors  = { success: 'bg-success', danger: 'bg-danger', warning: 'bg-warning', info: 'bg-info' };
    const html    = `
        <div id="${id}" class="toast align-items-center text-white ${colors[type] ?? 'bg-primary'} border-0 show"
             role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body">${escHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    const toast   = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
};
