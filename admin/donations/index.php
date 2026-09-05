<?php

/* =========================================================
   SESSION & AUTHENTICATION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   DATABASE CONNECTION & INCLUDES
========================================================= */

include __DIR__ . "/../../config/db.php";
include __DIR__ . "/../../includes/mailer.php";
include __DIR__ . "/../../includes/certificate.php";

$successMessage = '';
$errorMessage = '';

/* =========================================================
   HANDLE DIRECT PDF DOWNLOAD (GET REQUEST)
========================================================= */

if (isset($_GET['action']) && $_GET['action'] === 'download_pdf' && isset($_GET['id'])) {
    $donId = (int) $_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM donations WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $donId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $don = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($don) {
            $pdf = SevarthaCertificate::generatePdf($don);
            $certNum = SevarthaCertificate::getCertificateNumber($donId);
            $filename = "Sevartha_Certificate_{$certNum}.pdf";

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $pdf;
            exit;
        }
    }
}

/* =========================================================
   HANDLE ACTIONS: APPROVE / RESEND / REJECT
========================================================= */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' &&
    isset($_POST['action']) &&
    isset($_POST['donation_id'])
) {
    $donationId = (int) $_POST['donation_id'];
    $action = $_POST['action'];

    /* ---------------------------------------------------------
       1. APPROVE & SEND CERTIFICATE
    --------------------------------------------------------- */
    if ($action === 'approve') {
        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE donations
            SET
                payment_status = 'completed',
                updated_at = CURRENT_TIMESTAMP
            WHERE
                id = ?
                AND payment_status = 'pending'
            "
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $donationId);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    mysqli_stmt_close($stmt);

                    // Fetch updated donation record
                    $fetchStmt = mysqli_prepare($conn, "SELECT * FROM donations WHERE id = ? LIMIT 1");
                    mysqli_stmt_bind_param($fetchStmt, "i", $donationId);
                    mysqli_stmt_execute($fetchStmt);
                    $donRes = mysqli_stmt_get_result($fetchStmt);
                    $donationRow = mysqli_fetch_assoc($donRes);
                    mysqli_stmt_close($fetchStmt);

                    if ($donationRow) {
                        $pdfBytes = SevarthaCertificate::generatePdf($donationRow);
                        $certNumber = SevarthaCertificate::getCertificateNumber($donationId);
                        $token = SevarthaCertificate::getSecurityToken($donationId, $donationRow['donor_email']);

                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $webCertUrl = "{$scheme}://{$host}/NGO-Website/donate/certificate.php?id={$donationId}&token={$token}";

                        $mailer = SevarthaMailer::loadSettings($conn);
                        if ($mailer->isConfigured()) {
                            if ($mailer->sendDonationVerification($donationRow, $certNumber, $pdfBytes, $webCertUrl)) {
                                $successMessage = "Donation #{$donationId} verified! Official Certificate ({$certNumber}) was generated and emailed to " . htmlspecialchars($donationRow['donor_email']) . ".";
                            } else {
                                $successMessage = "Donation #{$donationId} verified successfully! However, the email could not be sent: " . htmlspecialchars($mailer->getLastError()) . ". You can check Email Settings or resend anytime.";
                            }
                        } else {
                            $successMessage = "Donation #{$donationId} verified successfully! (Note: Email was not sent because Gmail SMTP is not configured yet. Configure it in <a href='email-settings.php' class='alert-link'>Email Settings</a> or download the certificate directly).";
                        }
                    } else {
                        $successMessage = "Donation #{$donationId} approved successfully.";
                    }
                } else {
                    mysqli_stmt_close($stmt);
                    $errorMessage = "Donation could not be approved. It may already have been processed.";
                }
            } else {
                $errorMessage = "Database error while approving donation: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $errorMessage = "Unable to prepare approval request: " . mysqli_error($conn);
        }
    }

    /* ---------------------------------------------------------
       2. RESEND CERTIFICATE EMAIL (FOR COMPLETED DONATIONS)
    --------------------------------------------------------- */
    elseif ($action === 'resend_certificate') {
        $fetchStmt = mysqli_prepare($conn, "SELECT * FROM donations WHERE id = ? LIMIT 1");
        if ($fetchStmt) {
            mysqli_stmt_bind_param($fetchStmt, "i", $donationId);
            mysqli_stmt_execute($fetchStmt);
            $donRes = mysqli_stmt_get_result($fetchStmt);
            $donationRow = mysqli_fetch_assoc($donRes);
            mysqli_stmt_close($fetchStmt);

            if ($donationRow && $donationRow['payment_status'] === 'completed') {
                $pdfBytes = SevarthaCertificate::generatePdf($donationRow);
                $certNumber = SevarthaCertificate::getCertificateNumber($donationId);
                $token = SevarthaCertificate::getSecurityToken($donationId, $donationRow['donor_email']);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $webCertUrl = "{$scheme}://{$host}/NGO-Website/donate/certificate.php?id={$donationId}&token={$token}";

                $mailer = SevarthaMailer::loadSettings($conn);
                if ($mailer->isConfigured()) {
                    if ($mailer->sendDonationVerification($donationRow, $certNumber, $pdfBytes, $webCertUrl)) {
                        $successMessage = "Certificate ({$certNumber}) successfully re-sent to " . htmlspecialchars($donationRow['donor_email']) . ".";
                    } else {
                        $errorMessage = "Unable to send email: " . htmlspecialchars($mailer->getLastError());
                    }
                } else {
                    $errorMessage = "Gmail SMTP is not configured. Please configure it in <a href='email-settings.php' class='alert-link'>Email Settings</a> first.";
                }
            } else {
                $errorMessage = "Donation must be verified before a certificate can be emailed.";
            }
        }
    }

    /* ---------------------------------------------------------
       3. REJECT DONATION
    --------------------------------------------------------- */
    elseif ($action === 'reject') {
        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE donations
            SET
                payment_status = 'failed',
                updated_at = CURRENT_TIMESTAMP
            WHERE
                id = ?
                AND payment_status = 'pending'
            "
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $donationId);
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $successMessage = "Donation rejected successfully.";
                } else {
                    $errorMessage = "Donation could not be rejected. It may already have been processed.";
                }
            } else {
                $errorMessage = "Database error while rejecting donation: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = "Unable to prepare rejection request: " . mysqli_error($conn);
        }
    }
}

