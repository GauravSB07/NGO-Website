<?php

/* =========================================================
   SEVARTHA FOUNDATION
   ADMIN: EMAIL & CERTIFICATE AUTOMATION SETTINGS
========================================================= */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../includes/mailer.php';

$configFile = __DIR__ . '/../../config/email_config.php';
$message = '';
$messageType = '';
$testResult = '';

// Load current configuration
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => '',
    'from_name' => 'Sevartha Foundation'
];

if (file_exists($configFile)) {
    $loaded = include $configFile;
    if (is_array($loaded)) {
        $config = array_merge($config, $loaded);
    }
}

// 1. Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $config['smtp_host'] = trim($_POST['smtp_host'] ?? 'smtp.gmail.com');
    $config['smtp_port'] = (int) ($_POST['smtp_port'] ?? 587);
    $config['smtp_secure'] = in_array($_POST['smtp_secure'] ?? '', ['tls', 'ssl', 'none']) ? $_POST['smtp_secure'] : 'tls';
    $config['smtp_username'] = trim($_POST['smtp_username'] ?? '');

    // Keep existing password if not provided
    $newPassword = trim($_POST['smtp_password'] ?? '');
    if (!empty($newPassword)) {
        $config['smtp_password'] = $newPassword;
    }

    $config['from_email'] = trim($_POST['from_email'] ?? $config['smtp_username']);
    $config['from_name'] = trim($_POST['from_name'] ?? 'Sevartha Foundation');

    $exportContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($configFile, $exportContent)) {
        $message = "Email and SMTP settings saved successfully.";
        $messageType = "success";
    } else {
        $message = "Unable to write configuration file. Please check file permissions on config/email_config.php.";
        $messageType = "error";
    }
}

