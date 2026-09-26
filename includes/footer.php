<?php
/**
 * PaySim - Global HTML Footer Include
 */
?>
    <footer class="app-footer">
        <div class="footer-inner">
            <p>&copy; <?php echo date('Y'); ?> PaySim. Simulated UPI Payment Platform. For Educational & Demonstration Purposes Only.</p>
            <div class="footer-links">
                <a href="<?php echo APP_URL; ?>/dashboard.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/send-money.php">Send Money</a>
                <a href="<?php echo APP_URL; ?>/scan-pay.php">Scan & Pay</a>
                <a href="<?php echo APP_URL; ?>/admin/login.php">Admin Portal</a>
            </div>
        </div>
    </footer>

    <!-- App JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?php echo APP_URL . '/' . ltrim($jsFile, '/'); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