/* =========================================================
   FILTERS & SEARCH
========================================================= */

$statusFilter = trim($_GET['status'] ?? 'all');
$searchQuery = trim($_GET['search'] ?? '');

$whereClauses = [];
$params = [];
$types = '';

if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'completed', 'failed', 'cancelled'], true)) {
    $whereClauses[] = "payment_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($searchQuery !== '') {
    $searchTerm = "%{$searchQuery}%";
    $whereClauses[] = "(donor_name LIKE ? OR donor_email LIKE ? OR transaction_id LIKE ? OR donation_purpose LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

/* =========================================================
   LOAD DONATIONS
========================================================= */

$donations = [];
$sql = "
    SELECT
        id,
        donor_name,
        donor_email,
        donor_phone,
        donation_purpose,
        donation_amount,
        payment_status,
        payment_submitted_at,
        payment_method,
        transaction_id,
        created_at,
        updated_at
    FROM donations
    {$whereSql}
    ORDER BY
        CASE
            WHEN payment_status = 'pending' THEN 1
            WHEN payment_status = 'completed' THEN 2
            WHEN payment_status = 'failed' THEN 3
            ELSE 4
        END,
        COALESCE(payment_submitted_at, created_at) DESC
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $donations[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $query = mysqli_query($conn, $sql);
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $donations[] = $row;
        }
    }
}

/* =========================================================
   SUMMARY METRICS
========================================================= */

$pendingCount = 0;
$completedCount = 0;
$totalAmount = 0.0;
$totalCount = 0;

$statsQuery = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total_donations,
        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
        SUM(CASE WHEN payment_status = 'completed' THEN donation_amount ELSE 0 END) AS sum_completed
    FROM donations
");

