<?php

session_start();

/*
|--------------------------------------------------------------------------
| PAYMENT PAGE
|--------------------------------------------------------------------------
|
| This page:
|
| 1. Accepts the donation information from donate.php
| 2. Validates the submitted information
| 3. Verifies the CSRF token
| 4. Loads payment settings from the database
| 5. Creates a pending donation record
| 6. Displays the payment information
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
| Donation rules
|--------------------------------------------------------------------------
*/

$allowedPurposes = [

    'Education',

    'Hunger and Poverty',

    'Healthcare and Medical Relief',

    'Elders',

    'Disaster Relief and Emergency Assistance'

];

$minimumDonation = 1;

$maximumDonation = 10000000;


/*
|--------------------------------------------------------------------------
| CSRF VALIDATION
|--------------------------------------------------------------------------
*/

$submittedCsrfToken = $_POST['csrf_token'] ?? '';

$sessionCsrfToken = $_SESSION['csrf_token'] ?? '';


if (
    empty($submittedCsrfToken) ||
    empty($sessionCsrfToken) ||
    !hash_equals(
        $sessionCsrfToken,
        $submittedCsrfToken
    )
) {

    die('Invalid request. Please return to the donation page and try again.');

}


/*
|--------------------------------------------------------------------------
| Retrieve submitted information
|--------------------------------------------------------------------------
*/

$donorName = trim(
    $_POST['donor_name'] ?? ''
);

$donorEmail = trim(
    $_POST['donor_email'] ?? ''
);

$donorPhone = trim(
    $_POST['donor_phone'] ?? ''
);

$donationPurpose = trim(
    $_POST['donation_purpose'] ?? ''
);

$donationAmount = $_POST['donation_amount'] ?? '';


/*
|--------------------------------------------------------------------------
| Validate donor name
|--------------------------------------------------------------------------
*/

if (
    $donorName === '' ||
    mb_strlen($donorName) > 100
) {

    die('Please enter a valid full name.');

}


/*
|--------------------------------------------------------------------------
| Validate email
|--------------------------------------------------------------------------
*/

if (
    $donorEmail === '' ||
    mb_strlen($donorEmail) > 150 ||
    !filter_var(
        $donorEmail,
        FILTER_VALIDATE_EMAIL
    )
) {

    die('Please enter a valid email address.');

}


/*
|--------------------------------------------------------------------------
| Validate phone number
|--------------------------------------------------------------------------
|
| Phone number is optional.
|
*/

if (
    $donorPhone !== '' &&
    (
        mb_strlen($donorPhone) > 20 ||
        !preg_match(
            '/^[0-9+\-\s()]+$/',
            $donorPhone
        )
    )
) {

    die('Please enter a valid phone number.');

}


/*
|--------------------------------------------------------------------------
| Validate donation purpose
|--------------------------------------------------------------------------
*/

if (
    !in_array(
        $donationPurpose,
        $allowedPurposes,
        true
    )
) {

    die('Invalid donation purpose.');

}


/*
|--------------------------------------------------------------------------
| Validate donation amount
|--------------------------------------------------------------------------
*/

if (
    $donationAmount === '' ||
    !is_numeric($donationAmount)
) {

    die('Please enter a valid donation amount.');

}


$donationAmount = (float) $donationAmount;


if (
    $donationAmount < $minimumDonation ||
    $donationAmount > $maximumDonation
) {

    die('The donation amount is outside the allowed range.');

}


/*
|--------------------------------------------------------------------------
| Format amount for database
|--------------------------------------------------------------------------
*/

$donationAmount = number_format(
    $donationAmount,
    2,
    '.',
    ''
);


/*
|--------------------------------------------------------------------------
| Load active payment settings
|--------------------------------------------------------------------------
*/

$paymentSettingsQuery = "
    SELECT
        id,
        upi_id,
        qr_code
    FROM payment_settings
    WHERE is_active = 1
    ORDER BY id DESC
    LIMIT 1
";


$paymentSettingsResult = mysqli_query(
    $conn,
    $paymentSettingsQuery
);


if (!$paymentSettingsResult) {

    die(
        'Unable to load payment settings.'
    );

}


$paymentSettings = mysqli_fetch_assoc(
    $paymentSettingsResult
);


if (!$paymentSettings) {

    die(
        'Payment settings have not been configured yet.'
    );

}


