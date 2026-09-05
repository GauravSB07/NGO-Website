<?php
/**
 * Shared Executive Admin Navbar - Sevartha Foundation
 * 
 * Configurable parameters prior to inclusion:
 * @var string $activeNav 'dashboard' | 'donations' | 'events' | 'categories' | 'volunteers' | 'contact'
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compute base URLs dynamically
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$adminPos = stripos($scriptName, '/admin');

if ($adminPos !== false) {
    $siteBase = substr($scriptName, 0, $adminPos);
    $adminBase = $siteBase . '/admin';
} else {
    // Relative fallback if accessed without standard URL routing
    $siteBase = '/NGO-Website';
    $adminBase = '/NGO-Website/admin';
}

$currentNav = $activeNav ?? '';

// Calculate pending donations count for badge
$navPendingCount = 0;
if (isset($conn) && $conn instanceof mysqli) {
    $navRes = @mysqli_query($conn, "SELECT COUNT(*) AS total_pending FROM donations WHERE payment_status = 'pending'");
    if ($navRes && ($navRow = @mysqli_fetch_assoc($navRes))) {
        $navPendingCount = (int)($navRow['total_pending'] ?? 0);
    }
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(substr(trim($adminName), 0, 1));
if ($adminInitial === '') {
    $adminInitial = 'A';
}
?>
<!-- =========================================================
     ADMIN NAVBAR (ALIGNED WITH MAIN WEBSITE NAVBAR)
     Dark Charcoal (#1A1A1A) with Gold/Sand Accent Trim
========================================================== -->
<nav class="admin-navbar">
    <div class="container-fluid px-4">

        <!-- BRAND & LOGO (MATCHING MAIN WEBSITE) -->
        <a class="admin-brand" href="<?= $adminBase; ?>/dashboard.php" title="Sevartha Admin Dashboard">
            <img src="<?= $siteBase; ?>/static_image.php?name=logo.png" alt="Sevartha Foundation Logo" class="admin-logo">
            <span class="admin-brand-text">
                <span class="brand-title">Sevartha</span>
                <span class="brand-subtitle">Foundation</span>
            </span>
            <span class="admin-portal-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Admin Console
            </span>
        </a>

        <!-- MODULE NAVIGATION LINKS -->
        <ul class="admin-nav-menu">
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/dashboard.php" class="admin-nav-link <?= ($currentNav === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/donations/index.php" class="admin-nav-link <?= ($currentNav === 'donations') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Donations</span>
                    <?php if ($navPendingCount > 0): ?>
                        <span class="admin-nav-badge"><?= $navPendingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/events/index.php" class="admin-nav-link <?= ($currentNav === 'events') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Events</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/categories/index.php" class="admin-nav-link <?= ($currentNav === 'categories') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/volunteers/index.php" class="admin-nav-link <?= ($currentNav === 'volunteers') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Volunteers</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $adminBase; ?>/contact/index.php" class="admin-nav-link <?= ($currentNav === 'contact') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Inquiries</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= $siteBase; ?>/index.php" target="_blank" class="admin-nav-link" title="Open Public Website in New Tab">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Website</span>
                </a>
            </li>
        </ul>

        <!-- USER CHIP & LOGOUT -->
        <div class="admin-user">
            <div class="admin-user-pill">
                <div class="admin-avatar-dot">
                    <?= $adminInitial; ?>
                    <span class="online-indicator"></span>
                </div>
                <span><?= htmlspecialchars($adminName); ?></span>
            </div>

            <a href="<?= $adminBase; ?>/logout.php" class="admin-logout" title="Sign out of Admin Console">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>

    </div>
</nav>
