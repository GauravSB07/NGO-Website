<?php

session_start();

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
    <title>Payment Settings | Sevartha Foundation Admin</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            color: #222;
        }
        .container { max-width: 850px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        h1 { margin: 0 0 8px; }
        h2 { margin-top: 30px; }
        .subtitle { color: #666; margin-bottom: 28px; }
        .message { padding: 14px 16px; border-radius: 9px; margin-bottom: 24px; }
        .success { background: #e8f7ee; color: #176b3a; }
        .error { background: #fdecec; color: #9b1c1c; }
        .current-box {
            padding: 22px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 1px solid #e8e8e8;
        }
        .label { font-size: 13px; color: #777; margin-bottom: 6px; }
        .upi { font-size: 20px; font-weight: 700; margin-bottom: 18px; word-break: break-word; }
        .qr-preview img {
            display: block;
            width: 240px;
            height: 240px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .updated { color: #777; font-size: 13px; margin-top: 12px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; }
        input[type=text], input[type=file] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            background: #fff;
        }
        .help { color: #777; font-size: 13px; margin-top: 7px; }
        button {
            border: 0;
            border-radius: 8px;
            padding: 13px 22px;
            background: #222;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }
        button:hover { opacity: .9; }
        .warning {
            margin-top: 16px;
            padding: 13px;
            border-radius: 8px;
            background: #fff7df;
            color: #795600;
            font-size: 14px;
        }
        .back { display: inline-block; margin-bottom: 20px; color: #222; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">

    <a class="back" href="../dashboard.php">← Back to Dashboard</a>

    <div class="card">
        <h1>Payment Settings</h1>
        <p class="subtitle">Manage the UPI ID and QR code shown on the public donation payment page.</p>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="current-box">
            <h2 style="margin-top:0;">Currently Active</h2>

            <?php if ($currentUpi !== ''): ?>
                <div class="label">UPI ID</div>
                <div class="upi"><?php echo htmlspecialchars($currentUpi); ?></div>
            <?php else: ?>
                <div class="message error">No active UPI ID is configured.</div>
            <?php endif; ?>

            <?php if ($currentQrExists): ?>
                <div class="label">QR Code</div>
                <div class="qr-preview">
                    <img src="../donate/qrimg.php" alt="Current UPI QR code">
                </div>
            <?php else: ?>
                <div class="message error" style="margin-top:15px;">No active QR code is stored.</div>
            <?php endif; ?>

            <?php if ($currentUpdatedAt !== ''): ?>
                <div class="updated">Last updated: <?php echo htmlspecialchars($currentUpdatedAt); ?></div>
            <?php endif; ?>
        </div>

        <h2>Add / Replace Payment Details</h2>
        <p>Enter the new UPI ID and upload its QR code. The new record becomes active immediately.</p>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="upi_id">UPI ID</label>
                <input type="text" id="upi_id" name="upi_id" placeholder="example@upi" maxlength="150" required>
            </div>

            <div class="form-group">
                <label for="qr_code">QR Code Image</label>
                <input type="file" id="qr_code" name="qr_code" accept="image/png,image/jpeg,image/webp" required>
                <div class="help">Allowed: PNG, JPG or WEBP. Maximum size: 5 MB.</div>
            </div>

            <button type="submit">Save &amp; Make Active</button>
        </form>

        <div class="warning">
            Saving new payment details deactivates the previous UPI/QR setting. Existing donation records are not changed.
        </div>
    </div>
</div>
</body>
</html>
