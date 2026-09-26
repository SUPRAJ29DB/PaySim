<?php
/**
 * PaySim - Flash Message Display Component Include
 */

require_once dirname(__DIR__) . '/utils/Helpers.php';
$flash = Helpers::getFlash();
?>
<?php if ($flash): ?>
<div class="flash-toast flash-toast--<?php echo htmlspecialchars($flash['type']); ?>" id="flashToast">
    <div class="flash-toast-icon">
        <?php if ($flash['type'] === 'success'): ?>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <?php elseif ($flash['type'] === 'error'): ?>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <?php else: ?>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php endif; ?>
    </div>
    <div class="flash-toast-msg"><?php echo htmlspecialchars($flash['message']); ?></div>
    <button class="flash-toast-close" onclick="document.getElementById('flashToast').remove()">✕</button>
</div>
<script>
    setTimeout(function() {
        var el = document.getElementById('flashToast');
        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-20px)';
            setTimeout(function() { el.remove(); }, 400);
        }
    }, 4000);
</script>
<?php endif; ?>
