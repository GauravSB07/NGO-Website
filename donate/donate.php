<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
 * STEP 1: Receive donation details and save them in the session.
 * The payment.php page reads these session values.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['donor_name'] ?? '');
    $email = trim($_POST['donor_email'] ?? '');
    $phone = trim($_POST['donor_phone'] ?? '');
    $purpose = trim($_POST['donation_purpose'] ?? '');
    $amount = (int) ($_POST['donation_amount'] ?? 0);

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

/*
 * Only clear old donation data when the donor is opening
 * donate.php normally, not when the form is being submitted.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset(
        $_SESSION['donation_name'],
        $_SESSION['donation_email'],
        $_SESSION['donation_phone'],
        $_SESSION['donation_purpose'],
        $_SESSION['donation_amount']
    );
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate | Sevartha Foundation</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Main CSS -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- Navbar CSS -->
    <link rel="stylesheet" href="../css/navbar.css">

    <!-- Donation CSS -->
    <link rel="stylesheet" href="donate.css?v=5">

    <!-- Donation Animation CSS -->
    <link rel="stylesheet" href="donation-animation.css?v=4">

    <!-- Footer CSS -->
    <link rel="stylesheet" href="../css/footer.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- =========================================================
         DONATION PAGE
    ========================================================= -->
    <section class="donation-page">
        <div class="donation-page-container">

            <!-- =================================================
                 LEFT COLUMN: STORYTELLING & IMPACT
            ================================================== -->
            <div class="donation-introduction">
                <nav class="giving-progress" aria-label="Donation progress">
                    <span class="current" aria-current="step"><b>01</b> Your contribution</span>
                    <span><b>02</b> Payment &amp; verification</span>
                </nav>

                <!-- Small editorial eyebrow -->
                <div class="donation-small-heading">
                    <span></span>
                    SUPPORT SEVARTHA FOUNDATION
                </div>

                <!-- Main heading -->
                <h1>
                    Give with
                    <span>purpose.</span>
                </h1>

                <!-- Lead statement -->
                <p class="donation-lead">
                    Support education, healthcare, nutrition, and compassionate care in the communities we serve across India.
                </p>

                <a class="giving-mobile-link" href="#contribution-form">
                    Make a contribution <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                </a>

                <!-- Featured Impact Visual -->
                <div class="giving-photo-wrapper">
                    <div class="giving-impact-tag">
                        <i class="fa-solid fa-shield-heart" aria-hidden="true"></i>
                        <span>100% Direct Impact</span>
                    </div>
                    <figure class="giving-photo">
                        <img src="../static_image.php?name=homepage_image1.png" alt="Sevartha Foundation community work in action">
                        <figcaption>
                            <i class="fa-solid fa-heart" aria-hidden="true"></i>
                            Small acts of kindness. Lasting possibilities.
                        </figcaption>
                    </figure>
                </div>

                <!-- Impact Areas Header -->
                <div class="donation-impact-heading">
                    <span>WHERE YOUR SUPPORT CAN HELP</span>
                    <small>Click to select</small>
                </div>

                <!-- Interactive Cause Cards (Synced with Form Dropdown) -->
                <div class="donation-areas" role="listbox" aria-label="Select an area to support">

                    <!-- Education -->
                    <div class="donation-area" data-cause="Education" role="option" tabindex="0" aria-selected="false">
                        <div class="donation-area-icon">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong>Education</strong>
                            <span>Learning opportunities, supplies and school support</span>
                        </div>
                    </div>

                    <!-- Hunger and Poverty -->
                    <div class="donation-area" data-cause="Hunger and Poverty" role="option" tabindex="0" aria-selected="false">
                        <div class="donation-area-icon">
                            <i class="fa-solid fa-bowl-food" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong>Hunger and Poverty</strong>
                            <span>Nutritional meals and essential ration packs</span>
                        </div>
                    </div>

                    <!-- Healthcare and Medical Relief -->
                    <div class="donation-area" data-cause="Healthcare and Medical Relief" role="option" tabindex="0" aria-selected="false">
                        <div class="donation-area-icon">
                            <i class="fa-solid fa-heart-pulse" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong>Healthcare and Medical Relief</strong>
                            <span>Medical checkups, health camps and medicines</span>
                        </div>
                    </div>

                    <!-- Elders -->
                    <div class="donation-area" data-cause="Elders" role="option" tabindex="0" aria-selected="false">
                        <div class="donation-area-icon">
                            <i class="fa-solid fa-person-cane" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong>Elders Care &amp; Dignity</strong>
                            <span>Care, warmth, dignity and essential medical support</span>
                        </div>
                    </div>

                    <!-- Disaster Relief -->
                    <div class="donation-area donation-area-wide" data-cause="Disaster Relief and Emergency Assistance" role="option" tabindex="0" aria-selected="false">
                        <div class="donation-area-icon">
                            <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong>Disaster Relief and Emergency Assistance</strong>
                            <span>Timely crisis rescue, emergency kits and community rehabilitation</span>
                        </div>
                    </div>

                </div>

                <!-- Trust Indicators -->
                <div class="donation-trust-row">
                    <div class="donation-trust-item">
                        <i class="fa-solid fa-shield-check" aria-hidden="true"></i>
                        <span>100% Transparent</span>
                    </div>
                    <div class="donation-trust-item">
                        <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                        <span>Direct Trust Account</span>
                    </div>
                    <div class="donation-trust-item">
                        <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                        <span>Instant Verification</span>
                    </div>
                </div>

            </div>

            <!-- =================================================
                 RIGHT COLUMN: DONATION FORM CARD
            ================================================== -->
            <div class="donation-form-card" id="contribution-form">
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <!-- Form heading -->
                <div class="donation-form-top">
                    <span class="donation-form-kicker">DONATION DETAILS</span>
                    <h2>Make your contribution</h2>
                    <p>A few details help us keep an accurate record of your generous support.</p>
                </div>

                <!-- Donation Details Form -->
                <form action="donate.php" method="POST" id="donationDetailsForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Full Name -->
                    <div class="donation-form-group">
                        <label for="donorName">Full Name</label>
                        <input
                            type="text"
                            id="donorName"
                            name="donor_name"
                            value="<?php echo htmlspecialchars($_POST['donor_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            maxlength="100"
                            required>
                    </div>

                    <!-- Email Address -->
                    <div class="donation-form-group">
                        <label for="donorEmail">Email Address</label>
                        <input
                            type="email"
                            id="donorEmail"
                            name="donor_email"
                            value="<?php echo htmlspecialchars($_POST['donor_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="you@example.com"
                            autocomplete="email"
                            maxlength="150"
                            required>
                    </div>

                    <!-- Phone Number (Optional) -->
                    <div class="donation-form-group">
                        <div class="donation-label-row">
                            <label for="donorPhone">Phone Number</label>
                            <span>Optional</span>
                        </div>
                        <input
                            type="tel"
                            id="donorPhone"
                            name="donor_phone"
                            value="<?php echo htmlspecialchars($_POST['donor_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter your phone number"
                            autocomplete="tel"
                            maxlength="20">
                    </div>

                    <!-- Purpose Selection -->
                    <div class="donation-form-group">
                        <label for="donationPurpose">I'd like my contribution to support</label>
                        <select id="donationPurpose" name="donation_purpose" required>
                            <option value="" <?php echo empty($_POST['donation_purpose']) ? 'selected' : ''; ?> disabled>Select an area</option>
                            <option value="Education" <?php echo (($_POST['donation_purpose'] ?? '') === 'Education') ? 'selected' : ''; ?>>Education</option>
                            <option value="Hunger and Poverty" <?php echo (($_POST['donation_purpose'] ?? '') === 'Hunger and Poverty') ? 'selected' : ''; ?>>Hunger and Poverty</option>
                            <option value="Healthcare and Medical Relief" <?php echo (($_POST['donation_purpose'] ?? '') === 'Healthcare and Medical Relief') ? 'selected' : ''; ?>>Healthcare and Medical Relief</option>
                            <option value="Elders" <?php echo (($_POST['donation_purpose'] ?? '') === 'Elders') ? 'selected' : ''; ?>>Elders</option>
                            <option value="Disaster Relief and Emergency Assistance" <?php echo (($_POST['donation_purpose'] ?? '') === 'Disaster Relief and Emergency Assistance') ? 'selected' : ''; ?>>Disaster Relief and Emergency Assistance</option>
                        </select>
                    </div>

                    <!-- Contribution Amount -->
                    <div class="donation-form-group">
                        <label for="donationAmount">Contribution Amount</label>

                        <!-- Presets with Impact Tags -->
                        <div class="donation-amount-options">
                            <button type="button" class="donation-amount-option" aria-pressed="false" data-amount="500">
                                <span>₹500</span>
                                <small>Essential kits</small>
                            </button>
                            <button type="button" class="donation-amount-option" aria-pressed="false" data-amount="1000">
                                <span>₹1,000</span>
                                <small>Family meals</small>
                            </button>
                            <button type="button" class="donation-amount-option" aria-pressed="false" data-amount="2500">
                                <span>₹2,500</span>
                                <small>Medical aid</small>
                            </button>
                            <button type="button" class="donation-amount-option" aria-pressed="false" data-amount="5000">
                                <span>₹5,000</span>
                                <small>Holistic care</small>
                            </button>
                        </div>

                        <!-- Custom Amount Input -->
                        <div class="donation-custom-amount">
                            <span>₹</span>
                            <input
                                type="number"
                                id="donationAmount"
                                name="donation_amount"
                                value="<?php echo htmlspecialchars($_POST['donation_amount'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Or enter another amount"
                                min="1"
                                max="10000000"
                                step="1"
                                required>
                        </div>
                    </div>

                    <!-- Live Contribution Summary Drawer -->
                    <div class="giving-preview">
                        <div class="giving-preview-label">
                            <span>Your Contribution</span>
                            <small id="givingPurposePreview">General support</small>
                        </div>
                        <strong id="givingAmountPreview" role="status" aria-live="polite">Your choice</strong>
                    </div>

                    <!-- Privacy Assurance -->
                    <div class="donation-form-notice">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <p>We record your contribution with high privacy standards and only use your details for verification.</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="donation-continue">
                        <span>Continue to Payment</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>

                    <p class="donation-payment-note">Next: Review your donation details and pay with UPI.</p>

                </form>
            </div>

        </div>
    </section>

    <!-- =========================================================
         BOTTOM TRUST MESSAGE
    ========================================================= -->
    <section class="donation-bottom">
        <div class="donation-bottom-inner">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <div>
                <strong>Thank you for supporting Sevartha Foundation.</strong>
                <p>Your contribution helps us continue work that is rooted in people, dignity, and meaningful community action.</p>
            </div>
        </div>
    </section>

    <!-- =========================================================
         FOOTER
    ========================================================= -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="giving.js?v=4"></script>
</body>

</html>