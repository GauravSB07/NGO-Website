<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

include __DIR__ . '/../../config/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $upiId = trim($_POST['upi_id'] ?? '');

    if ($upiId === '') {
        $message = 'Please enter the UPI ID.';
        $messageType = 'error';
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+$/', $upiId)) {
        $message = 'Please enter a valid UPI ID.';
        $messageType = 'error';
    } elseif (!isset($_FILES['qr_code']) || $_FILES['qr_code']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please select a QR code image.';
        $messageType = 'error';
    } else {

        $file = $_FILES['qr_code'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] <= 0 || $file['size'] > $maxSize) {
            $message = 'QR image must be between 1 byte and 5 MB.';
            $messageType = 'error';
        } else {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            $allowedTypes = [
                'image/png',
                'image/jpeg',
                'image/webp'
            ];

            if (!in_array($mimeType, $allowedTypes, true)) {
                $message = 'Only PNG, JPG and WEBP images are allowed.';
                $messageType = 'error';
            } else {

                $qrData = file_get_contents($file['tmp_name']);

                if ($qrData === false || $qrData === '') {
                    $message = 'Unable to read the QR image.';
                    $messageType = 'error';
                } else {

                    mysqli_begin_transaction($conn);

                    try {

                        $deactivate = mysqli_prepare(
                            $conn,
                            'UPDATE payment_settings SET is_active = 0'
                        );

                        if (!$deactivate || !mysqli_stmt_execute($deactivate)) {
                            throw new Exception(mysqli_error($conn));
                        }

                        mysqli_stmt_close($deactivate);

                        $insert = mysqli_prepare(
                            $conn,
                            'INSERT INTO payment_settings (upi_id, qr_code, is_active) VALUES (?, ?, 1)'
                        );

                        if (!$insert) {
                            throw new Exception(mysqli_error($conn));
                        }

                        $null = null;

                        mysqli_stmt_bind_param(
                            $insert,
                            'sb',
                            $upiId,
                            $null
                        );

                        mysqli_stmt_send_long_data(
                            $insert,
                            1,
                            $qrData
                        );

                        if (!mysqli_stmt_execute($insert)) {
                            throw new Exception(mysqli_stmt_error($insert));
                        }

                        mysqli_stmt_close($insert);

                        mysqli_commit($conn);

                        $message = 'New payment settings saved and activated successfully.';
                        $messageType = 'success';

                    } catch (Throwable $e) {

                        mysqli_rollback($conn);

                        $message = 'Unable to save payment settings: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }
        }
    }
}

$currentUpi = '';
$currentQrExists = false;
$currentUpdatedAt = '';

$result = mysqli_query(
    $conn,
    'SELECT upi_id, qr_code, updated_at FROM payment_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1'
);

if ($result) {
    $current = mysqli_fetch_assoc($result);

    if ($current) {
        $currentUpi = $current['upi_id'] ?? '';
        $currentQrExists = !empty($current['qr_code']);
        $currentUpdatedAt = $current['updated_at'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI &amp; QR Payment Settings | Sevartha Foundation Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin/admin.css?v=<?= time(); ?>">

    <style>
        * { box-sizing: border-box; }
        .settings-container { max-width: 960px; margin: 35px auto 60px; padding: 0 20px; }
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
        .back-link:hover { color: #1a1a1a; transform: translateX(-2px); }
        .settings-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 34px;
            box-shadow: 0 8px 30px rgba(26,26,26,0.06);
            border: 1px solid rgba(84, 82, 71, 0.14);
            margin-bottom: 24px;
        }
        .settings-card h1, .settings-card h2 { margin: 0 0 8px; font-family: Georgia, serif; font-size: 24px; font-weight: 800; color: #1a1a1a; }
        .subtitle { color: #666; margin-bottom: 26px; font-size: 14px; line-height: 1.6; }
        .current-box {
            padding: 24px;
            border-radius: 14px;
            background: var(--adm-sand-soft);
            border: 1px solid rgba(208, 200, 182, 0.5);
            margin-bottom: 30px;
        }
        .label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #716D60; margin-bottom: 6px; }
        .upi { font-size: 20px; font-weight: 800; color: var(--adm-dark); margin-bottom: 18px; word-break: break-word; font-family: monospace; background: #ffffff; padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(84, 82, 71, 0.14); display: inline-block; }
        .qr-preview img {
            display: block;
            width: 220px;
            height: 220px;
            object-fit: contain;
            background: #ffffff;
            border: 1px solid rgba(84, 82, 71, 0.2);
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .updated { color: #716D60; font-size: 12.5px; margin-top: 14px; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 7px; font-size: 13.5px; color: #333; }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8d4ca;
            border-radius: 10px;
            font-size: 14px;
            background: #ffffff;
            transition: var(--adm-transition);
        }
        input:focus {
            outline: none;
            border-color: #545247;
            box-shadow: 0 0 0 3px rgba(84, 82, 71, 0.15);
        }
        .help-text { color: #777; font-size: 12px; margin-top: 6px; line-height: 1.5; }
        .btn-save {
            background: #1a1a1a;
            color: #ffffff;
            border: 1px solid #1a1a1a;
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
        .btn-save:hover { background: #545247; border-color: #545247; color: #ffffff; transform: translateY(-1px); }
        .warning-notice {
            margin-top: 20px;
            padding: 14px 18px;
            border-radius: 10px;
            background: #fff9e6;
            border: 1px solid #f0dc99;
            color: #795600;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body class="admin-dashboard">

<?php
$activeNav = 'donations';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="settings-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <a href="index.php" class="back-link mb-0">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Donations Management</span>
        </a>
        <div class="d-flex gap-2 flex-wrap">
            <a href="certificate-settings.php" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="fa-solid fa-award me-1"></i> Certificate Template
            </a>
            <a href="email-settings.php" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-envelope me-1"></i> Email &amp; SMTP Settings
            </a>
        </div>
    </div>

    <div class="settings-card">
        <h1><i class="fa-solid fa-qrcode me-2"></i>UPI &amp; QR Payment Settings</h1>
        <p class="subtitle">
            Manage the official UPI ID and payment QR code displayed on the public donor payment page. When donors contribute, this QR code and UPI ID will be presented to them.
        </p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid <?php echo ($messageType === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="current-box">
            <h2 style="margin-top:0; font-size: 19px;"><i class="fa-solid fa-circle-check text-success me-2"></i>Currently Active Payment Details</h2>

            <div class="row align-items-center mt-3">
                <div class="col-md-6">
                    <?php if ($currentUpi !== ''): ?>
                        <div class="label">Configured UPI ID</div>
                        <div class="upi"><?php echo htmlspecialchars($currentUpi); ?></div>
                    <?php else: ?>
                        <div class="alert alert-warning py-2 mb-3">No active UPI ID is configured.</div>
                    <?php endif; ?>

                    <?php if ($currentUpdatedAt !== ''): ?>
                        <div class="updated">
                            <i class="fa-regular fa-clock me-1"></i> Last updated: <?php echo htmlspecialchars($currentUpdatedAt); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 text-md-center mt-3 mt-md-0">
                    <div class="label">Live Donation QR Code</div>
                    <?php if ($currentQrExists): ?>
                        <div class="qr-preview d-inline-block">
                            <img src="../../donate/qrimg.php" alt="Current UPI QR code">
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning py-2">No active QR code is stored.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h2 style="font-size: 20px;"><i class="fa-solid fa-pen-to-square me-2"></i>Update / Replace Payment Details</h2>
        <p class="help-text mb-4">Enter the new UPI ID and upload its QR code image. Once submitted, the new details will immediately be displayed on the public payment page.</p>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="upi_id"><i class="fa-solid fa-at me-1"></i> UPI ID *</label>
                        <input type="text" id="upi_id" name="upi_id" placeholder="e.g. sevartha@upi" maxlength="150" value="<?php echo htmlspecialchars($currentUpi); ?>" required>
                        <div class="help-text">Donors can copy and send payments to this UPI address.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="qr_code"><i class="fa-solid fa-image me-1"></i> QR Code Image *</label>
                        <input type="file" id="qr_code" name="qr_code" accept="image/png,image/jpeg,image/webp" required>
                        <div class="help-text">Allowed formats: PNG, JPG, or WEBP (Max: 5 MB).</div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save &amp; Make Active
                </button>
            </div>
        </form>

        <div class="warning-notice">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
            <div>Saving new payment details deactivates the previous UPI ID &amp; QR setting. Existing historical donation records remain safely preserved.</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
