<?php

/* =========================================================
   SESSION & CONFIGURATION
========================================================= */

session_start();

include __DIR__ . '/../config/db.php';

date_default_timezone_set('Asia/Kolkata');

mysqli_report(MYSQLI_REPORT_OFF);

$successMessage = '';
$submissionSummary = [];
$errorMessage = '';

/* =========================================================
   CHECK SUCCESS STATE
========================================================= */

if (isset($_SESSION['donation_success'])) {
    $successMessage = $_SESSION['donation_success'];
    $submissionSummary = $_SESSION['donation_submission_summary'] ?? [];
    unset($_SESSION['donation_success']);
    // Keep submission summary in session so refresh/print still has data
}

/* =========================================================
   STEP 1: RECEIVE DONATION DETAILS FROM donate.php
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['payment_completed'])
) {
    $name = trim($_POST['donor_name'] ?? '');
    $email = trim($_POST['donor_email'] ?? '');
    $phone = trim($_POST['donor_phone'] ?? '');
    $purpose = trim($_POST['donation_purpose'] ?? '');
    $amount = (float) ($_POST['donation_amount'] ?? 0);

    if ($name === '') {
        $errorMessage = 'Please enter your full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif ($purpose === '') {
        $errorMessage = 'Please select a donation purpose.';
    } elseif ($amount < 1 || $amount > 10000000) {
        $errorMessage = 'Please enter a valid donation amount.';
    } else {
        $_SESSION['donation_name'] = $name;
        $_SESSION['donation_email'] = $email;
        $_SESSION['donation_phone'] = $phone;
        $_SESSION['donation_purpose'] = $purpose;
        $_SESSION['donation_amount'] = $amount;

        header('Location: payment.php');
        exit;
    }
}

/* =========================================================
   STEP 2: HANDLE PAYMENT VERIFICATION SUBMISSION
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['payment_completed'])
) {
    if (!$conn) {
        $errorMessage = 'Database connection failed. Please contact the administrator.';
    } else {
        $name = trim($_SESSION['donation_name'] ?? '');
        $email = trim($_SESSION['donation_email'] ?? '');
        $phone = trim($_SESSION['donation_phone'] ?? '');
        $purpose = trim($_SESSION['donation_purpose'] ?? '');
        $amount = (float) ($_SESSION['donation_amount'] ?? 0);
        $transactionId = trim($_POST['transaction_id'] ?? '');

        if ($name === '') {
            $errorMessage = 'Donor information is missing. Please start the donation again.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Donor email is invalid. Please start the donation again.';
        } elseif ($purpose === '') {
            $errorMessage = 'Donation purpose is missing. Please start the donation again.';
        } elseif ($amount < 1) {
            $errorMessage = 'Donation amount is invalid. Please start the donation again.';
        } elseif ($transactionId === '') {
            $errorMessage = 'Please enter your UPI transaction / reference ID.';
        } elseif (strlen($transactionId) < 6) {
            $errorMessage = 'Please enter a valid UPI transaction / reference ID (at least 6 characters).';
        } else {
            $sql = "
                INSERT INTO donations
                (
                    donor_name,
                    donor_email,
                    donor_phone,
                    donation_purpose,
                    donation_amount,
                    payment_status,
                    payment_submitted_at,
                    payment_method,
                    transaction_id
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'pending',
                    CURRENT_TIMESTAMP,
                    'UPI',
                    ?
                )
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $errorMessage = 'Unable to prepare database request: ' . mysqli_error($conn);
            } else {
                $bindResult = mysqli_stmt_bind_param(
                    $stmt,
                    "ssssds",
                    $name,
                    $email,
                    $phone,
                    $purpose,
                    $amount,
                    $transactionId
                );

                if (!$bindResult) {
                    $errorMessage = 'Unable to prepare donation data: ' . mysqli_stmt_error($stmt);
                    mysqli_stmt_close($stmt);
                } else {
                    $executeResult = mysqli_stmt_execute($stmt);

                    if ($executeResult) {
                        $donationId = mysqli_insert_id($conn);
                        mysqli_stmt_close($stmt);

                        $_SESSION['donation_submission_summary'] = [
                            'id' => $donationId,
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone,
                            'amount' => $amount,
                            'purpose' => $purpose,
                            'transaction_id' => $transactionId,
                            'date' => date('d M Y, h:i A')
                        ];

                        $_SESSION['donation_success'] = 'Your payment details have been submitted successfully.';
                        $_SESSION['last_donation_id'] = $donationId;

                        unset(
                            $_SESSION['donation_name'],
                            $_SESSION['donation_email'],
                            $_SESSION['donation_phone'],
                            $_SESSION['donation_purpose'],
                            $_SESSION['donation_amount']
                        );

                        header('Location: payment.php');
                        exit;
                    } else {
                        $databaseError = mysqli_stmt_error($stmt);
                        mysqli_stmt_close($stmt);
                        $errorMessage = 'Database insert failed: ' . $databaseError;
                    }
                }
            }
        }
    }
}

/* =========================================================
   SHOW PAYMENT CHECKOUT PAGE VALUES
========================================================= */

