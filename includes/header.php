<?php
/**
 * PaySim - Global HTML Header Include
 */

if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

$pageTitle = $pageTitle ?? 'PaySim - UPI Payment Simulator';
$pageDesc  = $pageDesc ?? 'Next-Gen UPI Payment Simulator. Instant transfers, dynamic QR payments, and digital wallet.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo APP_URL . '/' . ltrim($cssFile, '/'); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>
    <?php require_once __DIR__ . '/flash-message.php'; ?>
