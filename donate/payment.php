<?php

/* =========================================================
   SESSION
========================================================= */

session_start();


/* =========================================================
   DATABASE CONNECTION
========================================================= */

include __DIR__ . '/../config/db.php';


/* =========================================================
   INDIAN TIMEZONE
========================================================= */

date_default_timezone_set('Asia/Kolkata');


/* =========================================================
   ERROR REPORTING
   IMPORTANT DURING DEVELOPMENT
========================================================= */

mysqli_report(MYSQLI_REPORT_OFF);


/* =========================================================
   MESSAGE VARIABLES
========================================================= */

$successMessage = '';
$errorMessage = '';


/* =========================================================
   CHECK SUCCESS MESSAGE
========================================================= */

if (isset($_SESSION['donation_success'])) {

    $successMessage = $_SESSION['donation_success'];

    unset($_SESSION['donation_success']);
}


/* =========================================================
   STEP 1
   RECEIVE DONATION DETAILS FROM donate.php
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


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($name === '') {

        $errorMessage = 'Please enter your full name.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = 'Please enter a valid email address.';

    } elseif ($purpose === '') {

        $errorMessage = 'Please select a donation purpose.';

    } elseif ($amount < 1 || $amount > 10000000) {

        $errorMessage = 'Please enter a valid donation amount.';

    } else {

        /* =================================================
           SAVE DETAILS IN SESSION
        ================================================= */

        $_SESSION['donation_name'] = $name;
        $_SESSION['donation_email'] = $email;
        $_SESSION['donation_phone'] = $phone;
        $_SESSION['donation_purpose'] = $purpose;
        $_SESSION['donation_amount'] = $amount;


        /* =================================================
           REDIRECT TO PAYMENT PAGE
        ================================================= */

        header('Location: payment.php');
        exit;
    }
}


/* =========================================================
   STEP 2
   HANDLE "I'VE COMPLETED THE PAYMENT"
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['payment_completed'])
) {


    /* =====================================================
       CHECK DATABASE CONNECTION
    ===================================================== */

    if (!$conn) {

        $errorMessage =
            'Database connection failed. Please contact the administrator.';

    } else {


        /* =================================================
           GET DONOR DETAILS FROM SESSION
        ================================================= */

        $name = trim($_SESSION['donation_name'] ?? '');

        $email = trim($_SESSION['donation_email'] ?? '');

        $phone = trim($_SESSION['donation_phone'] ?? '');

        $purpose = trim($_SESSION['donation_purpose'] ?? '');

        $amount = (float) ($_SESSION['donation_amount'] ?? 0);


        /* =================================================
           GET TRANSACTION ID
        ================================================= */

        $transactionId =
            trim($_POST['transaction_id'] ?? '');


        /* =================================================
           VALIDATION
        ================================================= */

        if ($name === '') {

            $errorMessage =
                'Donor information is missing. Please start the donation again.';

        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {

            $errorMessage =
                'Donor email is invalid. Please start the donation again.';

        } elseif ($purpose === '') {

            $errorMessage =
                'Donation purpose is missing. Please start the donation again.';

        } elseif ($amount < 1) {

            $errorMessage =
                'Donation amount is invalid. Please start the donation again.';

        } elseif ($transactionId === '') {

            $errorMessage =
                'Please enter your UPI transaction/reference ID.';

        } elseif (strlen($transactionId) < 6) {

            $errorMessage =
                'Please enter a valid UPI transaction/reference ID.';

        } else {


            /* =================================================
               INSERT DONATION
               STATUS = PENDING
            ================================================= */

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


            /* =================================================
               PREPARE
            ================================================= */

            $stmt = mysqli_prepare($conn, $sql);


            if (!$stmt) {

                $errorMessage =
                    'Unable to prepare database request: ' .
                    mysqli_error($conn);

            } else {


                /* =================================================
                   BIND PARAMETERS

                   s = string
                   s = string
                   s = string
                   s = string
                   d = decimal
                   s = string
                ================================================= */

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

                    $errorMessage =
                        'Unable to prepare donation data: ' .
                        mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);

                } else {


                    /* =================================================
                       EXECUTE INSERT
                    ================================================= */

                    $executeResult =
                        mysqli_stmt_execute($stmt);


                    if ($executeResult) {


                        /* =============================================
                           GET INSERTED DONATION ID
                        ============================================= */

                        $donationId =
                            mysqli_insert_id($conn);


                        mysqli_stmt_close($stmt);


                        /* =============================================
                           SUCCESS MESSAGE
                        ============================================= */

                        $_SESSION['donation_success'] =
                            'Your payment details have been submitted successfully. Your donation is now pending verification by the Sevartha Foundation team.';


                        /* =============================================
                           SAVE DONATION ID
                           OPTIONAL BUT USEFUL
                        ============================================= */

                        $_SESSION['last_donation_id'] =
                            $donationId;


                        /* =============================================
                           CLEAR DONATION SESSION
                        ============================================= */

                        unset(
                            $_SESSION['donation_name'],
                            $_SESSION['donation_email'],
                            $_SESSION['donation_phone'],
                            $_SESSION['donation_purpose'],
                            $_SESSION['donation_amount']
                        );


                        /* =============================================
                           REDIRECT AFTER SUCCESS
                        ============================================= */

                        header('Location: payment.php');
                        exit;


                    } else {


                        /* =============================================
                           GET EXACT MYSQL ERROR
                        ============================================= */

                        $databaseError =
                            mysqli_stmt_error($stmt);


                        mysqli_stmt_close($stmt);


                        $errorMessage =
                            'Database insert failed: ' .
                            $databaseError;
                    }
                }
            }
        }
    }
}