// 2. Handle Send Test Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $testTo = trim($_POST['test_email'] ?? '');
    if (!filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
        $testResult = "Please enter a valid recipient email address for testing.";
        $messageType = "error";
    } else {
        $mailer = new SevarthaMailer($config);
        $subject = "Test Email from Sevartha Foundation";
        $html = "
            <div style='font-family:Arial,sans-serif; max-width:500px; padding:24px; border:1px solid #d0c8b6; border-radius:12px;'>
                <h2 style='color:#545247; margin-top:0;'>SMTP Connection Successful!</h2>
                <p>This is a test email sent from the Sevartha Foundation Admin Panel to verify that Gmail SMTP delivery and automated donation certificates are ready.</p>
                <p style='font-size:12px; color:#777;'>Timestamp: " . date('d M Y, h:i:s A') . "</p>
            </div>
        ";

        if ($mailer->send($testTo, "Test Recipient", $subject, $html)) {
            $testResult = "Success! Test email delivered to " . htmlspecialchars($testTo) . ". Gmail SMTP is working perfectly!";
            $messageType = "success";
        } else {
            $testResult = "Failed to deliver test email. " . htmlspecialchars($mailer->getLastError());
            $messageType = "error";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email &amp; Certificate Settings | Sevartha Foundation Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 35px 20px 60px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6f8;
            color: #222;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: #545247;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .back-link:hover { color: #1a1a1a; }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 34px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
            border: 1px solid #e2e4e8;
            margin-bottom: 24px;
        }
        h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #1a1a1a; }
        .subtitle { color: #666; margin-bottom: 26px; font-size: 14px; line-height: 1.6; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 7px; font-size: 13px; color: #333; }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #545247;
            box-shadow: 0 0 0 3px rgba(84, 82, 71, 0.15);
        }
        .help-text { color: #777; font-size: 12px; margin-top: 6px; line-height: 1.5; }
        .btn-save {
            background: #545247;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 13px 26px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-save:hover { background: #46443b; }
        .btn-test {
            background: #198754;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-test:hover { background: #157347; }
        .guide-box {
            background: #f8f9fa;
            border: 1px solid #e2e4e8;
            border-left: 4px solid #545247;
            border-radius: 8px;
            padding: 20px 24px;
            margin-top: 14px;
            font-size: 13px;
            line-height: 1.7;
        }
        .guide-box ol { margin: 10px 0 0; padding-left: 20px; }
        .guide-box li { margin-bottom: 6px; }
        .sql-box {
            background: #282c34;
            color: #abb2bf;
            padding: 18px 20px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            margin-top: 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Donations Management</span>
    </a>

    <div class="card">
        <h1><i class="fa-solid fa-envelope me-2"></i>Email &amp; Automated Certificate Settings</h1>
        <p class="subtitle">
            Configure your Gmail or SMTP account. When you verify donations, the system uses these credentials to automatically email donors with a personalized thank you letter and their official PDF Certificate of Appreciation.
        </p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($testResult)): ?>
            <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> mb-4">
                <strong>Test Email Result:</strong> <?php echo htmlspecialchars($testResult); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="save_settings" value="1">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($config['smtp_host']); ?>" placeholder="smtp.gmail.com" required>
                        <div class="help-text">For Gmail, use: <code>smtp.gmail.com</code></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="text" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($config['smtp_port']); ?>" placeholder="587" required>
                        <div class="help-text">Standard: <code>587</code> (TLS) or <code>465</code> (SSL)</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_secure">Encryption Security</label>
                        <select id="smtp_secure" name="smtp_secure">
                            <option value="tls" <?php echo ($config['smtp_secure'] === 'tls') ? 'selected' : ''; ?>>TLS (Recommended for port 587)</option>
                            <option value="ssl" <?php echo ($config['smtp_secure'] === 'ssl') ? 'selected' : ''; ?>>SSL (Port 465)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="from_name">Sender Organization Name</label>
                        <input type="text" id="from_name" name="from_name" value="<?php echo htmlspecialchars($config['from_name']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_username">Gmail Address / Username</label>
                        <input type="email" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($config['smtp_username']); ?>" placeholder="your-foundation@gmail.com" required>
                        <div class="help-text">The Gmail account from which emails will be sent.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_password">Gmail App Password (16 characters)</label>
                        <input type="password" id="smtp_password" name="smtp_password" placeholder="<?php echo !empty($config['smtp_password']) ? '••••••••••••••••' : 'Enter 16-character App Password'; ?>">
                        <div class="help-text">Leave blank to keep existing password.</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="from_email">Reply-To / From Email</label>
                <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars($config['from_email']); ?>" placeholder="donations@sevartha.org (or same as Gmail)">
            </div>

            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Email Settings
            </button>
        </form>

        <!-- Gmail App Password Guide -->
        <div class="guide-box">
            <strong><i class="fa-solid fa-key me-2 text-warning"></i>How to get your Gmail 16-character App Password:</strong>
            <ol>
                <li>Open your Google Account: <a href="https://myaccount.google.com/security" target="_blank" rel="noopener">Google Account Security</a>.</li>
                <li>Ensure <strong>2-Step Verification</strong> is enabled.</li>
                <li>Search for or navigate to <strong>App Passwords</strong> (<a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">direct link</a>).</li>
                <li>Under "App name", enter <code>Sevartha Website</code> and click <strong>Create</strong>.</li>
                <li>Google will generate a 16-character code (e.g. <code>abcd efgh ijkl mnop</code>). Paste that code into the <em>Gmail App Password</em> field above.</li>
            </ol>
        </div>
    </div>

    <!-- Test Email Card -->
    <div class="card">
        <h2><i class="fa-solid fa-paper-plane me-2 text-success"></i>Test Your Email Configuration</h2>
        <p class="subtitle">Send an instant test email to verify that your SMTP credentials connect properly before verifying donations.</p>

        <form method="POST" class="d-flex gap-3 align-items-center">
            <input type="hidden" name="send_test" value="1">
            <input type="email" name="test_email" class="form-control" placeholder="Enter recipient email (e.g. your personal email)" required style="max-width: 450px;">
            <button type="submit" class="btn-test">
                <i class="fa-solid fa-paper-plane me-2"></i>Send Test Email
            </button>
        </form>
    </div>

    <!-- Optional SQL Query for User (Without Modifying Database Directly) -->
    <div class="card">
        <h2><i class="fa-solid fa-database me-2 text-primary"></i>Database Schema Note</h2>
        <p class="subtitle">
            The system works automatically without requiring any database changes (settings are safely stored in <code>config/email_config.php</code> and certificates are generated using existing donation records). If you want to store certificate tracking columns in your database, you can optionally run this SQL query yourself:
        </p>

        <div class="sql-box">
ALTER TABLE donations
ADD COLUMN IF NOT EXISTS certificate_number VARCHAR(100) NULL AFTER transaction_id,
ADD COLUMN IF NOT EXISTS certificate_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER certificate_number,
ADD COLUMN IF NOT EXISTS certificate_sent_at TIMESTAMP NULL AFTER certificate_sent,
ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL AFTER certificate_sent_at;
        </div>
    </div>
</div>

</body>
</html>
