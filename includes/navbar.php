<?php
/**
 * PaySim - Top Navigation Bar Include
 */

$navUser = $_SESSION['NAME'] ?? 'User';
$navUpi  = $_SESSION['UPI_ID'] ?? 'user@paysim';
$navInitials = Helpers::getInitials($navUser);
$navColor = Helpers::getAvatarColor($navUser);
?>
<header class="top-nav">
    <div class="nav-left">
        <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <a href="<?php echo APP_URL; ?>/dashboard.php" class="brand-logo">
            <div class="brand-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="brand-name">PaySim</span>
            <span class="brand-badge">UPI 2.0</span>
        </a>
    </div>

    <div class="nav-right">
        <a href="<?php echo APP_URL; ?>/notifications.php" class="nav-action-btn" title="Notifications">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="badge-dot"></span>
        </a>

        <div class="user-profile-menu">
            <a href="<?php echo APP_URL; ?>/profile.php" class="user-chip">
                <div class="user-avatar" style="background: <?php echo $navColor; ?>">
                    <?php echo htmlspecialchars($navInitials); ?>
                </div>
                <div class="user-meta">
                    <span class="user-name"><?php echo htmlspecialchars($navUser); ?></span>
                    <span class="user-upi"><?php echo htmlspecialchars($navUpi); ?></span>
                </div>
            </a>
            <a href="<?php echo APP_URL; ?>/logout.php" class="logout-icon-btn" title="Sign Out">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</header>
