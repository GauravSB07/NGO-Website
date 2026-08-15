<?php
session_start();

/*
 * Clear any previous donation information when
 * the donor starts a new donation.
 */
unset(
    $_SESSION['donation_name'],
    $_SESSION['donation_email'],
    $_SESSION['donation_phone'],
    $_SESSION['donation_purpose'],
    $_SESSION['donation_amount']
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

    <title>Donate | Sevartha Foundation</title>


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

    <link rel="stylesheet" href="../css/footer.css">



    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

</head>


<body>


<?php include __DIR__ . '/../includes/navbar.php'; ?>


<!-- =========================================================
     DONATION PAGE
========================================================= -->

<section class="donation-page">

    <div class="donation-page-container">


        <!-- =================================================
             INTRODUCTION
        ================================================== -->

        <div class="donation-introduction">


            <!-- Small editorial heading -->

            <div class="donation-small-heading">

                <span></span>

                SUPPORT SEVARTHA

            </div>


            <!-- Main heading -->

            <h1>

                Give with
                <span>purpose.</span>

            </h1>


            <!-- Lead statement -->

            <p class="donation-lead">

                Your contribution can help Sevartha Foundation
                continue working alongside people and communities
                where support is needed most.

            </p>


            <!-- Supporting statement -->

            <p class="donation-description">

                Choose an area that matters to you, tell us a little
                about yourself, and select the contribution you would
                like to make. Every donation becomes part of the work
                we carry forward together.

            </p>


            <!-- =================================================
                 CONTRIBUTION AREAS
            ================================================== -->

            <div class="donation-impact-heading">

                <span>
                    WHERE YOUR SUPPORT CAN HELP
                </span>

            </div>


            <div class="donation-areas">


                <!-- Education -->

                <div class="donation-area">

                    <div class="donation-area-icon">

                        <i class="fa-solid fa-book-open"></i>

                    </div>

                    <div>

                        <strong>
                            Education
                        </strong>

                        <span>
                            Learning opportunities and support
                        </span>

                    </div>

                </div>


                <!-- Hunger and Poverty -->

                <div class="donation-area">

                    <div class="donation-area-icon">

                        <i class="fa-solid fa-bowl-food"></i>

                    </div>

                    <div>

                        <strong>
                            Hunger and Poverty
                        </strong>

                        <span>
                            Essential support for vulnerable communities
                        </span>

                    </div>

                </div>


                <!-- Healthcare -->

                <div class="donation-area">

                    <div class="donation-area-icon">

                        <i class="fa-solid fa-heart-pulse"></i>

                    </div>

                    <div>

                        <strong>
                            Healthcare and Medical Relief
                        </strong>

                        <span>
                            Healthcare assistance and medical support
                        </span>

                    </div>

                </div>


                <!-- Elders -->

                <div class="donation-area">

                    <div class="donation-area-icon">

                        <i class="fa-solid fa-person-cane"></i>

                    </div>

                    <div>

                        <strong>
                            Elders
                        </strong>

                        <span>
                            Care, dignity and essential assistance
                        </span>

                    </div>

                </div>


                <!-- Disaster Relief -->

                <div class="donation-area donation-area-wide">

                    <div class="donation-area-icon">

                        <i class="fa-solid fa-hand-holding-heart"></i>

                    </div>

                    <div>

                        <strong>
                            Disaster Relief and Emergency Assistance
                        </strong>

                        <span>
                            Timely help during emergencies and crises
                        </span>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 TRUST INFORMATION
            ================================================== -->

            <div class="donation-trust-row">


                <div class="donation-trust-item">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Secure information
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-receipt"></i>

                    <span>
                        Clear donation record
                    </span>

                </div>


                <div class="donation-trust-item">

                    <i class="fa-solid fa-hand-holding-heart"></i>

                    <span>
                        Purpose-led giving
                    </span>

                </div>


            </div>


            <!-- =================================================
                 INFORMATION NOTE
            ================================================== -->

            <div class="donation-note-left">

                <i class="fa-solid fa-circle-info"></i>

                <p>

                    We ask for a few details so we can identify
                    your contribution correctly and contact you
                    only when necessary regarding your donation.

                </p>

            </div>


        </div>



        <!-- =================================================
             DONATION FORM
        ================================================== -->

        <div class="donation-form-card">


            <!-- Form heading -->

            <div class="donation-form-top">

                <span class="donation-form-kicker">
                    DONATION DETAILS
                </span>

                <h2>
                    Make your contribution
                </h2>

                <p>
                    A few details help us keep an accurate record
                    of your support.
                </p>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                action="payment.php"
                method="POST"
                id="donationDetailsForm"
            >


                <!-- =============================================
                     NAME
                ============================================== -->

                <div class="donation-form-group">

                    <label for="donorName">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="donorName"
                        name="donor_name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        maxlength="100"
                        required
                    >

                </div>



                <!-- =============================================
                     EMAIL
                ============================================== -->

                <div class="donation-form-group">

                    <label for="donorEmail">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="donorEmail"
                        name="donor_email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        maxlength="150"
                        required
                    >

                </div>



                <!-- =============================================
                     PHONE
                ============================================== -->

                <div class="donation-form-group">

                    <div class="donation-label-row">

                        <label for="donorPhone">
                            Phone Number
                        </label>

                        <span>
                            Optional
                        </span>

                    </div>

                    <input
                        type="tel"
                        id="donorPhone"
                        name="donor_phone"
                        placeholder="Enter your phone number"
                        autocomplete="tel"
                        maxlength="20"
                    >

                </div>



                <!-- =============================================
                     PURPOSE
                ============================================== -->

                <div class="donation-form-group">

                    <label for="donationPurpose">

                        I'd like my contribution to support

                    </label>


                    <select
                        id="donationPurpose"
                        name="donation_purpose"
                        required
                    >

                        <option
                            value=""
                            selected
                            disabled
                        >
                            Select an area
                        </option>


                        <option value="Education">
                            Education
                        </option>


                        <option value="Hunger and Poverty">
                            Hunger and Poverty
                        </option>


                        <option value="Healthcare and Medical Relief">
                            Healthcare and Medical Relief
                        </option>


                        <option value="Elders">
                            Elders
                        </option>


                        <option value="Disaster Relief and Emergency Assistance">
                            Disaster Relief and Emergency Assistance
                        </option>

                    </select>

                </div>



                <!-- =============================================
                     AMOUNT
                ============================================== -->

                <div class="donation-form-group">

                    <label>
                        Contribution Amount
                    </label>


                    <div class="donation-amount-options">


                        <button
                            type="button"
                            class="donation-amount-option"
                            data-amount="500"
                        >
                            ₹500
                        </button>


                        <button
                            type="button"
                            class="donation-amount-option"
                            data-amount="1000"
                        >
                            ₹1,000
                        </button>


                        <button
                            type="button"
                            class="donation-amount-option"
                            data-amount="2500"
                        >
                            ₹2,500
                        </button>


                        <button
                            type="button"
                            class="donation-amount-option"
                            data-amount="5000"
                        >
                            ₹5,000
                        </button>


                    </div>


                    <div class="donation-custom-amount">

                        <span>
                            ₹
                        </span>

                        <input
                            type="number"
                            id="donationAmount"
                            name="donation_amount"
                            placeholder="Or enter another amount"
                            min="1"
                            max="10000000"
                            step="1"
                            required
                        >

                    </div>

                </div>



                <!-- =============================================
                     PRIVACY / RECORD NOTICE
                ============================================== -->

                <div class="donation-form-notice">

                    <i class="fa-solid fa-lock"></i>

                    <p>

                        Your information is used for maintaining
                        donation records and for necessary
                        donation-related communication.

                    </p>

                </div>



                <!-- =============================================
                     CONTINUE
                ============================================== -->

                <button
                    type="submit"
                    class="donation-continue"
                >

                    <span>
                        Continue to Payment
                    </span>

                    <i class="fa-solid fa-arrow-right"></i>

                </button>


                <p class="donation-payment-note">

                    You will review your contribution details
                    before proceeding to payment.

                </p>


            </form>

        </div>

    </div>

</section>



<!-- =========================================================
     BOTTOM TRUST MESSAGE
========================================================= -->

<section class="donation-bottom">

    <div class="donation-bottom-inner">

        <i class="fa-solid fa-heart"></i>

        <div>

            <strong>
                Thank you for supporting Sevartha Foundation.
            </strong>

            <p>
                Your contribution helps us continue work that
                is rooted in people, dignity and meaningful action.
            </p>

        </div>

    </div>

</section>



<!-- =========================================================
     FOOTER
========================================================= -->

<?php include __DIR__ . '/../includes/footer.php'; ?>



<!-- =========================================================
     DONATION SCRIPT
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    /*
     * Amount selection
     */

    const amountButtons =
        document.querySelectorAll(".donation-amount-option");

    const amountInput =
        document.getElementById("donationAmount");


    amountButtons.forEach(button => {

        button.addEventListener("click", function () {


            amountButtons.forEach(item => {

                item.classList.remove("selected");

            });


            this.classList.add("selected");


            amountInput.value =
                this.dataset.amount;

        });

    });


    /*
     * Remove preset selection when
     * donor enters a custom amount.
     */

    amountInput.addEventListener("input", function () {

        amountButtons.forEach(item => {

            item.classList.remove("selected");

        });

    });


    /*
     * Basic amount validation
     */

    document
        .getElementById("donationDetailsForm")
        .addEventListener("submit", function (event) {


            const amount =
                Number(amountInput.value);


            if (
                !amount ||
                amount < 1 ||
                amount > 10000000
            ) {

                event.preventDefault();

                amountInput.focus();

                return;

            }

        });

});

</script>


</body>

</html>