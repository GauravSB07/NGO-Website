<?php

/* =========================================================
   SESSION
========================================================= */

session_start();


/* =========================================================
   DATABASE CONNECTION
========================================================= */

include '../config/db.php';


/* =========================================================
   MESSAGE VARIABLES
========================================================= */

$successMessage = '';
$errorMessage = '';


/* =========================================================
   FORM VALUES
========================================================= */

$full_name = '';
$city = '';
$email = '';
$phone = '';
$availability = '';
$previous_experience = '';
$message = '';
$selectedInterests = [];


/* =========================================================
   CHECK FOR ONE-TIME SUCCESS MESSAGE
========================================================= */

if (isset($_SESSION['volunteer_success'])) {

    $successMessage = $_SESSION['volunteer_success'];

    unset($_SESSION['volunteer_success']);
}


/* =========================================================
   HANDLE VOLUNTEER FORM SUBMISSION
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /* -----------------------------------------------------
       GET FORM DATA
    ----------------------------------------------------- */

    $full_name = trim($_POST['full_name'] ?? '');

    $city = trim($_POST['city'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $phone = trim($_POST['phone'] ?? '');

    $availability =
        trim($_POST['availability'] ?? '');

    $previous_experience =
        trim($_POST['previous_experience'] ?? '');

    $message =
        trim($_POST['message'] ?? '');


    /* -----------------------------------------------------
       GET INTERESTS
    ----------------------------------------------------- */

    $selectedInterests =
        $_POST['interest'] ?? [];


    if (!is_array($selectedInterests)) {

        $selectedInterests = [];
    }


    /*
     * Convert multiple selected interests
     * into one database value.
     */

    $interests =
        implode(', ', $selectedInterests);


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($full_name === '') {

        $errorMessage =
            "Please enter your full name.";
    } elseif ($email === '') {

        $errorMessage =
            "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage =
            "Please enter a valid email address.";
    } elseif ($phone === '') {

        $errorMessage =
            "Please enter your phone number.";
    } elseif (empty($selectedInterests)) {

        $errorMessage =
            "Please select at least one area where you would like to help.";
    } elseif ($availability === '') {

        $errorMessage =
            "Please select your availability.";
    } elseif ($previous_experience === '') {

        $errorMessage =
            "Please select whether you have previous volunteering experience.";
    } else {


        /* =================================================
           INSERT INTO DATABASE
        ================================================== */

        $sql = "
            INSERT INTO volunteer_applications
            (
                full_name,
                city,
                email,
                phone,
                interests,
                availability,
                previous_experience,
                message
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        /* -------------------------------------------------
           CHECK PREPARED STATEMENT
        ------------------------------------------------- */

        if (!$stmt) {

            $errorMessage =
                "Unable to process your application at the moment.";
        } else {


            /* -------------------------------------------------
               BIND PARAMETERS
            ------------------------------------------------- */

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssss",
                $full_name,
                $city,
                $email,
                $phone,
                $interests,
                $availability,
                $previous_experience,
                $message
            );


            /* -------------------------------------------------
               EXECUTE
            ------------------------------------------------- */

            if (mysqli_stmt_execute($stmt)) {


                /* ---------------------------------------------
                   SAVE ONE-TIME SUCCESS MESSAGE
                --------------------------------------------- */

                $_SESSION['volunteer_success'] =
                    "Thank you for applying to volunteer with Sevartha Foundation. We will contact you soon.";


                /* ---------------------------------------------
                   CLOSE STATEMENT
                --------------------------------------------- */

                mysqli_stmt_close($stmt);


                /* ---------------------------------------------
                   REDIRECT
                --------------------------------------------- */

                header("Location: volunteer.php");
                exit;
            } else {

                $errorMessage =
                    "Something went wrong while submitting your application. Please try again.";


                mysqli_stmt_close($stmt);
            }
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">


<head>


    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">


    <title>
        Volunteer With Us | Sevartha Foundation
    </title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- =====================================================
         MAIN WEBSITE CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/style.css">


    <!-- =====================================================
         NAVBAR CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/navbar.css">


    <!-- =====================================================
         FOOTER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/footer.css">


    <!-- =====================================================
         VOLUNTEER PAGE CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/volunteer.css">


    <!-- =====================================================
         SCROLL CONTENT ANIMATION CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/scroll-content.css">


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


</head>


<body>


    <?php

    /* =========================================================
   NAVBAR
========================================================= */

    include '../includes/navbar.php';

    ?>


    <!-- =========================================================
     HERO
========================================================= -->

    <section class="volunteer-hero">


        <div class="volunteer-container">


            <div class="volunteer-eyebrow scroll-heading">

                <span></span>

                SEVARTHA FOUNDATION

            </div>


            <h1 class="volunteer-title scroll-heading scroll-delay-1">

                Volunteer

                <span>
                    With Us
                </span>

            </h1>


            <p class="volunteer-intro scroll-paragraph scroll-delay-2">

                Be a part of the change and help us make a
                meaningful difference in the lives of others.

            </p>


        </div>


    </section>


    <!-- =========================================================
     INTRODUCTION
========================================================= -->

    <section class="volunteer-intro-section">


        <div class="volunteer-container">


            <div class="volunteer-intro-content">


                <div class="volunteer-label scroll-heading">

                    <span></span>

                    JOIN THE MOVEMENT

                </div>


                <h2 class="scroll-heading scroll-delay-1">

                    Your Time Can

                    <span>
                        Make a Difference
                    </span>

                </h2>


                <p class="scroll-paragraph scroll-delay-2">

                    Volunteering with Sevartha Foundation gives you
                    an opportunity to contribute your time, skills
                    and energy towards creating a positive impact
                    in the community.

                </p>


                <p class="scroll-paragraph scroll-delay-3">

                    Whether you want to support education, healthcare,
                    food distribution, elderly care or disaster relief,
                    there is always a way for you to contribute.

                </p>


            </div>


        </div>


    </section>


    <!-- =========================================================
     VOLUNTEER FORM
========================================================= -->

    <section class="volunteer-form-section">


        <div class="volunteer-container">


            <div class="volunteer-form-wrapper">


                <!-- =================================================
                 FORM INTRO
            ================================================== -->

                <div class="volunteer-form-header">


                    <div class="volunteer-label scroll-heading">

                        <span></span>

                        VOLUNTEER REGISTRATION

                    </div>


                    <h2 class="scroll-heading scroll-delay-1">

                        Become a

                        <span>
                            Volunteer
                        </span>

                    </h2>


                    <p class="scroll-paragraph scroll-delay-2">

                        Fill in the details below and take the
                        first step towards becoming a part of
                        Sevartha Foundation.

                    </p>


                </div>


                <!-- =================================================
                 FORM
            ================================================== -->

                <form
                    class="volunteer-form"
                    id="volunteerForm"
                    action=""
                    method="POST">


                    <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                    <?php if ($successMessage !== ''): ?>

                        <div
                            id="formMessage"
                            class="form-message success scroll-content"
                            aria-live="polite">

                            <i class="fa-solid fa-circle-check"></i>

                            <?php
                            echo htmlspecialchars(
                                $successMessage
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                     ERROR MESSAGE
                ================================================== -->

                    <?php if ($errorMessage !== ''): ?>

                        <div
                            id="formMessage"
                            class="form-message error scroll-content"
                            aria-live="polite">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            <?php
                            echo htmlspecialchars(
                                $errorMessage
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                    <!-- =========================================
                     PERSONAL DETAILS
                ========================================== -->

                    <div class="form-section-title scroll-heading">

                        <i class="fa-solid fa-user"></i>

                        Personal Details

                    </div>


                    <div class="form-row scroll-content scroll-delay-1">


                        <!-- FULL NAME -->

                        <div class="form-group">


                            <label for="full_name">

                                Full Name

                                <span>*</span>

                            </label>


                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($full_name); ?>"
                                maxlength="100"
                                required>


                        </div>


                        <!-- CITY -->

                        <div class="form-group">


                            <label for="city">

                                City

                            </label>


                            <input
                                type="text"
                                id="city"
                                name="city"
                                placeholder="Enter your city"
                                value="<?php echo htmlspecialchars($city); ?>"
                                maxlength="100">


                        </div>


                    </div>


                    <div class="form-row scroll-content scroll-delay-2">


                        <!-- EMAIL -->

                        <div class="form-group">


                            <label for="email">

                                Email Address

                                <span>*</span>

                            </label>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                maxlength="150"
                                required>


                        </div>


                        <!-- PHONE -->

                        <div class="form-group">


                            <label for="phone">

                                Phone Number

                                <span>*</span>

                            </label>


                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                placeholder="Enter your phone number"
                                value="<?php echo htmlspecialchars($phone); ?>"
                                maxlength="30"
                                required>


                        </div>


                    </div>


                    <!-- =========================================
                     INTEREST AREAS
                ========================================== -->

                    <div class="form-section-title scroll-heading">

                        <i class="fa-solid fa-hand-holding-heart"></i>

                        How Would You Like to Help?

                    </div>


                    <p class="form-help-text scroll-paragraph">

                        Select one or more areas where you would
                        like to contribute.

                    </p>


                    <div class="checkbox-group scroll-content scroll-delay-1">
                        <div class="form-row">


                            <!-- EDUCATION -->

                            <label class="interest-option scroll-content scroll-delay-1">


                                <input
                                    type="checkbox"
                                    name="interest[]"
                                    value="Education"
                                    <?php
                                    if (
                                        in_array(
                                            'Education',
                                            $selectedInterests
                                        )
                                    ) {
                                        echo 'checked';
                                    }
                                    ?>>


                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>


                                <span class="interest-content">

                                    <i class="fa-solid fa-book-open"></i>

                                    <span>

                                        <strong>
                                            Education
                                        </strong>

                                        <small>
                                            Supporting children and learning
                                        </small>

                                    </span>

                                </span>


                            </label>


                            <!-- FOOD -->

                            <label class="interest-option scroll-content scroll-delay-2">


                                <input
                                    type="checkbox"
                                    name="interest[]"
                                    value="Food and Poverty Relief"
                                    <?php
                                    if (
                                        in_array(
                                            'Food and Poverty Relief',
                                            $selectedInterests
                                        )
                                    ) {
                                        echo 'checked';
                                    }
                                    ?>>


                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>


                                <span class="interest-content">

                                    <i class="fa-solid fa-bowl-food"></i>

                                    <span>

                                        <strong>
                                            Food & Poverty Relief
                                        </strong>

                                        <small>
                                            Helping vulnerable communities
                                        </small>

                                    </span>

                                </span>


                            </label>
                        </div>
                        <div class="form-row">

                            <!-- HEALTHCARE -->

                            <label class="interest-option scroll-content scroll-delay-3">


                                <input
                                    type="checkbox"
                                    name="interest[]"
                                    value="Healthcare"
                                    <?php
                                    if (
                                        in_array(
                                            'Healthcare',
                                            $selectedInterests
                                        )
                                    ) {
                                        echo 'checked';
                                    }
                                    ?>>


                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>


                                <span class="interest-content">

                                    <i class="fa-solid fa-heart-pulse"></i>

                                    <span>

                                        <strong>
                                            Healthcare
                                        </strong>

                                        <small>
                                            Supporting health initiatives
                                        </small>

                                    </span>

                                </span>


                            </label>


                            <!-- ELDERLY CARE -->

                            <label class="interest-option scroll-content scroll-delay-4">


                                <input
                                    type="checkbox"
                                    name="interest[]"
                                    value="Elderly Care"
                                    <?php
                                    if (
                                        in_array(
                                            'Elderly Care',
                                            $selectedInterests
                                        )
                                    ) {
                                        echo 'checked';
                                    }
                                    ?>>


                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>


                                <span class="interest-content">

                                    <i class="fa-solid fa-person-cane"></i>

                                    <span>

                                        <strong>
                                            Elderly Care
                                        </strong>

                                        <small>
                                            Supporting senior citizens
                                        </small>

                                    </span>

                                </span>


                            </label>

                        </div>
                        <div class="form-row">
                            <!-- DISASTER RELIEF -->

                            <label class="interest-option scroll-content scroll-delay-4">


                                <input
                                    type="checkbox"
                                    name="interest[]"
                                    value="Disaster Relief"
                                    <?php
                                    if (
                                        in_array(
                                            'Disaster Relief',
                                            $selectedInterests
                                        )
                                    ) {
                                        echo 'checked';
                                    }
                                    ?>>


                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>


                                <span class="interest-content">

                                    <i class="fa-solid fa-house-medical"></i>

                                    <span>

                                        <strong>
                                            Disaster Relief
                                        </strong>

                                        <small>
                                            Helping during emergencies
                                        </small>

                                    </span>

                                </span>


                            </label>
                        </div>

                    </div>


                    <!-- =========================================
                     AVAILABILITY
                ========================================== -->

                    <div class="form-section-title scroll-heading">

                        <i class="fa-regular fa-calendar"></i>

                        Your Availability

                    </div>


                    <div class="radio-group scroll-content scroll-delay-1">


                        <label class="radio-option">


                            <input
                                type="radio"
                                name="availability"
                                value="Weekdays"
                                <?php
                                if (
                                    $availability === 'Weekdays'
                                ) {
                                    echo 'checked';
                                }
                                ?>>


                            <span class="custom-radio"></span>

                            Weekdays


                        </label>


                        <label class="radio-option">


                            <input
                                type="radio"
                                name="availability"
                                value="Weekends"
                                <?php
                                if (
                                    $availability === 'Weekends'
                                ) {
                                    echo 'checked';
                                }
                                ?>>


                            <span class="custom-radio"></span>

                            Weekends


                        </label>


                        <label class="radio-option">


                            <input
                                type="radio"
                                name="availability"
                                value="Both"
                                <?php
                                if (
                                    $availability === 'Both'
                                ) {
                                    echo 'checked';
                                }
                                ?>>


                            <span class="custom-radio"></span>

                            Both


                        </label>


                    </div>


                    <!-- =========================================
                     EXPERIENCE
                ========================================== -->

                    <div class="form-section-title scroll-heading">

                        <i class="fa-solid fa-people-group"></i>

                        Previous Volunteering Experience

                    </div>


                    <div class="radio-group scroll-content scroll-delay-1">


                        <label class="radio-option">


                            <input
                                type="radio"
                                name="previous_experience"
                                value="Yes"
                                <?php
                                if (
                                    $previous_experience === 'Yes'
                                ) {
                                    echo 'checked';
                                }
                                ?>>


                            <span class="custom-radio"></span>

                            Yes


                        </label>


                        <label class="radio-option">


                            <input
                                type="radio"
                                name="previous_experience"
                                value="No"
                                <?php
                                if (
                                    $previous_experience === 'No'
                                ) {
                                    echo 'checked';
                                }
                                ?>>


                            <span class="custom-radio"></span>

                            No


                        </label>


                    </div>


                    <!-- =========================================
                     MESSAGE
                ========================================== -->

                    <div class="form-section-title scroll-heading">

                        <i class="fa-regular fa-message"></i>

                        Tell Us About Yourself

                    </div>


                    <div class="form-group scroll-content scroll-delay-1">


                        <label for="message">

                            Message

                        </label>


                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            maxlength="5000"
                            placeholder="Tell us a little about yourself, your skills, or why you would like to volunteer with us..."><?php echo htmlspecialchars($message); ?></textarea>


                    </div>


                    <!-- =========================================
                     SUBMIT
                ========================================== -->

                    <div class="form-submit scroll-content scroll-delay-2">


                        <button
                            type="submit"
                            class="volunteer-submit">

                            SUBMIT APPLICATION

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>


                        <p>

                            <i class="fa-solid fa-lock"></i>

                            Your information will be kept
                            confidential.

                        </p>


                    </div>


                </form>


            </div>


        </div>


    </section>


    <?php

    /* =========================================================
   FOOTER
========================================================= */

    include '../includes/footer.php';

    ?>


    <!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- =========================================================
     SCROLL CONTENT ANIMATION JS
========================================================= -->

    <script src="../js/scroll-content.js"></script>


</body>

</html>