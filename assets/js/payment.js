/**
 * PaySim - Payment Flows & PIN Keypad JavaScript
 */

let pinBuffer = '';

function pressPin(digit) {
    if (pinBuffer.length >= 6) return;
    pinBuffer += digit;
    updatePinDots();
    if (pinBuffer.length === 6) {
        if (typeof onPinComplete === 'function') {
            onPinComplete(pinBuffer);
        }
    }
}

function deletePin() {
    pinBuffer = pinBuffer.slice(0, -1);
    updatePinDots();
}

function clearPin() {
    pinBuffer = '';
    updatePinDots();
}

function updatePinDots() {
    for (let i = 0; i < 6; i++) {
        const dot = document.getElementById('pinDot' + i);
        if (dot) {
            dot.classList.toggle('filled', i < pinBuffer.length);
        }
    }
}
