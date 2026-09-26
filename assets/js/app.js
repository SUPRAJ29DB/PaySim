/**
 * PaySim - Global Application JavaScript
 */

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
}

function showToast(message, isSuccess = true) {
    const existing = document.getElementById('appDynamicToast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'appDynamicToast';
    toast.className = `flash-toast ${isSuccess ? 'flash-toast--success' : 'flash-toast--error'}`;
    toast.innerHTML = `
        <div class="flash-toast-icon">
            ${isSuccess ? '✓' : '✕'}
        </div>
        <div class="flash-toast-msg">${message}</div>
        <button class="flash-toast-close" onclick="this.parentElement.remove()">✕</button>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// Universal API request helper with JSON & CSRF support
async function apiRequest(url, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(options.headers || {})
    };

    // Include CSRF token if present
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
    }

    try {
        const response = await fetch(url, {
            ...options,
            headers
        });
        const data = await response.json();
        return data;
    } catch (err) {
        console.error('API Error:', err);
        return { success: false, message: 'Network or server communication failure.' };
    }
}
