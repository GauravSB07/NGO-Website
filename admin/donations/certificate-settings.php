<?php

/* =========================================================
   SEVARTHA FOUNDATION - EXECUTIVE ADMIN PANEL
   CERTIFICATE TEMPLATE & STRUCTURE EDITOR
   Visual No-Code Certificate Customizer
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../includes/certificate.php';

$successMessage = '';
$errorMessage = '';

// Handle Sample PDF Download
if (isset($_GET['action']) && $_GET['action'] === 'download_sample') {
    $sampleDonation = [
        'id' => 9999,
        'donor_name' => 'Ananya Sharma',
        'donor_email' => 'donor@example.com',
        'donation_amount' => 15000.00,
        'donation_purpose' => 'Child Education & Healthcare Mission',
        'transaction_id' => 'UPI/489274921984/REF',
        'payment_status' => 'completed',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $pdf = SevarthaCertificate::generatePdf($sampleDonation);
    $filename = "Sample_Certificate_Preview.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    echo $pdf;
    exit();
}

// Handle Reset to Defaults
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_defaults'])) {
    $defaults = SevarthaCertificate::getDefaultTemplate();
    if (SevarthaCertificate::saveTemplate($defaults)) {
        $successMessage = "Certificate template structure and styling have been reset to foundation defaults.";
    } else {
        $errorMessage = "Unable to reset template. Please check file permissions for config/certificate_template.json.";
    }
}

// Handle Save Custom Template
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_template'])) {
    $templateData = [
        'org_name' => trim($_POST['org_name'] ?? 'SEVARTHA FOUNDATION'),
        'org_sub' => trim($_POST['org_sub'] ?? ''),
        'org_motto' => trim($_POST['org_motto'] ?? ''),
        'kicker' => trim($_POST['kicker'] ?? 'OFFICIAL PHILANTHROPIC RECOGNITION'),
        'cert_title' => trim($_POST['cert_title'] ?? 'CERTIFICATE OF APPRECIATION'),
        'presentation_text' => trim($_POST['presentation_text'] ?? 'This certificate is gratefully presented in recognition of'),
        'citation_line1' => trim($_POST['citation_line1'] ?? ''),
        'citation_line2' => trim($_POST['citation_line2'] ?? ''),
        'citation_line3' => trim($_POST['citation_line3'] ?? ''),
        'citation_paragraph' => trim($_POST['citation_paragraph'] ?? ''),
        'citation_sub_paragraph' => trim($_POST['citation_sub_paragraph'] ?? ''),
        'seal_text_top' => trim($_POST['seal_text_top'] ?? 'SEVARTHA'),
        'seal_text_mid' => trim($_POST['seal_text_mid'] ?? 'FOUNDATION'),
        'seal_text_bot' => trim($_POST['seal_text_bot'] ?? '* OFFICIAL SEAL *'),
        'signatory1_title' => trim($_POST['signatory1_title'] ?? 'Authorized Trustee'),
        'signatory1_org' => trim($_POST['signatory1_org'] ?? 'Sevartha Foundation'),
        'signatory2_title' => trim($_POST['signatory2_title'] ?? 'Date of Issue'),
        'signatory2_subtitle' => trim($_POST['signatory2_subtitle'] ?? '{date}'),
        'footer_authenticity' => trim($_POST['footer_authenticity'] ?? 'Digitally Verified by Sevartha Foundation Trust'),
        'color_theme' => trim($_POST['color_theme'] ?? 'classic_gold')
    ];

    if ($templateData['org_name'] === '' || $templateData['cert_title'] === '') {
        $errorMessage = "Organization Name and Certificate Title are required fields.";
    } else {
        if (SevarthaCertificate::saveTemplate($templateData)) {
            $successMessage = "Certificate template structure and content updated successfully! All new certificates and emails will instantly use this layout.";
        } else {
            $errorMessage = "Unable to save template. Please verify that config/certificate_template.json is writable.";
        }
    }
}

// Load current configuration
$tmpl = SevarthaCertificate::loadTemplate();

// Sample donation for live preview
$samplePreview = [
    'id' => 1248,
    'donor_name' => 'Ananya Sharma',
    'donation_amount' => 10000.00,
    'donation_purpose' => 'Education For All & Nutrition',
    'transaction_id' => 'UPI/489274921984/REF',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Template &amp; Structure Editor | Sevartha Foundation Admin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../css/admin/admin.css?v=<?= time(); ?>">

    <style>
        .cert-editor-container {
            max-width: 1540px;
            margin: 32px auto 80px;
            padding: 0 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: #545247;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: var(--adm-transition);
        }
        .back-link:hover {
            color: #1a1a1a;
            transform: translateX(-3px);
        }

        .editor-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .editor-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-section-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            border: 1px solid rgba(84, 82, 71, 0.14);
            box-shadow: 0 8px 24px rgba(26, 26, 26, 0.05);
            margin-bottom: 24px;
        }

        .form-section-card h2 {
            font-family: Georgia, serif;
            font-size: 19px;
            font-weight: 800;
            color: var(--adm-dark);
            margin: 0 0 16px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(208, 200, 182, 0.35);
        }

        .form-section-card h2 i {
            color: var(--adm-olive);
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 6px;
        }

        .label-hint {
            font-weight: normal;
            font-size: 11.5px;
            color: #777777;
            margin-left: 6px;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d8d4ca;
            border-radius: 10px;
            font-size: 13.5px;
            background: #ffffff;
            transition: var(--adm-transition);
        }

        input[type="text"]:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #545247;
            box-shadow: 0 0 0 3px rgba(84, 82, 71, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 70px;
            line-height: 1.5;
        }

        .tag-pill-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
            margin-bottom: 16px;
            padding: 10px 14px;
            background: #fbf9f5;
            border: 1px dashed #d8d4ca;
            border-radius: 10px;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            background: #ffffff;
            border: 1px solid #d0c8b6;
            border-radius: 999px;
            font-size: 11.5px;
            font-family: monospace;
            font-weight: 700;
            color: var(--adm-olive-dark);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tag-pill:hover {
            background: var(--adm-dark);
            color: #ffffff;
            border-color: var(--adm-dark);
            transform: translateY(-1px);
        }

        .preview-sticky {
            position: sticky;
            top: 100px;
        }

        /* Certificate Live Preview Simulator */
        .preview-card-wrap {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            border: 1px solid rgba(84, 82, 71, 0.14);
            box-shadow: 0 10px 30px rgba(26, 26, 26, 0.08);
        }

        .cert-mockup {
            background: #FBF9F5;
            border: 3px solid #D0C8B6;
            border-radius: 12px;
            padding: 28px 24px;
            position: relative;
            box-shadow: inset 0 0 0 4px #FBF9F5, inset 0 0 0 5px #545247;
            text-align: center;
            transition: all 0.3s ease;
        }

        .mock-org-name {
            font-family: Georgia, serif;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #1A1A1A;
            margin: 0;
            text-transform: uppercase;
        }

        .mock-org-sub {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #545247;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .mock-divider {
            width: 60%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #D0C8B6, transparent);
            margin: 12px auto;
        }

        .mock-cert-title {
            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #545247;
            text-transform: uppercase;
            margin: 8px 0 4px 0;
        }

        .mock-present-text {
            font-size: 11px;
            font-style: italic;
            color: #68665E;
            margin-bottom: 8px;
        }

        .mock-donor-name {
            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: 800;
            color: #1A1A1A;
            text-decoration: underline;
            text-decoration-color: #D0C8B6;
            text-underline-offset: 4px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .mock-citation {
            font-size: 11px;
            line-height: 1.55;
            color: #444444;
            max-width: 90%;
            margin: 0 auto 16px auto;
        }

        .mock-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
            padding-top: 14px;
        }

        .mock-seal {
            width: 64px;
            height: 64px;
            border: 2px solid #D0C8B6;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            font-weight: 800;
            color: #545247;
            text-transform: uppercase;
            line-height: 1.15;
            box-shadow: inset 0 0 0 2px #FBF9F5, inset 0 0 0 3px #545247;
        }

        .mock-sign {
            text-align: center;
            width: 120px;
        }

        .mock-sign-line {
            height: 1px;
            background: #545247;
            margin-bottom: 4px;
        }

        .mock-sign-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #1A1A1A;
            display: block;
        }

        .mock-sign-sub {
            font-size: 8px;
            color: #68665E;
            display: block;
        }

        .mock-meta {
            font-size: 8px;
            color: #888888;
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid rgba(208, 200, 182, 0.4);
            padding-top: 6px;
        }

        .btn-action-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(84, 82, 71, 0.12);
        }

        .btn-save-cert {
            background: #1A1A1A;
            color: #FFFFFF;
            border: 1px solid #1A1A1A;
            border-radius: 999px;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }
        .btn-save-cert:hover {
            background: #545247;
            border-color: #545247;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-preview-pdf {
            background: #2E7D32;
            color: #FFFFFF;
            border: 1px solid #2E7D32;
            border-radius: 999px;
            padding: 12px 22px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }
        .btn-preview-pdf:hover {
            background: #236327;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-reset-defaults {
            background: transparent;
            color: #8C2A2A;
            border: 1px solid rgba(140, 42, 42, 0.3);
            border-radius: 999px;
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            transition: all 0.2s ease;
        }
        .btn-reset-defaults:hover {
            background: #FEE2E2;
            border-color: #8C2A2A;
        }
    </style>
