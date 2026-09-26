/**
 * PaySim - Transactions & Filtering Client Script
 */

function filterTransactions() {
    const searchVal = (document.getElementById('txnSearchInput')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('.txn-table tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchVal) ? '' : 'none';
    });
}

function printReceipt() {
    window.print();
}
