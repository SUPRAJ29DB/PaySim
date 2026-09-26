/**
 * PaySim - QR Code Scanning & Generation JavaScript
 */

function buildUpiUri(upiId, name, amount, note) {
    let uri = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=${encodeURIComponent(name)}&cu=INR`;
    if (amount && parseFloat(amount) > 0) {
        uri += `&am=${parseFloat(amount).toFixed(2)}`;
    }
    if (note) {
        uri += `&tn=${encodeURIComponent(note)}`;
    }
    return uri;
}

function parseUpiUri(uri) {
    if (!uri || !uri.startsWith('upi://pay?')) return null;
    const url = new URL(uri);
    const params = new URLSearchParams(url.search);
    return {
        upiId: params.get('pa') || '',
        name: params.get('pn') || '',
        amount: params.get('am') ? parseFloat(params.get('am')) : null,
        note: params.get('tn') || ''
    };
}