</head>

<body class="admin-dashboard">

<?php
$activeNav = 'donations';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="cert-editor-container">

    <a href="index.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Donations Management</span>
    </a>

    <!-- PAGE HEADER & ACTION LINKS -->
    <div class="admin-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1><i class="fa-solid fa-award me-2 text-warning"></i>Certificate Template &amp; Structure Editor</h1>
            <p>Customize the official Certificate of Appreciation structure, organization names, citations, and seal without touching any code.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="index.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-list-check me-1"></i> Donations List
            </a>
            <a href="email-settings.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-envelope me-1"></i> Email &amp; SMTP
            </a>
            <a href="payment-settings.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-qrcode me-1"></i> UPI &amp; QR
            </a>
            <a href="certificate-settings.php?action=download_sample" class="btn btn-success btn-sm fw-bold" target="_blank" title="Generate real sample PDF with current settings">
                <i class="fa-solid fa-file-pdf me-1"></i> Download Sample PDF
            </a>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- MAIN EDITOR & LIVE PREVIEW GRID -->
    <div class="editor-grid">

        <!-- LEFT COLUMN: SETTINGS FORM -->
        <form method="POST" id="certTemplateForm">
            <input type="hidden" name="save_template" value="1">

            <!-- 1. ORGANIZATION IDENTITY -->
            <div class="form-section-card">
                <h2><i class="fa-solid fa-landmark"></i> 1. Organization &amp; Trust Header</h2>

                <div class="form-group">
                    <label for="org_name">Organization / Trust Name *</label>
                    <input type="text" id="org_name" name="org_name" value="<?= htmlspecialchars($tmpl['org_name']); ?>" required>
                    <div class="label-hint mt-1">Displayed prominently at the top in Georgia / Times bold serif font.</div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="org_sub">Subtitle / Registration Status</label>
                            <input type="text" id="org_sub" name="org_sub" value="<?= htmlspecialchars($tmpl['org_sub']); ?>">
                            <div class="label-hint mt-1">e.g. A REGISTERED PUBLIC CHARITABLE TRUST</div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="org_motto">Foundation Motto / Tagline</label>
                            <input type="text" id="org_motto" name="org_motto" value="<?= htmlspecialchars($tmpl['org_motto'] ?? ''); ?>">
                            <div class="label-hint mt-1">e.g. Small acts of kindness. Lasting possibilities.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. CERTIFICATE TITLE & PRESENTATION PHRASE -->
            <div class="form-section-card">
                <h2><i class="fa-solid fa-ribbon"></i> 2. Certificate Title &amp; Presentation</h2>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kicker">Award Kicker / Category Eyebrow</label>
                            <input type="text" id="kicker" name="kicker" value="<?= htmlspecialchars($tmpl['kicker']); ?>">
                            <div class="label-hint mt-1">e.g. OFFICIAL PHILANTHROPIC RECOGNITION</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cert_title">Main Certificate Title *</label>
                            <input type="text" id="cert_title" name="cert_title" value="<?= htmlspecialchars($tmpl['cert_title']); ?>" required>
                            <div class="label-hint mt-1">e.g. CERTIFICATE OF APPRECIATION</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="presentation_text">Presentation Statement</label>
                    <input type="text" id="presentation_text" name="presentation_text" value="<?= htmlspecialchars($tmpl['presentation_text']); ?>">
                    <div class="label-hint mt-1">Precedes the recipient's name (e.g. This certificate is gratefully presented in recognition of).</div>
                </div>
            </div>

            <!-- 3. CITATION BODY & DYNAMIC VARIABLES -->
            <div class="form-section-card">
                <h2><i class="fa-solid fa-quote-left"></i> 3. Citation Statement &amp; Body Text</h2>

                <p class="text-muted small mb-2">
                    Click any placeholder below to copy or insert it into your citation fields:
                </p>

                <div class="tag-pill-container">
                    <span class="tag-pill" onclick="insertTag('{donor_name}')" title="Insert Donor Full Name"><i class="fa-solid fa-user"></i> {donor_name}</span>
                    <span class="tag-pill" onclick="insertTag('{amount}')" title="Insert Formatted Amount"><i class="fa-solid fa-indian-rupee-sign"></i> {amount}</span>
                    <span class="tag-pill" onclick="insertTag('{purpose}')" title="Insert Donation Purpose / Cause"><i class="fa-solid fa-heart"></i> {purpose}</span>
                    <span class="tag-pill" onclick="insertTag('{date}')" title="Insert Current Date"><i class="fa-regular fa-calendar"></i> {date}</span>
                    <span class="tag-pill" onclick="insertTag('{cert_id}')" title="Insert Certificate ID"><i class="fa-solid fa-hashtag"></i> {cert_id}</span>
                    <span class="tag-pill" onclick="insertTag('{transaction_id}')" title="Insert Bank UTR / Transaction ID"><i class="fa-solid fa-receipt"></i> {transaction_id}</span>
                </div>

                <div class="form-group">
                    <label for="citation_line1">PDF Citation Line 1 <span class="label-hint">(Primary recognition clause)</span></label>
                    <input type="text" id="citation_line1" name="citation_line1" value="<?= htmlspecialchars($tmpl['citation_line1']); ?>">
                </div>

                <div class="form-group">
                    <label for="citation_line2">PDF Citation Line 2 <span class="label-hint">(Impact / Mission clause)</span></label>
                    <input type="text" id="citation_line2" name="citation_line2" value="<?= htmlspecialchars($tmpl['citation_line2']); ?>">
                </div>

                <div class="form-group">
                    <label for="citation_line3">PDF Citation Line 3 <span class="label-hint">(Gratitude / Transformation closing)</span></label>
                    <input type="text" id="citation_line3" name="citation_line3" value="<?= htmlspecialchars($tmpl['citation_line3']); ?>">
                </div>

                <div class="form-group mt-3">
                    <label for="citation_paragraph">Web Certificate Paragraph <span class="label-hint">(Used for responsive digital certificate viewer)</span></label>
                    <textarea id="citation_paragraph" name="citation_paragraph" rows="3"><?= htmlspecialchars($tmpl['citation_paragraph']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="citation_sub_paragraph">Web Certificate Closing Line</label>
                    <input type="text" id="citation_sub_paragraph" name="citation_sub_paragraph" value="<?= htmlspecialchars($tmpl['citation_sub_paragraph'] ?? ''); ?>">
                </div>
            </div>

            <!-- 4. SIGNATORIES & SEAL -->
            <div class="form-section-card">
                <h2><i class="fa-solid fa-signature"></i> 4. Signatories &amp; Official Seal</h2>

                <div class="row">
                    <!-- Signatory 1 -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-pen-nib me-1"></i> Signatory 1 (Left Authority)</h6>
                            <div class="form-group">
                                <label for="signatory1_title">Title / Role</label>
                                <input type="text" id="signatory1_title" name="signatory1_title" value="<?= htmlspecialchars($tmpl['signatory1_title']); ?>">
                            </div>
                            <div class="form-group mb-0">
                                <label for="signatory1_org">Organization / Designation</label>
                                <input type="text" id="signatory1_org" name="signatory1_org" value="<?= htmlspecialchars($tmpl['signatory1_org']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Signatory 2 -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-pen-nib me-1"></i> Signatory 2 (Right Authority / Date)</h6>
                            <div class="form-group">
                                <label for="signatory2_title">Title / Role</label>
                                <input type="text" id="signatory2_title" name="signatory2_title" value="<?= htmlspecialchars($tmpl['signatory2_title']); ?>">
                            </div>
                            <div class="form-group mb-0">
                                <label for="signatory2_subtitle">Subtitle / Value</label>
                                <input type="text" id="signatory2_subtitle" name="signatory2_subtitle" value="<?= htmlspecialchars($tmpl['signatory2_subtitle']); ?>">
                                <div class="label-hint mt-1">Use <code>{date}</code> for automatic issue date.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seal Text -->
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-stamp me-1"></i> Official Foundation Seal Text</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="seal_text_top">Seal Top Text</label>
                                <input type="text" id="seal_text_top" name="seal_text_top" value="<?= htmlspecialchars($tmpl['seal_text_top']); ?>" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="seal_text_mid">Seal Middle Text</label>
                                <input type="text" id="seal_text_mid" name="seal_text_mid" value="<?= htmlspecialchars($tmpl['seal_text_mid']); ?>" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="seal_text_bot">Seal Bottom Text</label>
                                <input type="text" id="seal_text_bot" name="seal_text_bot" value="<?= htmlspecialchars($tmpl['seal_text_bot']); ?>" maxlength="25">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. VISUAL THEME & FOOTER NOTICE -->
            <div class="form-section-card">
                <h2><i class="fa-solid fa-palette"></i> 5. Border Accent &amp; Verification Footer</h2>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="color_theme">Border &amp; Accent Palette</label>
                            <select id="color_theme" name="color_theme">
                                <option value="classic_gold" <?= ($tmpl['color_theme'] === 'classic_gold') ? 'selected' : ''; ?>>Classic Gold &amp; Warm Sand (Default)</option>
                                <option value="regal_olive" <?= ($tmpl['color_theme'] === 'regal_olive') ? 'selected' : ''; ?>>Regal Deep Olive &amp; Bronze</option>
                                <option value="royal_bronze" <?= ($tmpl['color_theme'] === 'royal_bronze') ? 'selected' : ''; ?>>Royal Bronze &amp; Warm Ochre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="footer_authenticity">Footer Verification Notice</label>
                            <input type="text" id="footer_authenticity" name="footer_authenticity" value="<?= htmlspecialchars($tmpl['footer_authenticity']); ?>">
                        </div>
                    </div>
                </div>

                <!-- BUTTON ACTIONS -->
                <div class="btn-action-bar">
                    <button type="submit" class="btn-save-cert">
                        <i class="fa-solid fa-floppy-disk"></i> Save Template Changes
                    </button>

                    <a href="certificate-settings.php?action=download_sample" class="btn-preview-pdf" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i> Download Sample PDF
                    </a>

                    <button type="button" class="btn-reset-defaults" onclick="confirmReset()">
                        <i class="fa-solid fa-rotate-left"></i> Reset to Defaults
                    </button>
                </div>
            </div>
        </form>

        <!-- HIDDEN RESET FORM -->
        <form method="POST" id="resetForm" style="display:none;">
            <input type="hidden" name="reset_defaults" value="1">
        </form>

        <!-- RIGHT COLUMN: REAL-TIME INTERACTIVE SIMULATOR -->
        <div class="preview-sticky">
            <div class="preview-card-wrap">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark m-0">
                        <i class="fa-solid fa-eye text-primary me-2"></i>Live Layout Preview
                    </h5>
                    <span class="badge bg-light text-dark border px-2 py-1">Interactive</span>
                </div>

                <p class="small text-muted mb-3">
                    As you edit the form on the left, this mockup updates in real time to show how donor certificates will render:
                </p>

                <!-- Mockup Box -->
                <div class="cert-mockup" id="certMockup">
                    <div class="mock-org-sub" id="prevOrgSub"><?= htmlspecialchars($tmpl['org_sub']); ?></div>
                    <div class="mock-org-name" id="prevOrgName"><?= htmlspecialchars($tmpl['org_name']); ?></div>
                    <div class="mock-divider"></div>

                    <div class="mock-cert-title" id="prevCertTitle"><?= htmlspecialchars($tmpl['cert_title']); ?></div>
                    <div class="mock-present-text" id="prevPresentText"><?= htmlspecialchars($tmpl['presentation_text']); ?></div>

                    <div>
                        <span class="mock-donor-name">Ananya Sharma</span>
                    </div>

                    <div class="mock-citation" id="prevCitation">
                        <?= htmlspecialchars(SevarthaCertificate::replacePlaceholders($tmpl['citation_line1'], $samplePreview)); ?><br>
                        <?= htmlspecialchars(SevarthaCertificate::replacePlaceholders($tmpl['citation_line2'], $samplePreview)); ?><br>
                        <?= htmlspecialchars(SevarthaCertificate::replacePlaceholders($tmpl['citation_line3'], $samplePreview)); ?>
                    </div>

                    <div class="mock-footer">
                        <!-- Seal -->
                        <div class="mock-seal">
                            <span id="prevSealTop"><?= htmlspecialchars($tmpl['seal_text_top']); ?></span>
                            <span id="prevSealMid"><?= htmlspecialchars($tmpl['seal_text_mid']); ?></span>
                            <span id="prevSealBot" style="font-size: 6px; font-weight: bold;"><?= htmlspecialchars($tmpl['seal_text_bot']); ?></span>
                        </div>

                        <!-- Signatory 1 -->
                        <div class="mock-sign">
                            <div class="mock-sign-line"></div>
                            <span class="mock-sign-title" id="prevSig1Title"><?= htmlspecialchars($tmpl['signatory1_title']); ?></span>
                            <span class="mock-sign-sub" id="prevSig1Org"><?= htmlspecialchars($tmpl['signatory1_org']); ?></span>
                        </div>

                        <!-- Signatory 2 -->
                        <div class="mock-sign">
                            <div class="mock-sign-line"></div>
                            <span class="mock-sign-title" id="prevSig2Title"><?= htmlspecialchars($tmpl['signatory2_title']); ?></span>
                            <span class="mock-sign-sub" id="prevSig2Sub"><?= htmlspecialchars(SevarthaCertificate::replacePlaceholders($tmpl['signatory2_subtitle'], $samplePreview)); ?></span>
                        </div>
                    </div>

                    <div class="mock-meta">
                        <span>Cert ID: <b>SF-CERT-<?= date('Y'); ?>-001248</b></span>
                        <span id="prevFooter"><?= htmlspecialchars($tmpl['footer_authenticity']); ?></span>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="certificate-settings.php?action=download_sample" class="btn btn-outline-dark btn-sm w-100 py-2 fw-bold" target="_blank">
                        <i class="fa-solid fa-file-arrow-down me-1"></i> Download Actual PDF Sample (Print-Ready A4)
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let activeInput = document.getElementById('citation_line1');

    // Track active input for placeholder inserting
    document.querySelectorAll('input, textarea').forEach(el => {
        el.addEventListener('focus', () => { activeInput = el; });
    });

    function insertTag(tag) {
        if (!activeInput) return;
        const start = activeInput.selectionStart;
        const end = activeInput.selectionEnd;
        const val = activeInput.value;
        activeInput.value = val.substring(0, start) + tag + val.substring(end);
        activeInput.focus();
        activeInput.selectionStart = activeInput.selectionEnd = start + tag.length;
        updatePreview();
    }

    function confirmReset() {
        if (confirm("Are you sure you want to reset all certificate structure and styling back to original foundation defaults?")) {
            document.getElementById('resetForm').submit();
        }
    }

    // Live preview updater
    function updatePreview() {
        const orgName = document.getElementById('org_name').value || 'SEVARTHA FOUNDATION';
        const orgSub = document.getElementById('org_sub').value || '';
        const certTitle = document.getElementById('cert_title').value || 'CERTIFICATE OF APPRECIATION';
        const presentText = document.getElementById('presentation_text').value || 'This certificate is gratefully presented in recognition of';

        let c1 = document.getElementById('citation_line1').value;
        let c2 = document.getElementById('citation_line2').value;
        let c3 = document.getElementById('citation_line3').value;

        // Sample replacements
        const replacements = {
            '{donor_name}': 'Ananya Sharma',
            '{amount}': '10,000',
            '{purpose}': 'Education For All & Nutrition',
            '{date}': '<?= date('d F Y'); ?>',
            '{cert_id}': 'SF-CERT-<?= date('Y'); ?>-001248',
            '{transaction_id}': 'UPI/489274921984/REF'
        };

        const replacePlaceholders = str => {
            for (const [k, v] of Object.entries(replacements)) {
                str = str.replaceAll(k, v);
            }
            return str;
        };

        document.getElementById('prevOrgName').innerText = orgName;
        document.getElementById('prevOrgSub').innerText = orgSub;
        document.getElementById('prevCertTitle').innerText = certTitle;
        document.getElementById('prevPresentText').innerText = presentText;

        const citationHtml = [replacePlaceholders(c1), replacePlaceholders(c2), replacePlaceholders(c3)]
            .filter(l => l.trim() !== '')
            .join('<br>');
        document.getElementById('prevCitation').innerHTML = citationHtml;

        document.getElementById('prevSealTop').innerText = document.getElementById('seal_text_top').value;
        document.getElementById('prevSealMid').innerText = document.getElementById('seal_text_mid').value;
        document.getElementById('prevSealBot').innerText = document.getElementById('seal_text_bot').value;

        document.getElementById('prevSig1Title').innerText = document.getElementById('signatory1_title').value;
        document.getElementById('prevSig1Org').innerText = document.getElementById('signatory1_org').value;

        document.getElementById('prevSig2Title').innerText = document.getElementById('signatory2_title').value;
        document.getElementById('prevSig2Sub').innerText = replacePlaceholders(document.getElementById('signatory2_subtitle').value);

        document.getElementById('prevFooter').innerText = document.getElementById('footer_authenticity').value;
    }

    // Attach real-time input listeners
    document.querySelectorAll('#certTemplateForm input, #certTemplateForm textarea, #certTemplateForm select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });
</script>
</body>
</html>
