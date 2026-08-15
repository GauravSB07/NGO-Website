<?php

session_start();

/*
|--------------------------------------------------------------------------
| PAYMENT SUBMISSION / CONFIRMATION
|--------------------------------------------------------------------------
|
| This page does NOT verify the actual payment.
|
| Since we are not using a payment gateway, the donor submits their
| transaction / UTR number after making the payment.
|
| The donation remains "pending" until an administrator verifies it.
|
*/


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: donate.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| Check donation session
|--------------------------------------------------------------------------
|
| The donation must have been created by payment.php.
|
*/

if (
    !isset($_SESSION['donation']) ||
    !is_array($_SESSION['donation'])
) {

    die(
        'Your donation session has expired. Please start the donation process again.'
    );

}


$donationSession = $_SESSION['donation'];


/*
|--------------------------------------------------------------------------
| Retrieve CSRF tokens
|--------------------------------------------------------------------------
*/

$submittedCsrfToken = $_POST['csrf_token'] ?? '';

$sessionCsrfToken =
    $donationSession['csrf_token'] ?? '';


/*
|--------------------------------------------------------------------------
| Validate CSRF token
|--------------------------------------------------------------------------
*/

if (
    empty($submittedCsrfToken) ||
    empty($sessionCsrfToken) ||
    !hash_equals(
        $sessionCsrfToken,
        $submittedCsrfToken
    )
) {

    die(
        'Invalid request. Please return to the donation page and try again.'
    );

}


/*
|--------------------------------------------------------------------------
| Retrieve donation ID
|--------------------------------------------------------------------------
*/

$donationId =
    (int) ($donationSession['id'] ?? 0);


if ($donationId <= 0) {

    die(
        'Invalid donation record.'
    );

}


/*
|--------------------------------------------------------------------------
| Retrieve transaction ID / UTR
|--------------------------------------------------------------------------
*/