$upiId = trim(
    $paymentSettings['upi_id']
);

$qrCode = trim(
    $paymentSettings['qr_code']
);


/*
|--------------------------------------------------------------------------
| Make sure payment settings are usable
|--------------------------------------------------------------------------
*/

if (
    $upiId === '' ||
    $qrCode === ''
) {

    die(
        'Payment information is incomplete.'
    );

}


/*
|--------------------------------------------------------------------------
| Prevent duplicate donation records
|--------------------------------------------------------------------------
|
| If the visitor refreshes payment.php, we don't want to
| automatically create another pending donation.
|
*/

if (
    isset($_SESSION['donation']['id']) &&
    isset($_SESSION['donation']['csrf_token']) &&
    hash_equals(
        $_SESSION['donation']['csrf_token'],
        $submittedCsrfToken
    )
) {

    $existingDonationId =
        (int) $_SESSION['donation']['id'];

} else {


    /*
    |--------------------------------------------------------------------------
    | Create pending donation
    |--------------------------------------------------------------------------
    */

    $insertQuery = "
        INSERT INTO donations (
            donor_name,
            donor_email,
            donor_phone,
            donation_purpose,
            donation_amount,
            payment_status
        )
        VALUES (?, ?, ?, ?, ?, 'pending')
    ";


    $stmt = mysqli_prepare(
        $conn,
        $insertQuery
    );


    if (!$stmt) {

        die(
            'Unable to prepare donation record.'
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        'ssssd',
        $donorName,
        $donorEmail,
        $donorPhone,
        $donationPurpose,
        $donationAmount
    );


    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        die(
            'Unable to create donation record.'
        );

    }


    $existingDonationId =
        mysqli_insert_id($conn);


    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | Store donation information in session
    |--------------------------------------------------------------------------
    */

    $_SESSION['donation'] = [

        'id' => $existingDonationId,

        'donor_name' => $donorName,

        'donor_email' => $donorEmail,

        'donor_phone' => $donorPhone,

        'donation_purpose' => $donationPurpose,

        'donation_amount' => $donationAmount,

        'csrf_token' => $submittedCsrfToken

    ];

}


/*
|--------------------------------------------------------------------------
| QR code path
|--------------------------------------------------------------------------
|
| The database stores the QR image path.
|
| Example:
|
| uploads/payment/upi-qr.png
|
*/

$qrCodePath = '../' . ltrim(
    $qrCode,
    '/'
);


/*
|--------------------------------------------------------------------------
| Escape values before displaying them
|--------------------------------------------------------------------------
*/

$safeDonorName = htmlspecialchars(
    $donorName,
    ENT_QUOTES,
    'UTF-8'
);

$safeDonorEmail = htmlspecialchars(
    $donorEmail,
    ENT_QUOTES,
    'UTF-8'
);

$safeDonorPhone = htmlspecialchars(
    $donorPhone,
    ENT_QUOTES,
    'UTF-8'
);

$safeDonationPurpose = htmlspecialchars(
    $donationPurpose,
    ENT_QUOTES,
    'UTF-8'
);

$safeUpiId = htmlspecialchars(
    $upiId,
    ENT_QUOTES,
    'UTF-8'
);