if ($successMessage === '') {
    if (
        empty($_SESSION['donation_name']) ||
        empty($_SESSION['donation_email']) ||
        empty($_SESSION['donation_amount'])
    ) {
        header('Location: donate.php');
        exit;
    }

    $name = $_SESSION['donation_name'];
    $email = $_SESSION['donation_email'];
    $phone = $_SESSION['donation_phone'] ?? '';
    $purpose = $_SESSION['donation_purpose'];
    $amount = (float) $_SESSION['donation_amount'];

    $upiId = '';
    $settingsQuery = mysqli_query(
        $conn,
        "
        SELECT upi_id
        FROM payment_settings
        WHERE is_active = 1
        ORDER BY id DESC
        LIMIT 1
        "
    );

    if ($settingsQuery) {
        $settings = mysqli_fetch_assoc($settingsQuery);
        if ($settings) {
            $upiId = $settings['upi_id'] ?? '';
        }
    }
} else {
    // Confirmation page values
    $name = '';
    $email = '';
    $phone = '';
    $purpose = '';
    $amount = 0;
    $upiId = '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($successMessage !== '') ? 'Submission Complete | Sevartha Foundation' : 'Complete Your Donation | Sevartha Foundation'; ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Main Website CSS -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- Navbar CSS -->
    <link rel="stylesheet" href="../css/navbar.css">

    <!-- Donation CSS -->
    <link rel="stylesheet" href="donate.css?v=5">

    <!-- Animation CSS -->
    <link rel="stylesheet" href="donation-animation.css?v=4">

    <!-- Payment CSS -->
    <link rel="stylesheet" href="payment.css?v=5">

    <!-- Footer CSS -->
    <link rel="stylesheet" href="../css/footer.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- =========================================================
         STATE A: PAYMENT CONFIRMATION SCREEN (SUCCESS)
    ========================================================= -->
    <?php if ($successMessage !== ''): ?>

        <section class="payment-page confirmation-page">
            <div class="confirmation-container">
                <article class="confirmation-card" aria-labelledby="confirmation-title">

                    <!-- Victory Checkmark Medallion -->
                    <div class="confirmation-mark" aria-hidden="true">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <span class="confirmation-kicker">SUBMISSION COMPLETE</span>

                    <h1 id="confirmation-title">
                        Thank you for<br>
                        <em>making a difference.</em>
                    </h1>

                    <p class="confirmation-intro">
                        Your payment details have been submitted to Sevartha Foundation. Our finance team is currently verifying your transaction. We deeply appreciate your compassion and commitment to our shared mission.
                    </p>

                    <!-- Status Pill -->
                    <div class="confirmation-status-pill" role="status">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <span>Awaiting Team Verification</span>
                    </div>

                    <!-- Official Philanthropic Contribution Voucher -->
                    <?php if (!empty($submissionSummary)): ?>
                        <div class="confirmation-voucher">
                            <div class="confirmation-voucher-header">
                                <span>
                                    <i class="fa-solid fa-certificate"></i>
                                    SEVARTHA FOUNDATION • PHILANTHROPIC RECEIPT
                                </span>
                                <span class="confirmation-voucher-id">
                                    #SF-<?php echo str_pad((int)($submissionSummary['id'] ?? ($_SESSION['last_donation_id'] ?? 1)), 6, '0', STR_PAD_LEFT); ?>
                                </span>
                            </div>

                            <div class="confirmation-voucher-body">
                                <div class="confirmation-amount-row">
                                    <span class="confirmation-amount-label">Contributed Amount</span>
                                    <strong class="confirmation-amount-value">₹<?php echo number_format((float) $submissionSummary['amount']); ?></strong>
                                </div>

                                <dl class="confirmation-details-grid">
                                    <div class="confirmation-detail-item">
                                        <dt>Donor Name</dt>
                                        <dd><?php echo htmlspecialchars($submissionSummary['name'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="confirmation-detail-item">
                                        <dt>Email Address</dt>
                                        <dd><?php echo htmlspecialchars($submissionSummary['email'] ?? 'Recorded', ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="confirmation-detail-item">
                                        <dt>Supported Purpose</dt>
                                        <dd><?php echo htmlspecialchars($submissionSummary['purpose'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="confirmation-detail-item">
                                        <dt>UPI Reference / UTR</dt>
                                        <dd style="font-family: monospace;"><?php echo htmlspecialchars($submissionSummary['transaction_id'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="confirmation-detail-item">
                                        <dt>Payment Method</dt>
                                        <dd>UPI (Unified Payments Interface)</dd>
                                    </div>
                                    <div class="confirmation-detail-item">
                                        <dt>Submission Timestamp</dt>
                                        <dd><?php echo htmlspecialchars($submissionSummary['date'] ?? date('d M Y, h:i A'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Next Steps Milestone Timeline -->
                    <div class="confirmation-next">
                        <h2>What happens next</h2>
                        <ol class="confirmation-timeline">
                            <li>
                                <span class="confirmation-step-badge done" aria-hidden="true">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <div>
                                    <strong>Details Recorded</strong>
                                    <p>Your transaction reference has been logged into our verified contributions ledger.</p>
                                </div>
                            </li>
                            <li>
                                <span class="confirmation-step-badge in-progress" aria-hidden="true">2</span>
                                <div>
                                    <strong>Bank Reconciliation</strong>
                                    <p>Our finance team verifies the reference against incoming bank credits.</p>
                                </div>
                            </li>
                            <li>
                                <span class="confirmation-step-badge" aria-hidden="true" style="background: var(--off-white); border: 1px solid var(--sand); color: var(--grey);">3</span>
                                <div>
                                    <strong>Official Receipt &amp; Impact Update</strong>
                                    <p>An official confirmation will be generated and sent to your registered email address.</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <!-- Action Buttons -->
                    <div class="confirmation-actions">
                        <button type="button" class="confirmation-btn-action confirmation-btn-print" onclick="window.print();">
                            <i class="fa-solid fa-print" aria-hidden="true"></i>
                            <span>Print / Save Receipt</span>
                        </button>

                        <button type="button" class="confirmation-btn-action confirmation-btn-share" id="shareImpactBtn">
                            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                            <span>Share Giving</span>
                        </button>

                        <a class="confirmation-btn-action confirmation-btn-home" href="../index.php">
                            <i class="fa-solid fa-house" aria-hidden="true"></i>
                            <span>Return to Home</span>
                        </a>
                    </div>

                    <a class="confirmation-help-link" href="../connect-with-us/contact.php">
                        Have questions about this transaction? Connect with our team.
                    </a>

                </article>
            </div>
        </section>

    <!-- =========================================================
         STATE B: PAYMENT CHECKOUT PAGE (INITIAL)
    ========================================================= -->
    <?php else: ?>

        <section class="payment-page payment-checkout" aria-labelledby="checkout-title">
            <div class="checkout-container">

                <!-- Progress Stepper -->
                <nav class="checkout-progress" aria-label="Donation progress">
                    <a href="donate.php" title="Return to edit details">
                        <span aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                        01 Contribution details
                    </a>
                    <i class="checkout-progress-line" aria-hidden="true"></i>
                    <span aria-current="step"><b>02</b> Payment &amp; verification</span>
                </nav>

                <!-- Page Header -->
                <header class="checkout-heading">
                    <div>
                        <div class="checkout-eyebrow">
                            <span></span>
                            SUPPORT SEVARTHA FOUNDATION
                        </div>
                        <h1 id="checkout-title">Complete your <em>donation.</em></h1>
                        <p>Pay with your preferred UPI app, then submit your transaction reference to complete verification.</p>
                    </div>
                    <a class="checkout-help" href="../connect-with-us/contact.php">
                        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                        <span>Need assistance?</span>
                    </a>
                </header>

                <div class="checkout-layout">

                    <!-- =============================================
                         LEFT: EXECUTIVE DONOR SUMMARY VOUCHER
                    ============================================== -->
                    <aside class="checkout-summary" aria-labelledby="summary-title">
                        <div class="checkout-community">
                            <img src="../static_image.php?name=homepage_image1.png" alt="Sevartha Foundation community work">
                            <div class="checkout-community-badge">
                                <i class="fa-solid fa-shield-heart" aria-hidden="true"></i>
                                <span>SEVARTHA FOUNDATION</span>
                            </div>
                        </div>

                        <div class="checkout-summary-body">
                            <span class="checkout-summary-kicker">YOUR CONTRIBUTION</span>
                            <div class="checkout-amount" id="summary-title">
                                ₹<?php echo number_format($amount); ?>
                                <span>INR</span>
                            </div>

                            <div class="checkout-purpose-chip">
                                <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <!-- Donor Details -->
                            <div class="checkout-donor-card">
                                <div class="checkout-donor-header">
                                    <div class="checkout-donor-avatar" aria-hidden="true">
                                        <?php echo htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>

                                <div class="checkout-donor-details">
                                    <dl>
                                        <?php if ($phone !== ''): ?>
                                            <div>
                                                <dt>Phone</dt>
                                                <dd><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <dt>Frequency</dt>
                                            <dd>One-time Contribution</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <a class="checkout-edit" href="donate.php">
                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                Edit contribution details
                            </a>
                        </div>

                        <div class="checkout-trust-badge">
                            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                            <span>Direct Trust Account • Official NGO Receipt</span>
                        </div>
                    </aside>

                    <!-- =============================================
                         RIGHT: PAYMENT PANEL
                    ============================================== -->
                    <div class="checkout-panel">

                        <!-- STEP 1: MAKE YOUR UPI PAYMENT -->
                        <section class="checkout-pay" aria-labelledby="pay-title">
                            <div class="checkout-section-title">
                                <span class="checkout-step" aria-hidden="true">1</span>
                                <div>
                                    <h2 id="pay-title">Make your UPI payment</h2>
                                    <p>Scan the QR code or transfer directly to our official UPI ID.</p>
                                </div>
                                <span class="checkout-method">UPI</span>
                            </div>

                            <!-- Supported Apps Bar -->
                            <div class="checkout-apps-bar">
                                <span>ACCEPTED APPS</span>
                                <div class="checkout-app-chip">Google Pay</div>
                                <div class="checkout-app-chip">PhonePe</div>
                                <div class="checkout-app-chip">Paytm</div>
                                <div class="checkout-app-chip">BHIM</div>
                                <div class="checkout-app-chip">Cred</div>
                            </div>

                            <?php if ($upiId !== ''): ?>
                                <div class="checkout-transfer">

                                    <!-- QR Code Frame -->
                                    <div class="checkout-scan">
                                        <div class="checkout-qr-frame">
                                            <span class="checkout-qr-scanline" aria-hidden="true"></span>
                                            <img id="checkoutQr" src="qrimg.php" alt="Scan QR code to pay Sevartha Foundation using UPI" width="240" height="240">
                                            <p id="checkoutQrFallback" hidden>
                                                QR code unavailable.<br>Use the UPI ID below to pay.
                                            </p>
                                        </div>
                                        <p>Scan with any UPI application</p>
                                    </div>

                                    <!-- Pay Instructions -->
                                    <div class="checkout-pay-instructions">
                                        <span class="checkout-label">PAY RECIPIENT</span>
                                        <h3>Sevartha Foundation</h3>

                                        <div class="checkout-pay-amount">
                                            <span>Exact amount to pay</span>
                                            <strong>₹<?php echo number_format($amount); ?></strong>
                                        </div>

                                        <!-- Mobile Direct Intent -->
                                        <div class="checkout-mobile-upi">
                                            <a href="upi://pay?pa=<?php echo urlencode($upiId); ?>&amp;pn=Sevartha%20Foundation&amp;am=<?php echo $amount; ?>&amp;cu=INR" class="checkout-mobile-upi-btn">
                                                <i class="fa-solid fa-mobile-screen"></i>
                                                <span>Pay directly via UPI App</span>
                                            </a>
                                        </div>

                                        <ol>
                                            <li>Open your preferred UPI app and scan the QR code.</li>
                                            <li>Verify the recipient is <strong>Sevartha Foundation</strong>.</li>
                                            <li>Transfer <strong>₹<?php echo number_format($amount); ?></strong> and note your 12-digit reference number (UTR).</li>
                                        </ol>
                                    </div>

                                </div>

                                <!-- Copy UPI ID -->
                                <div class="checkout-upi">
                                    <div>
                                        <span class="checkout-label">OR TRANSFER VIA UPI ID</span>
                                        <strong id="upiId"><?php echo htmlspecialchars($upiId, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <button type="button" id="copyUpi" class="checkout-copy-btn" aria-label="Copy UPI ID">
                                        <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                        <span>Copy ID</span>
                                    </button>
                                </div>
                                <p class="payment-copy-message" id="copyMessage" role="status" aria-live="polite">UPI ID copied.</p>

                            <?php else: ?>
                                <div class="alert alert-warning my-4" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    <strong>UPI details are currently undergoing maintenance.</strong>
                                    <p class="mb-0 mt-1">Please <a href="../connect-with-us/contact.php">contact our team</a> to complete your contribution.</p>
                                </div>
                            <?php endif; ?>

                            <!-- Safety Advisory -->
                            <div class="checkout-safety">
                                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                                <div>
                                    <strong>Safety Advisory:</strong> Always verify the recipient name before confirming payment in your UPI app. Sevartha Foundation will never ask for your UPI PIN or OTP.
                                </div>
                            </div>
                        </section>

                        <!-- STEP 2: SHARE PAYMENT REFERENCE -->
                        <section class="checkout-reference" aria-labelledby="reference-title">
                            <div class="checkout-section-title">
                                <span class="checkout-step" aria-hidden="true">2</span>
                                <div>
                                    <h2 id="reference-title">Share your payment reference</h2>
                                    <p>Already paid? Submit your transaction reference ID (UTR) so our team can record and verify your contribution.</p>
                                </div>
                            </div>

                            <?php if ($errorMessage !== ''): ?>
                                <div class="alert alert-danger my-3" role="alert">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                                    <strong>We couldn't submit your details:</strong>
                                    <p class="mb-0 mt-1"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endif; ?>

                            <form action="payment.php" method="POST" novalidate>
                                <input type="hidden" name="payment_completed" value="1">

                                <label for="transaction_id">UPI Transaction / Reference ID (UTR)</label>
                                <input
                                    type="text"
                                    id="transaction_id"
                                    name="transaction_id"
                                    value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="e.g., 423589123456"
                                    maxlength="100"
                                    minlength="6"
                                    aria-describedby="reference-hint"
                                    required>

                                <!-- Expandable Guide -->
                                <details class="checkout-reference-help" id="reference-hint">
                                    <summary>
                                        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                                        <span>Where can I find my 12-digit reference ID / UTR?</span>
                                    </summary>
                                    <div class="checkout-reference-help-content">
                                        <p>Open the completed transaction in your UPI app to locate the reference number:</p>
                                        <ul>
                                            <li><strong>Google Pay:</strong> Tap transaction &gt; find <strong>UPI transaction ID</strong> (12 digits).</li>
                                            <li><strong>PhonePe:</strong> View payment details &gt; locate <strong>UTR number</strong>.</li>
                                            <li><strong>Paytm:</strong> In passbook &gt; locate <strong>UPI Ref No.</strong> or UTR.</li>
                                            <li><strong>BHIM / Banks:</strong> Look for <strong>Transaction Reference ID / UTR</strong> on your receipt.</li>
                                        </ul>
                                    </div>
                                </details>

                                <button type="submit" class="checkout-submit" id="submitPaymentBtn">
                                    <span>Submit Payment for Verification</span>
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </button>
                            </form>

                            <div class="checkout-verification">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                <p>Your donation will be logged in <strong>Awaiting Verification</strong> status until our team reconciles the transaction.</p>
                            </div>
                        </section>

                    </div>

                </div>

            </div>
        </section>

    <?php endif; ?>

    <!-- =========================================================
         FOOTER
    ========================================================= -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="payment.js?v=5"></script>
</body>

</html>