$transactionId = trim(
    $_POST['transaction_id'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validate transaction ID
|--------------------------------------------------------------------------
|
| We keep this reasonably flexible because UPI transaction/reference
| formats can vary between banks and payment apps.
|
*/

if ($transactionId === '') {

    die(
        'Please enter your UPI transaction ID or UTR number.'
    );

}


if (mb_strlen($transactionId) > 150) {

    die(
        'The transaction ID is too long.'
    );

}


/*
|--------------------------------------------------------------------------
| Basic transaction ID character validation
|--------------------------------------------------------------------------
|
| Allows:
| - letters
| - numbers
| - spaces
| - hyphens
| - underscores
| - dots
|
*/

if (
    !preg_match(
        '/^[A-Za-z0-9._\-\s]+$/',
        $transactionId
    )
) {

    die(
        'Please enter a valid transaction ID or UTR number.'
    );

}


/*
|--------------------------------------------------------------------------
| Payment method
|--------------------------------------------------------------------------
|
| Since the current system is UPI based, we record the method as UPI.
|
*/

$paymentMethod = 'UPI';


/*
|--------------------------------------------------------------------------
| Verify the donation record
|--------------------------------------------------------------------------
|
| We make sure that:
|
| 1. The donation exists
| 2. It belongs to the donor information stored in the session
| 3. It is still pending
|
*/

$donorEmail =
    $donationSession['donor_email'] ?? '';


$verifyQuery = "
    SELECT
        id,
        donor_name,
        donor_email,
        donor_phone,
        donation_purpose,
        donation_amount,
        payment_status,
        transaction_id,
        payment_submitted_at
    FROM donations
    WHERE id = ?
      AND donor_email = ?
    LIMIT 1
";


$verifyStmt = mysqli_prepare(
    $conn,
    $verifyQuery
);


if (!$verifyStmt) {

    die(
        'Unable to verify the donation record.'
    );

}


mysqli_stmt_bind_param(
    $verifyStmt,
    'is',
    $donationId,
    $donorEmail
);


if (!mysqli_stmt_execute($verifyStmt)) {

    mysqli_stmt_close($verifyStmt);

    die(
        'Unable to verify the donation record.'
    );

}


$verifyResult =
    mysqli_stmt_get_result($verifyStmt);


$donation = mysqli_fetch_assoc(
    $verifyResult
);


mysqli_stmt_close($verifyStmt);


if (!$donation) {

    die(
        'Donation record not found.'
    );

}


/*
|--------------------------------------------------------------------------
| Prevent submitting an already completed donation
|--------------------------------------------------------------------------
*/

if (
    $donation['payment_status'] === 'completed'
) {

    die(
        'This donation has already been marked as completed.'
    );

}


/*
|--------------------------------------------------------------------------
| Prevent submitting a failed/cancelled donation
|--------------------------------------------------------------------------
*/

if (
    $donation['payment_status'] === 'failed' ||
    $donation['payment_status'] === 'cancelled'
) {

    die(
        'This donation is no longer available for payment submission.'
    );

}


/*
|--------------------------------------------------------------------------
| Update donation
|--------------------------------------------------------------------------
|
| Important:
|
| payment_status remains "pending".
|
| We are NOT claiming that the payment was successfully verified.
|
*/

$updateQuery = "
    UPDATE donations
    SET
        payment_method = ?,
        transaction_id = ?,
        payment_submitted_at = CURRENT_TIMESTAMP
    WHERE id = ?
      AND payment_status = 'pending'
";


$updateStmt = mysqli_prepare(
    $conn,
    $updateQuery
);


if (!$updateStmt) {

    die(
        'Unable to prepare payment submission.'
    );

}


mysqli_stmt_bind_param(
    $updateStmt,
    'ssi',
    $paymentMethod,
    $transactionId,
    $donationId
);


if (!mysqli_stmt_execute($updateStmt)) {

    mysqli_stmt_close($updateStmt);

    die(
        'Unable to submit payment information.'
    );

}


mysqli_stmt_close($updateStmt);


/*
|--------------------------------------------------------------------------
| Store confirmation information in session
|--------------------------------------------------------------------------
*/

$_SESSION['donation']['payment_submitted'] = true;

$_SESSION['donation']['transaction_id'] =
    $transactionId;

$_SESSION['donation']['payment_method'] =
    $paymentMethod;


/*
|--------------------------------------------------------------------------
| Escape values for display
|--------------------------------------------------------------------------
*/

$safeDonorName = htmlspecialchars(
    $donation['donor_name'],
    ENT_QUOTES,
    'UTF-8'
);

$safeTransactionId = htmlspecialchars(
    $transactionId,
    ENT_QUOTES,
    'UTF-8'
);

$safeDonationPurpose = htmlspecialchars(
    $donation['donation_purpose'],
    ENT_QUOTES,
    'UTF-8'
);

$safeDonationAmount = number_format(
    (float) $donation['donation_amount'],
    2
);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Payment Submitted | Sevartha Foundation
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <!-- Navbar CSS -->

    <link
        rel="stylesheet"
        href="../css/navbar.css"
    >


    <!-- Donation CSS -->

    <link
        rel="stylesheet"
        href="donate.css"
    >


    <!-- Footer CSS -->

    <link
        rel="stylesheet"
        href="../css/footer.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

</head>


<body>


<?php include __DIR__ . '/../includes/navbar.php'; ?>


<!-- =========================================================
     PAYMENT SUBMITTED
========================================================= -->

<section class="donation-page">

    <div class="donation-page-container">


        <!-- =================================================
             LEFT SIDE
        ================================================== -->

        <div class="donation-introduction">


            <div class="donation-small-heading">

                <span></span>

                PAYMENT SUBMITTED

            </div>


            <h1>

                Thank you for
                <span>your support.</span>

            </h1>


            <p class="donation-lead">

                Thank you,
                <?= $safeDonorName ?>.

                Your payment information has been submitted
                successfully.

            </p>


            <p class="donation-description">

                Your donation is now waiting for verification.
                We will review the transaction details before
                marking the contribution as completed.

            </p>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div class="donation-impact-heading">

                <span>
                    DONATION STATUS
                </span>

            </div>


            <div class="donation-area">

                <div class="donation-area-icon">

                    <i class="fa-solid fa-clock"></i>

                </div>

                <div>

                    <strong>
                        Pending verification
                    </strong>

                    <span>
                        Your payment is awaiting verification
                    </span>

                </div>

            </div>


            <div class="donation-area">

                <div class="donation-area-icon">

                    <i class="fa-solid fa-receipt"></i>

                </div>

                <div>

                    <strong>
                        <?= $safeTransactionId ?>
                    </strong>

                    <span>
                        Submitted transaction reference
                    </span>

                </div>

            </div>


            <div class="donation-trust-row">


                <div class="donation-trust-item">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Recorded securely
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-clock"></i>

                    <span>
                        Verification pending
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-heart"></i>

                    <span>
                        Thank you
                    </span>

                </div>


            </div>


        </div>



        <!-- =================================================
             RIGHT SIDE
        ================================================== -->

        <div class="donation-form-card">


            <div class="donation-form-top">

                <span class="donation-form-kicker">
                    DONATION RECEIPT
                </span>

                <h2>
                    Contribution recorded
                </h2>

                <p>
                    Keep your transaction reference for your records.
                </p>

            </div>


            <!-- =================================================
                 SUCCESS ICON
            ================================================== -->

            <div
                style="
                    text-align:center;
                    margin:25px 0 30px;
                "
            >

                <div
                    style="
                        width:72px;
                        height:72px;
                        margin:0 auto;
                        border:1px solid #d8d8d8;
                        border-radius:50%;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        background:#f7f5f1;
                    "
                >

                    <i
                        class="fa-solid fa-check"
                        style="
                            font-size:28px;
                            color:#555;
                        "
                    ></i>

                </div>

            </div>


            <!-- =================================================
                 DONATION DETAILS
            ================================================== -->

            <div
                style="
                    border-top:1px solid #e5e5e5;
                    padding-top:20px;
                "
            >


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:15px;
                    "
                >

                    <span>
                        Purpose
                    </span>

                    <strong
                        style="
                            text-align:right;
                        "
                    >
                        <?= $safeDonationPurpose ?>
                    </strong>

                </div>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:15px;
                    "
                >

                    <span>
                        Amount
                    </span>

                    <strong>
                        ₹<?= $safeDonationAmount ?>
                    </strong>

                </div>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:15px;
                    "
                >

                    <span>
                        Payment method
                    </span>

                    <strong>
                        UPI
                    </strong>

                </div>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:15px;
                    "
                >

                    <span>
                        Transaction ID
                    </span>

                    <strong
                        style="
                            text-align:right;
                            word-break:break-word;
                        "
                    >
                        <?= $safeTransactionId ?>
                    </strong>

                </div>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:5px;
                    "
                >

                    <span>
                        Status
                    </span>

                    <strong>
                        Pending verification
                    </strong>

                </div>


            </div>


            <!-- =================================================
                 IMPORTANT NOTICE
            ================================================== -->

            <div class="donation-form-notice">

                <i class="fa-solid fa-circle-info"></i>

                <p>

                    This page confirms that your payment information
                    was submitted. It does not mean that the payment
                    has been independently verified yet.

                </p>

            </div>


            <!-- =================================================
                 RETURN BUTTON
            ================================================== -->

            <a
                href="../index.php"
                class="donation-continue"
                style="
                    text-decoration:none;
                "
            >

                <span>
                    Return to Sevartha Foundation
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>


            <p class="donation-payment-note">

                Please keep your transaction ID for your records.

            </p>


        </div>

    </div>

</section>



<?php include __DIR__ . '/../includes/footer.php'; ?>


</body>

</html>