if ($statsQuery && ($s = mysqli_fetch_assoc($statsQuery))) {
    $totalCount = (int) ($s['total_donations'] ?? 0);
    $pendingCount = (int) ($s['total_pending'] ?? 0);
    $completedCount = (int) ($s['total_completed'] ?? 0);
    $totalAmount = (float) ($s['sum_completed'] ?? 0);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations &amp; Verification | Sevartha Foundation Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin/admin.css?v=<?= time(); ?>">

    <style>
        .donation-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .donation-summary-card {
            position: relative;
            background: #ffffff;
            border-radius: 18px;
            padding: 24px 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: var(--adm-shadow-md);
            border: 1px solid rgba(84, 82, 71, 0.13);
            overflow: hidden;
            transition: var(--adm-transition);
        }

        .donation-summary-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--adm-sand), var(--adm-olive));
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }

        .donation-summary-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--adm-shadow-lift);
            border-color: rgba(208, 200, 182, 0.6);
        }

        .donation-summary-card:hover::before {
            opacity: 1;
            height: 4px;
            background: linear-gradient(90deg, var(--adm-gold), var(--adm-olive-dark));
        }

        .donation-summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.7);
            transition: var(--adm-transition);
        }

        .donation-summary-card:hover .donation-summary-icon {
            transform: scale(1.06) rotate(-2deg);
        }

        .pending-icon { background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; }
        .completed-icon { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }
        .total-icon { background: var(--adm-sand-soft); color: var(--adm-olive-dark); border: 1px solid var(--adm-sand); }
        .amount-icon { background: #F4EFE6; color: #545247; border: 1px solid #D0C8B6; }

        .donation-summary-card span {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            color: #716D60;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .donation-summary-card strong {
            display: block;
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--adm-dark);
            line-height: 1.1;
        }

        /* Filter & Search Bar */
        .filter-bar {
            background: #ffffff;
            border: 1px solid var(--adm-border);
            border-radius: 16px;
            padding: 16px 22px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: var(--adm-shadow-sm);
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 18px;
            border-radius: var(--adm-radius-pill);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            color: #6C685B;
            background: var(--adm-sand-soft);
            border: 1px solid rgba(208, 200, 182, 0.4);
            transition: var(--adm-transition);
        }

        .filter-tab:hover {
            background: var(--adm-dark);
            border-color: var(--adm-dark);
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .filter-tab.active {
            background: var(--adm-dark);
            border-color: var(--adm-dark);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(26, 26, 26, 0.15);
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-input {
            padding: 9px 16px;
            border: 1px solid #D8D4CA;
            border-radius: var(--adm-radius-pill);
            font-size: 13.5px;
            min-width: 280px;
            background: #FFFFFF;
            transition: var(--adm-transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--adm-olive);
            box-shadow: 0 0 0 3px rgba(84, 82, 71, 0.12);
        }

        /* Table */
        .donation-table-wrapper {
            background: #ffffff;
            border-radius: var(--adm-radius-lg);
            padding: 24px;
            box-shadow: var(--adm-shadow-md);
            border: 1px solid var(--adm-border);
            overflow-x: auto;
        }

        .donation-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
        }

        .donation-table th {
            text-align: left;
            padding: 15px 18px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--adm-dark);
            background: var(--adm-sand-soft);
            border-bottom: 1px solid rgba(84, 82, 71, 0.15);
            white-space: nowrap;
        }

        .donation-table td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(84, 82, 71, 0.08);
            vertical-align: middle;
            font-size: 14px;
            color: #3E3C36;
        }

        .donation-table tbody tr {
            transition: background 0.18s ease;
        }

        .donation-table tbody tr:hover {
            background: var(--adm-off-white-hover);
        }

        .donor-name { font-weight: 700; color: var(--adm-dark); font-size: 14.5px; }
        .donor-email { font-size: 12px; color: #6C685B; margin-top: 2px; }
        .donation-amount { font-family: Georgia, serif; font-weight: 800; color: var(--adm-dark); white-space: nowrap; font-size: 15px; }

        .transaction-id {
            font-family: monospace;
            font-size: 12.5px;
            background: var(--adm-sand-soft);
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid rgba(208, 200, 182, 0.6);
            color: var(--adm-dark);
            white-space: nowrap;
            display: inline-block;
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: var(--adm-radius-pill);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .payment-status.pending { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
        .payment-status.completed { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
        .payment-status.failed { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        .donation-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .donation-action-btn {
            border: none;
            border-radius: var(--adm-radius-pill);
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--adm-transition);
        }

        .approve-btn {
            background: #166534;
            color: #ffffff;
            border: 1px solid #166534;
            box-shadow: 0 2px 8px rgba(22, 101, 52, 0.25);
        }
        .approve-btn:hover {
            background: #14532D;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.35);
        }

        .reject-btn {
            background: #DC2626;
            color: #ffffff;
            border: 1px solid #DC2626;
        }
        .reject-btn:hover { background: #B91C1C; color: #fff; transform: translateY(-1px); }

        .pdf-btn {
            background: var(--adm-dark);
            color: #ffffff;
            border: 1px solid var(--adm-dark);
        }
        .pdf-btn:hover {
            background: var(--adm-olive);
            border-color: var(--adm-olive);
            color: #fff;
            transform: translateY(-1px);
        }

        .view-btn {
            background: #ffffff;
            color: var(--adm-dark);
            border: 1px solid rgba(84, 82, 71, 0.22);
        }
        .view-btn:hover {
            background: var(--adm-sand-soft);
            color: var(--adm-dark);
            transform: translateY(-1px);
        }

        .resend-btn {
            background: var(--adm-sand-soft);
            color: var(--adm-dark);
            border: 1px solid rgba(208, 200, 182, 0.6);
        }
        .resend-btn:hover {
            background: var(--adm-dark);
            border-color: var(--adm-dark);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .top-action-bar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        @media (max-width: 992px) {
            .donation-summary { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 576px) {
            .donation-summary { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .search-input { width: 100%; min-width: 0; }
        }
    </style>
</head>

<body class="admin-dashboard">

<?php
$activeNav = 'donations';
include __DIR__ . '/../includes/navbar.php';
?>

<main class="admin-container">

    <!-- HEADER & QUICK ACTIONS -->
    <div class="admin-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1>Donations &amp; Verification</h1>
            <p>Review donor payments, verify transactions, and automatically generate/email certificates.</p>
        </div>

        <div class="top-action-bar">
            <a href="certificate-settings.php" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="fa-solid fa-award me-1"></i> Certificate Template
            </a>
            <a href="email-settings.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-envelope me-1"></i> Email &amp; SMTP Settings
            </a>
            <a href="payment-settings.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-qrcode me-1"></i> UPI &amp; QR Settings
            </a>
            <a href="../dashboard.php" class="admin-btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success admin-alert alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger admin-alert alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- SUMMARY METRICS -->
    <div class="donation-summary">
        <div class="donation-summary-card">
            <div class="donation-summary-icon pending-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <span>Awaiting Verification</span>
                <strong><?= $pendingCount; ?></strong>
            </div>
        </div>

        <div class="donation-summary-card">
            <div class="donation-summary-icon completed-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Verified / Completed</span>
                <strong><?= $completedCount; ?></strong>
            </div>
        </div>

        <div class="donation-summary-card">
            <div class="donation-summary-icon amount-icon">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <span>Total Collected</span>
                <strong>₹<?= number_format($totalAmount); ?></strong>
            </div>
        </div>

        <div class="donation-summary-card">
            <div class="donation-summary-icon total-icon">
                <i class="fa-solid fa-hand-holding-heart"></i>
            </div>
            <div>
                <span>Total Submissions</span>
                <strong><?= $totalCount; ?></strong>
            </div>
        </div>
    </div>

    <!-- FILTERS & SEARCH -->
    <div class="filter-bar">
        <div class="filter-tabs">
            <a href="index.php?status=all" class="filter-tab <?= ($statusFilter === 'all') ? 'active' : ''; ?>">
                All (<?= $totalCount; ?>)
            </a>
            <a href="index.php?status=pending" class="filter-tab <?= ($statusFilter === 'pending') ? 'active' : ''; ?>">
                Pending (<?= $pendingCount; ?>)
            </a>
            <a href="index.php?status=completed" class="filter-tab <?= ($statusFilter === 'completed') ? 'active' : ''; ?>">
                Verified (<?= $completedCount; ?>)
            </a>
            <a href="index.php?status=failed" class="filter-tab <?= ($statusFilter === 'failed') ? 'active' : ''; ?>">
                Failed / Rejected
            </a>
        </div>

        <form method="GET" class="search-form">
            <?php if ($statusFilter !== 'all'): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter); ?>">
            <?php endif; ?>
            <input type="text" name="search" class="search-input" value="<?= htmlspecialchars($searchQuery); ?>" placeholder="Search name, email, UTR, cause...">
            <button type="submit" class="btn btn-dark btn-sm px-3 fw-bold">Search</button>
            <?php if ($searchQuery !== ''): ?>
                <a href="index.php?status=<?= urlencode($statusFilter); ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- DONATIONS TABLE -->
    <div class="donation-table-wrapper">
        <?php if (!empty($donations)): ?>
            <table class="donation-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor Name &amp; Email</th>
                        <th>Phone</th>
                        <th>Supported Purpose</th>
                        <th>Amount</th>
                        <th>UPI Ref (UTR)</th>
                        <th>Submitted At</th>
                        <th>Status</th>
                        <th>Actions &amp; Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $don): ?>
                        <tr>
                            <td>#<?= (int) $don['id']; ?></td>
                            <td>
                                <div class="donor-name"><?= htmlspecialchars($don['donor_name']); ?></div>
                                <div class="donor-email"><?= htmlspecialchars($don['donor_email']); ?></div>
                            </td>
                            <td><?= !empty($don['donor_phone']) ? htmlspecialchars($don['donor_phone']) : '<span class="text-muted">—</span>'; ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($don['donation_purpose']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="donation-amount">₹<?= number_format((float)$don['donation_amount']); ?></span>
                            </td>
                            <td>
                                <code class="transaction-id"><?= htmlspecialchars($don['transaction_id']); ?></code>
                            </td>
                            <td>
                                <span class="donation-date text-muted">
                                    <?= date('d M Y, h:i A', strtotime($don['payment_submitted_at'] ?? $don['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="payment-status <?= htmlspecialchars($don['payment_status']); ?>">
                                    <?php if ($don['payment_status'] === 'pending'): ?>
                                        <i class="fa-solid fa-clock"></i> Pending
                                    <?php elseif ($don['payment_status'] === 'completed'): ?>
                                        <i class="fa-solid fa-check"></i> Verified
                                    <?php elseif ($don['payment_status'] === 'failed'): ?>
                                        <i class="fa-solid fa-xmark"></i> Failed
                                    <?php else: ?>
                                        <?= htmlspecialchars(ucfirst($don['payment_status'])); ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <div class="donation-actions">
                                    <?php if ($don['payment_status'] === 'pending'): ?>
                                        <!-- Verify Action -->
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Verify this payment? This will automatically generate and email the Certificate of Appreciation to the donor.');">
                                            <input type="hidden" name="donation_id" value="<?= (int) $don['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="donation-action-btn approve-btn" title="Verify Payment &amp; Send Certificate">
                                                <i class="fa-solid fa-check"></i> Verify &amp; Send Certificate
                                            </button>
                                        </form>

                                        <!-- Reject Action -->
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this payment record?');">
                                            <input type="hidden" name="donation_id" value="<?= (int) $don['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="donation-action-btn reject-btn" title="Reject Payment">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </button>
                                        </form>

                                    <?php elseif ($don['payment_status'] === 'completed'): ?>
                                        <!-- Download PDF Certificate -->
                                        <a href="index.php?action=download_pdf&id=<?= (int) $don['id']; ?>" class="donation-action-btn pdf-btn" title="Download PDF Certificate">
                                            <i class="fa-solid fa-file-pdf"></i> Download PDF
                                        </a>

                                        <!-- View Web Certificate -->
                                        <?php
                                        $token = SevarthaCertificate::getSecurityToken((int)$don['id'], $don['donor_email']);
                                        ?>
                                        <a href="../../donate/certificate.php?id=<?= (int) $don['id']; ?>&token=<?= urlencode($token); ?>" target="_blank" class="donation-action-btn view-btn" title="View Digital Certificate">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Web View
                                        </a>

                                        <!-- Resend Email -->
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Resend certificate email to <?= htmlspecialchars($don['donor_email']); ?>?');">
                                            <input type="hidden" name="donation_id" value="<?= (int) $don['id']; ?>">
                                            <button type="submit" name="action" value="resend_certificate" class="donation-action-btn resend-btn" title="Resend Certificate Email">
                                                <i class="fa-solid fa-envelope"></i> Resend Email
                                            </button>
                                        </form>

                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:12px;">No actions</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-inbox fa-3x mb-3 text-secondary"></i>
                <p class="mb-0">No donations found matching your selection.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>