<?php

/* =========================================================
   SEVARTHA FOUNDATION
   DIGITAL CERTIFICATE VIEWER & DOWNLOAD PORTAL
========================================================= */

session_start();

include __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/certificate.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$token = trim($_GET['token'] ?? '');
$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($id <= 0) {
    die("Invalid certificate request. Please check your link.");
}

// Fetch donation record
$stmt = mysqli_prepare($conn, "SELECT * FROM donations WHERE id = ? LIMIT 1");
if (!$stmt) {
    die("Database error. Please try again later.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$donation = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$donation) {
    die("Certificate not found.");
}

// Security: Allow admin (if logged in) OR verify security token for public donors
$isAdmin = isset($_SESSION['admin_id']);
$expectedToken = SevarthaCertificate::getSecurityToken($id, $donation['donor_email']);

if (!$isAdmin && $token !== $expectedToken) {
    // If donor is on same session that just submitted, allow viewing
    if (!isset($_SESSION['last_donation_id']) || (int)$_SESSION['last_donation_id'] !== $id) {
        die("Invalid or expired certificate access token.");
    }
}

// Direct PDF download action
if ($download) {
    $pdf = SevarthaCertificate::generatePdf($donation);
    $certNumber = SevarthaCertificate::getCertificateNumber($id);
    $filename = "Sevartha_Foundation_Certificate_{$certNumber}.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    echo $pdf;
    exit;
}

$certNumber = SevarthaCertificate::getCertificateNumber($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Appreciation | <?php echo htmlspecialchars($donation['donor_name']); ?> | Sevartha Foundation</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        :root {
            --sand: #D0C8B6;
            --sand-light: #E5DED0;
            --sand-soft: #EEE9DF;
            --sand-dark: #BDB4A1;
            --olive: #545247;
            --olive-dark: #46443B;
            --dark-grey: #1A1A1A;
            --off-white: #F6F2E8;
            --off-white-2: #FBF9F5;
        }

        body {
            background: var(--off-white-2);
            color: var(--dark-grey);
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 30px 16px 60px;
            min-height: 100vh;
        }

        .cert-container {
            max-width: 980px;
            margin: 0 auto;
        }

        /* Action bar */
        .cert-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--sand);
            border-radius: 14px;
            padding: 16px 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 18px rgba(84, 82, 71, 0.08);
            flex-wrap: wrap;
            gap: 14px;
        }

        .cert-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #2F6B38;
            background: #E8F5E9;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #C8E6C9;
        }

        .cert-btn-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .cert-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cert-btn-primary {
            background: var(--olive);
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 12px rgba(84, 82, 71, 0.2);
        }

        .cert-btn-primary:hover {
            background: var(--olive-dark);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .cert-btn-secondary {
            background: #ffffff;
            color: var(--olive-dark);
            border: 1px solid var(--sand-dark);
        }

        .cert-btn-secondary:hover {
            background: var(--sand-soft);
            color: var(--dark-grey);
            transform: translateY(-2px);
        }

        /* Certificate Visual Styling */
        .certificate-outer {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 16px 50px rgba(84, 82, 71, 0.14);
            padding: 16px;
            border: 1px solid var(--sand);
        }

        .certificate-frame {
            position: relative;
            background: #FAF8F2;
            border: 3px solid var(--sand-dark);
            border-radius: 12px;
            padding: 12px;
        }

        /* Corner Florets */
        .cert-corner {
            position: absolute;
            width: 16px;
            height: 16px;
            background: var(--sand-dark);
        }
        .cert-corner.top-left { top: 6px; left: 6px; }
        .cert-corner.top-right { top: 6px; right: 6px; }
        .cert-corner.bottom-left { bottom: 6px; left: 6px; }
        .cert-corner.bottom-right { bottom: 6px; right: 6px; }

        .certificate-inner {
            border: 1px solid var(--olive);
            border-radius: 8px;
            padding: 44px 48px 36px;
            text-align: center;
            background: radial-gradient(circle at 50% 30%, #ffffff 40%, #FAF8F2 100%);
        }

        .cert-org-eyebrow {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--olive);
            display: block;
            margin-bottom: 6px;
        }

        .cert-org-name {
            font-family: Georgia, serif;
            font-size: 34px;
            font-weight: bold;
            color: var(--dark-grey);
            letter-spacing: 1px;
            margin: 0;
        }

        .cert-org-motto {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 13px;
            color: #68665e;
            margin: 6px 0 0;
        }

        .cert-divider {
            width: 220px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--sand-dark), transparent);
            margin: 24px auto;
        }

        .cert-award-kicker {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--olive);
            margin-bottom: 12px;
        }

        .cert-award-title {
            font-family: Georgia, serif;
            font-size: 38px;
            font-weight: bold;
            letter-spacing: -0.5px;
            color: var(--olive-dark);
            margin: 0 0 10px;
        }

        .cert-award-to {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 16px;
            color: #68665e;
            margin: 0;
        }

        .cert-recipient {
            margin: 22px 0 20px;
        }

        .cert-donor-name {
            font-family: Georgia, serif;
            font-size: 34px;
            font-weight: bold;
            color: var(--dark-grey);
            border-bottom: 2px solid var(--sand-dark);
            padding: 0 32px 8px;
            display: inline-block;
        }

        .cert-citation {
            max-width: 680px;
            margin: 0 auto 34px;
            font-size: 15px;
            line-height: 1.8;
            color: #4a483f;
        }

        .cert-citation-sub {
            font-style: italic;
            font-size: 13px;
            color: #706d5f;
            margin-top: 8px;
        }

        /* Footer: Seal & Signatures */
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 10px 30px 24px;
            border-bottom: 1px solid var(--sand);
        }

        .cert-seal-wrap {
            width: 120px;
            text-align: center;
        }

        .cert-seal {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 2px solid var(--sand-dark);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #FAF6ED;
            box-shadow: 0 4px 14px rgba(84, 82, 71, 0.12);
        }

        .cert-seal i {
            color: var(--olive);
            font-size: 16px;
            margin-bottom: 3px;
        }

        .cert-seal span {
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
            color: var(--olive);
        }

        .cert-sign-col {
            width: 180px;
            text-align: center;
        }

        .cert-sign-line {
            height: 1px;
            background: var(--olive);
            margin-bottom: 8px;
        }

        .cert-sign-name {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--dark-grey);
        }

        .cert-sign-org {
            display: block;
            font-size: 11px;
            color: #68665e;
        }

        .cert-bottom-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #706d5f;
            padding-top: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Print optimization */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .cert-actions-bar {
                display: none !important;
            }

            .certificate-outer {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                page-break-inside: avoid;
            }

            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }

        @media (max-width: 768px) {
            .certificate-inner {
                padding: 28px 18px 24px;
            }
            .cert-org-name {
                font-size: 26px;
            }
            .cert-award-title {
                font-size: 26px;
            }
            .cert-donor-name {
                font-size: 24px;
                padding: 0 12px 6px;
            }
            .cert-footer {
                flex-direction: column;
                align-items: center;
                gap: 20px;
                padding: 0 0 20px;
            }
            .cert-bottom-meta {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="cert-container">
    <!-- Top Action Bar -->
    <div class="cert-actions-bar">
        <div class="cert-status-badge">
            <i class="fa-solid fa-circle-check"></i>
            <span>Verified Philanthropic Contribution</span>
        </div>

        <div class="cert-btn-group">
            <a href="certificate.php?id=<?php echo $id; ?>&download=1<?php echo !empty($token) ? '&token=' . urlencode($token) : ''; ?>" class="cert-btn cert-btn-primary">
                <i class="fa-solid fa-file-arrow-down"></i>
                <span>Download PDF Certificate</span>
            </a>

            <button type="button" class="cert-btn cert-btn-secondary" onclick="window.print();">
                <i class="fa-solid fa-print"></i>
                <span>Print Certificate</span>
            </button>

            <a href="../index.php" class="cert-btn cert-btn-secondary">
                <i class="fa-solid fa-house"></i>
                <span>Return to Home</span>
            </a>
        </div>
    </div>

    <!-- Official Certificate Render -->
    <?php echo SevarthaCertificate::renderHtmlCertificate($donation); ?>
</div>

</body>
</html>
