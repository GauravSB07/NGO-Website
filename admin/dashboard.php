<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   ADMIN AUTHENTICATION (UNMODIFIED)
========================================================= */

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include __DIR__ . "/../config/db.php";

/* =========================================================
   DATABASE QUERIES (100% UNMODIFIED LOGIC)
========================================================= */

// 1. Total Events
$eventQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events");
$eventData = mysqli_fetch_assoc($eventQuery);
$totalEvents = $eventData['total'] ?? 0;

// 2. Total Categories
$categoryQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
$categoryData = mysqli_fetch_assoc($categoryQuery);
$totalCategories = $categoryData['total'] ?? 0;

// 3. Total Contact Messages
$contactQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages");
$totalContacts = 0;
if ($contactQuery) {
    $contactData = mysqli_fetch_assoc($contactQuery);
    $totalContacts = $contactData['total'] ?? 0;
}

// 4. Total Volunteer Applications
$volunteerQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM volunteer_applications");
$totalVolunteers = 0;
if ($volunteerQuery) {
    $volunteerData = mysqli_fetch_assoc($volunteerQuery);
    $totalVolunteers = $volunteerData['total'] ?? 0;
}

// 5. Pending Donations
$donationQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donations WHERE payment_status = 'pending'");
$totalDonations = 0;
if ($donationQuery) {
    $donationData = mysqli_fetch_assoc($donationQuery);
    $totalDonations = $donationData['total'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Admin Dashboard | Sevartha Foundation</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../css/admin/admin.css?v=<?= time(); ?>">
</head>

<body class="admin-dashboard">

<?php
$activeNav = 'dashboard';
include __DIR__ . '/includes/navbar.php';
?>

<!-- =========================================================
     DASHBOARD MAIN CONTAINER
========================================================== -->
<main class="admin-container">

    <!-- DASHBOARD HEADER -->
    <div class="admin-header">
        <div class="admin-header-left">
            <span class="admin-tagline">Administrative Control Center</span>
            <h1>Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h1>
            <p>Monitor real-time donations, manage causes, review volunteer applications, and coordinate community initiatives.</p>
        </div>

        <div class="admin-header-actions">
            <div class="admin-live-pill">
                <span class="pulse-dot"></span>
                <span>System Operational • <?= date('M Y'); ?></span>
            </div>

            <a href="../index.php" target="_blank" class="admin-btn-secondary">
                <i class="fa-solid fa-globe me-1"></i>
                Public Website
            </a>
        </div>
    </div>

    <!-- =====================================================
         KPI CARDS GRID
    ====================================================== -->
    <div class="row g-4">

        <!-- 1. DONATIONS & VERIFICATION (FEATURED) -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card <?= ($totalDonations > 0) ? 'admin-card-featured' : ''; ?>">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Donations</h3>
                            <p class="admin-stat-label">Pending Verification</p>
                            <div class="admin-stat-number" style="<?= ($totalDonations > 0) ? 'color: #D97706;' : ''; ?>">
                                <?= $totalDonations; ?>
                            </div>
                        </div>

                        <div class="admin-card-icon" style="<?= ($totalDonations > 0) ? 'background: #FEF3C7; color: #B45309; border-color: #FCD34D;' : ''; ?>">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>

                    <p>Review donor payments and automatically generate official certificates of appreciation.</p>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <a href="donations/index.php" class="admin-btn-primary flex-grow-1">
                        <i class="fa-solid fa-certificate"></i>
                        Verify Donations
                    </a>
                    <a href="donations/certificate-settings.php" class="admin-btn-secondary" title="Customize Certificate Format & Structure">
                        <i class="fa-solid fa-award"></i>
                    </a>
                    <a href="donations/email-settings.php" class="admin-btn-secondary" title="Email &amp; SMTP Settings">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. EVENTS & DRIVES -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Events &amp; Drives</h3>
                            <p class="admin-stat-label">Total Events</p>
                            <div class="admin-stat-number">
                                <?= $totalEvents; ?>
                            </div>
                        </div>

                        <div class="admin-card-icon">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>

                    <p>Schedule, publish, and showcase community relief and empowerment events.</p>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="events/add.php" class="admin-btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Add Event
                    </a>
                    <a href="events/index.php" class="admin-btn-secondary flex-grow-1">
                        Manage Drives
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. WORK CATEGORIES -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Work Categories</h3>
                            <p class="admin-stat-label">Core Pillars</p>
                            <div class="admin-stat-number">
                                <?= $totalCategories; ?>
                            </div>
                        </div>

                        <div class="admin-card-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                    </div>

                    <p>Education, Nutrition, Healthcare, Elders Care, and Disaster Relief programs.</p>
                </div>

                <div class="mt-4">
                    <a href="categories/index.php" class="admin-btn-secondary w-100">
                        <i class="fa-solid fa-sliders me-1"></i>
                        Manage Categories
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. VOLUNTEER APPLICATIONS -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Volunteers</h3>
                            <p class="admin-stat-label">Applications</p>
                            <div class="admin-stat-number">
                                <?= $totalVolunteers; ?>
                            </div>
                        </div>

                        <div class="admin-card-icon">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                    </div>

                    <p>Applications submitted by compassionate individuals eager to serve.</p>
                </div>

                <div class="mt-4">
                    <a href="volunteers/index.php" class="admin-btn-secondary w-100">
                        <i class="fa-solid fa-users me-1"></i>
                        View Applications
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. CONTACT MESSAGES -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Contact Inquiries</h3>
                            <p class="admin-stat-label">Total Messages</p>
                            <div class="admin-stat-number">
                                <?= $totalContacts; ?>
                            </div>
                        </div>

                        <div class="admin-card-icon">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                    </div>

                    <p>General inquiries, collaboration proposals, and feedback from the public.</p>
                </div>

                <div class="mt-4">
                    <a href="contact/index.php" class="admin-btn-secondary w-100">
                        <i class="fa-solid fa-eye me-1"></i>
                        View Messages
                    </a>
                </div>
            </div>
        </div>

        <!-- 6. PUBLIC PORTAL LAUNCHPAD -->
        <div class="col-md-6 col-lg-4">
            <div class="admin-card">
                <div>
                    <div class="admin-stat">
                        <div>
                            <h3>Public Portal</h3>
                            <p class="admin-stat-label">Live Site</p>
                            <div class="admin-stat-number" style="font-size: 1.8rem; line-height: 1.4;">
                                Active
                            </div>
                        </div>

                        <div class="admin-card-icon">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                    </div>

                    <p>Preview live website changes, donation flow, and story presentations.</p>
                </div>

                <div class="mt-4">
                    <a href="../index.php" target="_blank" class="admin-btn-secondary w-100">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                        Open Website
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- =====================================================
         QUICK COMMAND LAUNCHPAD BAR
    ====================================================== -->
    <div class="admin-quick-toolbar">
        <div class="admin-quick-title">
            <i class="fa-solid fa-bolt-lightning"></i>
            <span>Quick Management Shortcuts</span>
        </div>

        <div class="admin-quick-pills">
            <a href="donations/index.php" class="admin-quick-btn">
                <i class="fa-solid fa-check-double"></i>
                Verify Donations
                <?php if ($totalDonations > 0): ?>
                    <span class="badge bg-warning text-dark ms-1"><?= $totalDonations; ?></span>
                <?php endif; ?>
            </a>

            <a href="donations/certificate-settings.php" class="admin-quick-btn">
                <i class="fa-solid fa-award"></i>
                Certificate Template Editor
            </a>

            <a href="donations/email-settings.php" class="admin-quick-btn">
                <i class="fa-solid fa-envelope-circle-check"></i>
                Gmail SMTP Settings
            </a>

            <a href="events/add.php" class="admin-quick-btn">
                <i class="fa-solid fa-circle-plus"></i>
                Publish New Event
            </a>

            <a href="volunteers/index.php" class="admin-quick-btn">
                <i class="fa-solid fa-user-check"></i>
                Review Volunteers (<?= $totalVolunteers; ?>)
            </a>

            <a href="contact/index.php" class="admin-quick-btn">
                <i class="fa-solid fa-inbox"></i>
                Check Inquiries (<?= $totalContacts; ?>)
            </a>

            <a href="categories/index.php" class="admin-quick-btn">
                <i class="fa-solid fa-tags"></i>
                Cause Categories
            </a>
        </div>
    </div>

</main>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>