/* =========================================================
   SHOW PAYMENT PAGE
========================================================= */

if ($successMessage === '') {


    /* =====================================================
       CHECK ACTIVE DONATION SESSION
    ===================================================== */

    if (
        empty($_SESSION['donation_name']) ||
        empty($_SESSION['donation_email']) ||
        empty($_SESSION['donation_amount'])
    ) {

        header('Location: donate.php');
        exit;
    }


    /* =====================================================
       GET DONATION DETAILS
    ===================================================== */

    $name =
        $_SESSION['donation_name'];

    $email =
        $_SESSION['donation_email'];

    $phone =
        $_SESSION['donation_phone'] ?? '';

    $purpose =
        $_SESSION['donation_purpose'];

    $amount =
        (float) $_SESSION['donation_amount'];


    /* =====================================================
       GET ACTIVE PAYMENT SETTINGS
    ===================================================== */

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

        $settings =
            mysqli_fetch_assoc($settingsQuery);

        if ($settings) {

            $upiId =
                $settings['upi_id'] ?? '';
        }
    }

} else {


    /* =====================================================
       SUCCESS SCREEN VALUES
    ===================================================== */

    $name = '';

    $email = '';

    $phone = '';

    $purpose = '';

    $amount = 0;

    $upiId = '';

    $qrCode = '';
}

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
        Make Your Payment | Sevartha Foundation
    </title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         MAIN WEBSITE CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <!-- =====================================================
         NAVBAR CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/navbar.css"
    >


    <!-- =====================================================
         DONATION CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="donate.css"
    >


    <!-- =====================================================
         FOOTER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/footer.css"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


</head>


<body>


<?php

/* =========================================================
   NAVBAR
========================================================= */

include __DIR__ . '/../includes/navbar.php';

?>


<!-- =========================================================
     SUCCESS MESSAGE
========================================================= -->

<?php if ($successMessage !== ''): ?>


<section class="payment-page">


    <div class="payment-container">


        <div class="payment-card">


            <div class="payment-card-heading">


                <span>
                    PAYMENT
                </span>


                <h2>
                    Payment Submitted
                </h2>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $successMessage
                    );
                    ?>

                </p>


            </div>


            <div class="payment-safety">


                <i class="fa-solid fa-circle-check"></i>


                <div>


                    <strong>
                        Thank you for supporting Sevartha Foundation.
                    </strong>


                    <p>

                        Your payment details have been
                        submitted for verification by our team.

                    </p>


                </div>


            </div>


            <div class="payment-change">


                <a href="../index.php">


                    <i class="fa-solid fa-arrow-left"></i>


                    Back to Website


                </a>


            </div>


        </div>


    </div>


</section>


<?php else: ?>


<!-- =========================================================
     PAYMENT PAGE
========================================================= -->

