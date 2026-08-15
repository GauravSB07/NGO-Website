<?php

session_start();


/*
 * Accept donation details from the first page.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name =
        trim($_POST['donor_name'] ?? '');

    $email =
        trim($_POST['donor_email'] ?? '');

    $phone =
        trim($_POST['donor_phone'] ?? '');

    $purpose =
        trim($_POST['donation_purpose'] ?? '');

    $amount =
        filter_input(
            INPUT_POST,
            'donation_amount',
            FILTER_VALIDATE_INT
        );


    /*
     * Basic validation.
     */

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $purpose === '' ||
        !$amount ||
        $amount < 1
    ) {

        header('Location: donate.php');

        exit;

    }


    /*
     * Store the donation information temporarily
     * for the payment page.
     */

    $_SESSION['donation_name'] =
        $name;

    $_SESSION['donation_email'] =
        $email;

    $_SESSION['donation_phone'] =
        $phone;

    $_SESSION['donation_purpose'] =
        $purpose;

    $_SESSION['donation_amount'] =
        $amount;

}


/*
 * If there is no active donation,
 * return to the donation page.
 */

if (
    empty($_SESSION['donation_name']) ||
    empty($_SESSION['donation_email']) ||
    empty($_SESSION['donation_amount'])
) {

    header('Location: donate.php');

    exit;

}


$name =
    $_SESSION['donation_name'];

$email =
    $_SESSION['donation_email'];

$purpose =
    $_SESSION['donation_purpose'];

$amount =
    (int) $_SESSION['donation_amount'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Make Your Payment | Sevartha Foundation</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="../css/navbar.css">

    <link rel="stylesheet" href="donate.css">

    <link rel="stylesheet" href="../css/footer.css">



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

<section class="payment-page">

    <div class="payment-container">


        <!-- TOP -->

        <div class="payment-heading">

            <div class="donation-small-heading">

                <span></span>

                PAYMENT

            </div>


            <h1>
                You're almost there.
            </h1>


            <p>
                Review your contribution below and complete your
                payment using UPI.
            </p>

        </div>



        <!-- PAYMENT LAYOUT -->

        <div class="payment-layout">


            <!-- LEFT SUMMARY -->

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
                                substr($name, 0, 1)
                            )
                        );
                        ?>

                    </div>


                    <div>

                        <strong>
                            <?php
                            echo htmlspecialchars($name);
                            ?>
                        </strong>

                        <span>
                            <?php
                            echo htmlspecialchars($email);
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
                            echo htmlspecialchars($purpose);
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



            <!-- RIGHT PAYMENT -->

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


                <!-- QR -->

                <div class="payment-qr-area">


                    <div class="payment-qr">

                        <img
                            src="../images/donation-qr.png"
                            alt="Sevartha Foundation UPI payment QR code"
                        >

                    </div>


                    <p class="payment-qr-caption">

                        Scan with Google Pay, PhonePe, Paytm
                        or another supported UPI app.

                    </p>

                </div>



                <!-- UPI -->

                <div class="payment-upi">

                    <div>

                        <span>
                            UPI ID
                        </span>

                        <strong id="upiId">
                            your-upi-id@upi
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



                <!-- SAFETY -->

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



                <!-- CONFIRM -->

                <form
                    action="../contact.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="donor_name"
                        value="<?php
                        echo htmlspecialchars($name);
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="donor_email"
                        value="<?php
                        echo htmlspecialchars($email);
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="donor_phone"
                        value="<?php
                        echo htmlspecialchars(
                            $_SESSION['donation_phone'] ?? ''
                        );
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="donation_purpose"
                        value="<?php
                        echo htmlspecialchars($purpose);
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="donation_amount"
                        value="<?php
                        echo $amount;
                        ?>"
                    >


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

            </div>

        </div>

    </div>

</section>



<!-- Footer -->

<?php include __DIR__ . '/../includes/footer.php'; ?>



<script>

document.addEventListener("DOMContentLoaded", function () {


    const copyButton =
        document.getElementById("copyUpi");

    const upiId =
        document.getElementById("upiId");

    const copyMessage =
        document.getElementById("copyMessage");


    copyButton.addEventListener("click", async function () {


        try {

            await navigator.clipboard.writeText(
                upiId.textContent.trim()
            );


            copyButton.innerHTML =
                '<i class="fa-solid fa-check"></i> Copied';


            copyMessage.classList.add("show");


            setTimeout(function () {

                copyButton.innerHTML =
                    '<i class="fa-regular fa-copy"></i> Copy';

                copyMessage.classList.remove("show");

            }, 2000);


        } catch (error) {

            copyMessage.textContent =
                "Please copy the UPI ID manually.";

            copyMessage.classList.add("show");

        }

    });

});

</script>


</body>

</html>