$safeQrCodePath = htmlspecialchars(
    $qrCodePath,
    ENT_QUOTES,
    'UTF-8'
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

    <title>Payment | Sevartha Foundation</title>


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
     PAYMENT PAGE
========================================================= -->

<section class="donation-page">

    <div class="donation-page-container">


        <!-- =================================================
             LEFT INFORMATION
        ================================================== -->

        <div class="donation-introduction">


            <div class="donation-small-heading">

                <span></span>

                SECURE YOUR CONTRIBUTION

            </div>


            <h1>

                Review your
                <span>support.</span>

            </h1>


            <p class="donation-lead">

                Thank you,
                <?= $safeDonorName ?>.

                Your contribution details have been recorded
                and are ready for payment.

            </p>


            <p class="donation-description">

                Please review the information below and complete
                your contribution using the payment details provided.

            </p>


            <!-- =================================================
                 DONATION SUMMARY
            ================================================== -->

            <div class="donation-impact-heading">

                <span>
                    YOUR CONTRIBUTION
                </span>

            </div>


            <div class="donation-area">

                <div class="donation-area-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <div>

                    <strong>
                        <?= $safeDonationPurpose ?>
                    </strong>

                    <span>
                        Selected area of support
                    </span>

                </div>

            </div>


            <div class="donation-area">

                <div class="donation-area-icon">

                    <i class="fa-solid fa-indian-rupee-sign"></i>

                </div>

                <div>

                    <strong>
                        ₹<?= number_format(
                            (float) $donationAmount,
                            2
                        ) ?>
                    </strong>

                    <span>
                        Contribution amount
                    </span>

                </div>

            </div>


            <div class="donation-trust-row">


                <div class="donation-trust-item">

                    <i class="fa-solid fa-lock"></i>

                    <span>
                        Secure process
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-receipt"></i>

                    <span>
                        Donation record
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-heart"></i>

                    <span>
                        Purpose-led giving
                    </span>

                </div>


            </div>


        </div>



        <!-- =================================================
             PAYMENT CARD
        ================================================== -->

        <div class="donation-form-card">


            <div class="donation-form-top">

                <span class="donation-form-kicker">
                    PAYMENT
                </span>

                <h2>
                    Complete your contribution
                </h2>

                <p>
                    Scan the QR code or use the UPI ID below.
                </p>

            </div>


            <!-- =================================================
                 QR CODE
            ================================================== -->

            <div
                style="
                    text-align:center;
                    margin:25px 0;
                "
            >

                <div
                    style="
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        padding:18px;
                        background:#ffffff;
                        border:1px solid #ddd;
                        border-radius:12px;
                    "
                >

                    <img
                        src="<?= $safeQrCodePath ?>"
                        alt="Sevartha Foundation UPI QR Code"
                        style="
                            width:240px;
                            height:240px;
                            object-fit:contain;
                            display:block;
                        "
                    >

                </div>

            </div>


            <!-- =================================================
                 UPI ID
            ================================================== -->

            <div
                style="
                    text-align:center;
                    margin-bottom:25px;
                "
            >

                <span
                    style="
                        display:block;
                        font-size:12px;
                        letter-spacing:1.5px;
                        color:#777;
                        margin-bottom:7px;
                    "
                >
                    UPI ID
                </span>


                <strong
                    style="
                        font-size:18px;
                        color:#333;
                        word-break:break-word;
                    "
                >
                    <?= $safeUpiId ?>
                </strong>

            </div>


            <!-- =================================================
                 DONOR INFORMATION
            ================================================== -->

            <div
                style="
                    border-top:1px solid #e5e5e5;
                    padding-top:20px;
                    margin-top:10px;
                "
            >

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:12px;
                    "
                >

                    <span>
                        Donor
                    </span>

                    <strong>
                        <?= $safeDonorName ?>
                    </strong>

                </div>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:12px;
                    "
                >

                    <span>
                        Email
                    </span>

                    <strong>
                        <?= $safeDonorEmail ?>
                    </strong>

                </div>


                <?php if ($donorPhone !== ''): ?>

                    <div
                        style="
                            display:flex;
                            justify-content:space-between;
                            gap:20px;
                            margin-bottom:12px;
                        "
                    >

                        <span>
                            Phone
                        </span>

                        <strong>
                            <?= $safeDonorPhone ?>
                        </strong>

                    </div>

                <?php endif; ?>


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        margin-bottom:12px;
                    "
                >

                    <span>
                        Amount
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $donationAmount,
                            2
                        ) ?>
                    </strong>

                </div>

            </div>


            <!-- =================================================
                 PAYMENT NOTICE
            ================================================== -->

            <div class="donation-form-notice">

                <i class="fa-solid fa-circle-info"></i>

                <p>

                    After completing the payment, you will need
                    to confirm the transaction so that the donation
                    can be verified.

                </p>

            </div>


            <!-- =================================================
                 PAYMENT STATUS BUTTON
            ================================================== -->

            <form
                action="payment-success.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $submittedCsrfToken,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >


                <input
                    type="hidden"
                    name="donation_id"
                    value="<?= (int) $existingDonationId ?>"
                >


                <button
                    type="submit"
                    class="donation-continue"
                >

                    <span>
                        I Have Completed Payment
                    </span>

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>


            <p class="donation-payment-note">

                Your donation will remain pending until the
                payment is verified.

            </p>


        </div>

    </div>

</section>



<?php include __DIR__ . '/../includes/footer.php'; ?>


</body>

</html>