<section class="payment-page">


    <div class="payment-container">


        <!-- =================================================
             TOP
        ================================================== -->

        <div class="payment-heading">


            <div class="donation-small-heading">


                <span></span>


                PAYMENT


            </div>


            <h1>

                You're almost there.

            </h1>


            <p>

                Review your contribution below and complete
                your payment using UPI.

            </p>


        </div>


        <!-- =================================================
             PAYMENT LAYOUT
        ================================================== -->

        <div class="payment-layout">


            <!-- =================================================
                 LEFT SUMMARY
            ================================================== -->

            <div class="payment-details">


                <div class="payment-details-heading">


                    <span>

                        YOUR CONTRIBUTION

                    </span>


                    <i class="fa-solid fa-receipt"></i>


                </div>


                <div class="payment-person">


                    <div class="payment-avatar">


                        <?php

                        echo strtoupper(
                            htmlspecialchars(
                                substr(
                                    $name,
                                    0,
                                    1
                                )
                            )
                        );

                        ?>


                    </div>


                    <div>


                        <strong>


                            <?php

                            echo htmlspecialchars(
                                $name
                            );

                            ?>


                        </strong>


                        <span>


                            <?php

                            echo htmlspecialchars(
                                $email
                            );

                            ?>


                        </span>


                    </div>


                </div>


                <div class="payment-summary-list">


                    <div>


                        <span>
                            Purpose
                        </span>


                        <strong>


                            <?php

                            echo htmlspecialchars(
                                $purpose
                            );

                            ?>


                        </strong>


                    </div>


                    <div class="payment-total">


                        <span>
                            Donation amount
                        </span>


                        <strong>


                            ₹<?php

                            echo number_format(
                                $amount
                            );

                            ?>


                        </strong>


                    </div>


                </div>


                <div class="payment-change">


                    <a href="donate.php">


                        <i class="fa-solid fa-arrow-left"></i>


                        Change donation details


                    </a>


                </div>


            </div>


            <!-- =================================================
                 RIGHT PAYMENT
            ================================================== -->

            <div class="payment-card">


                <div class="payment-card-heading">


                    <span>
                        STEP 2
                    </span>


                    <h2>

                        Complete your payment

                    </h2>


                    <p>

                        Scan the QR code using your UPI app.

                    </p>


                </div>


                <!-- =================================================
                     QR
                ================================================== -->

                <div class="payment-qr-area">


                    <div class="payment-qr">


                    <img
                        src="qrimg.php"
                        alt="Sevartha Foundation UPI payment QR code"
                    >

                    </div>

                    <p class="payment-qr-caption">

                        Scan with Google Pay, PhonePe, Paytm
                        or another supported UPI app.

                    </p>

                </div>

                <!-- =================================================
                     UPI
                ================================================== -->

                <div class="payment-upi">

                    <div>

                        <span>
                            UPI ID
                        </span>

                        <strong id="upiId">

                            <?php

                            echo htmlspecialchars(
                                $upiId
                            );

                            ?>

                        </strong>

                    </div>

                    <button
                        type="button"
                        id="copyUpi"
                    >

                        <i class="fa-regular fa-copy"></i>

                        Copy


                    </button>


                </div>


                <p
                    class="payment-copy-message"
                    id="copyMessage"
                >

                    UPI ID copied.

                </p>


                <!-- =================================================
                     SAFETY
                ================================================== -->

                <div class="payment-safety">


                    <i class="fa-solid fa-shield-halved"></i>


                    <div>


                        <strong>

                            Before you pay

                        </strong>


                        <p>

                            Check that the recipient name shown
                            in your UPI app belongs to Sevartha
                            Foundation. Never share your UPI PIN,
                            OTP or banking password with anyone.

                        </p>


                    </div>


                </div>


                <!-- =================================================
                     CONFIRM PAYMENT
                ================================================== -->

                <form
                    action="payment.php"
                    method="POST"
                >


                    <input
                        type="hidden"
                        name="payment_completed"
                        value="1"
                    >


                    <!-- =============================================
                         TRANSACTION ID
                    ============================================== -->

                    <div class="form-group mt-4">


                        <label
                            for="transaction_id"
                            class="form-label"
                        >

                            UPI Transaction / Reference ID

                        </label>


                        <input
                            type="text"
                            id="transaction_id"
                            name="transaction_id"
                            class="form-control"
                            placeholder="Enter your UPI transaction/reference ID"
                            maxlength="100"
                            required
                        >


                        <small class="text-muted">

                            You can find this reference number
                            in your UPI payment receipt.

                        </small>


                    </div>


                    <!-- =============================================
                         SUBMIT
                    ============================================== -->

                    <button
                        type="submit"
                        class="payment-complete"
                    >


                        <span>

                            I've Completed the Payment

                        </span>


                        <i class="fa-solid fa-arrow-right"></i>


                    </button>


                </form>


                <p class="payment-final-note">

                    Please use this button only after completing
                    the payment in your UPI application.

                </p>


                <!-- =================================================
                     ERROR MESSAGE
                ================================================== -->

                <?php if ($errorMessage !== ''): ?>


                    <div
                        class="payment-safety mt-3"
                    >


                        <i class="fa-solid fa-circle-exclamation"></i>


                        <div>


                            <strong>

                                Unable to submit payment

                            </strong>


                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $errorMessage
                                );

                                ?>

                            </p>


                        </div>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </div>


</section>


<?php endif; ?>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php

include __DIR__ . '/../includes/footer.php';

?>


<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>


<!-- =========================================================
     COPY UPI
========================================================= -->

<?php if ($successMessage === ''): ?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const copyButton =
            document.getElementById("copyUpi");

        const upiId =
            document.getElementById("upiId");

        const copyMessage =
            document.getElementById("copyMessage");


        if (
            !copyButton ||
            !upiId ||
            !copyMessage
        ) {

            return;

        }


        copyButton.addEventListener(
            "click",
            async function () {

                try {

                    await navigator.clipboard.writeText(
                        upiId.textContent.trim()
                    );


                    copyButton.innerHTML =
                        '<i class="fa-solid fa-check"></i> Copied';


                    copyMessage.classList.add(
                        "show"
                    );


                    setTimeout(
                        function () {

                            copyButton.innerHTML =
                                '<i class="fa-regular fa-copy"></i> Copy';


                            copyMessage.classList.remove(
                                "show"
                            );

                        },
                        2000
                    );


                }

                catch (error) {

                    copyMessage.textContent =
                        "Please copy the UPI ID manually.";

                    copyMessage.classList.add(
                        "show"
                    );

                }

            }
        );

    }
);

</script>


<?php endif; ?>


</body>

</html>