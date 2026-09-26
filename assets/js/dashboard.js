/**
 * PaySim - Dashboard Interactions JavaScript
 */

function refreshBalance() {
    const balEl = document.getElementById('dashBalance');
    if (!balEl) return;

    balEl.style.opacity = '0.5';
    apiRequest('api/user/balance.php')
        .then(res => {
            if (res.success && res.data) {
                balEl.textContent = '₹' + parseFloat(res.data.balance).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        })
        .finally(() => {
            balEl.style.opacity = '1';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const refreshBtn = document.getElementById('refreshBalanceBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            refreshBalance();
        });
    }
});
