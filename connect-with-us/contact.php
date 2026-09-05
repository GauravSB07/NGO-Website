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

$name = '';
$email = '';
$phone = '';
$message = '';


/* =========================================================
   CHECK FOR ONE-TIME SUCCESS MESSAGE
========================================================= */

if (isset($_SESSION['contact_success'])) {

    $successMessage = $_SESSION['contact_success'];

    /*
     * Remove the message immediately after reading it.
     * Therefore it will NOT appear again after refresh.
     */

    unset($_SESSION['contact_success']);
}


/* =========================================================
   HANDLE CONTACT FORM SUBMISSION
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($name === '') {

        $errorMessage = "Please enter your name.";
    } elseif ($email === '') {

        $errorMessage = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = "Please enter a valid email address.";
    } elseif ($message === '') {

        $errorMessage = "Please enter your message.";
    } elseif (strlen($message) < 10) {

        $errorMessage =
            "Please enter a message of at least 10 characters.";
    } else {


        /* =================================================
           INSERT INTO DATABASE
        ================================================= */

        $sql = "
            INSERT INTO contact_messages
            (
                name,
                email,
                phone,
                message
            )
            VALUES
            (?, ?, ?, ?)
        ";


        $stmt = mysqli_prepare($conn, $sql);


        if (!$stmt) {

            $errorMessage =
                "Database prepare error: " .
                mysqli_error($conn);
        } else {


            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $phone,
                $message
            );


            /* =============================================
               EXECUTE INSERT
            ============================================= */

            if (mysqli_stmt_execute($stmt)) {


                /* =========================================
                   SAVE ONE-TIME SUCCESS MESSAGE
                ========================================= */

                $_SESSION['contact_success'] =
                    "Thank you for contacting us. We will get back to you soon.";


                /* =========================================
                   CLOSE STATEMENT
                ========================================= */

                mysqli_stmt_close($stmt);


                /* =========================================
                   REDIRECT TO CONTACT PAGE
                ========================================= */

                header("Location: contact.php");

                exit;
            } else {

                $errorMessage =
                    "Database insert error: " .
                    mysqli_stmt_error($stmt);


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
        Contact Us | Sevartha Foundation
    </title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- MAIN WEBSITE CSS -->

    <link
        rel="stylesheet"
        href="../css/style.css">


    <!-- NAVBAR CSS -->

    <link
        rel="stylesheet"
        href="../css/navbar.css">


    <!-- FOOTER CSS -->

    <link
        rel="stylesheet"
        href="../css/footer.css">


    <!-- CONTACT CSS -->

    <link
        rel="stylesheet"
        href="css/contact.css">


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../css/scroll-content.css">
    
</head>


<body>


    <?php

    /* =========================================================
   NAVBAR
========================================================= */

    include '../includes/navbar.php';

    ?>


    <!-- =========================================================
     CONTACT HERO
========================================================= -->

    <section class="contact-hero">

        <div class="contact-container">

            <div class="contact-eyebrow scroll-eyebrow">

                <span></span>

                SEVARTHA FOUNDATION

            </div>


            <h1 class="contact-title scroll-heading scroll-delay-1">

                Get in Touch

                <span>
                    With Us
                </span>

            </h1>


            <p class="contact-intro scroll-paragraph scroll-delay-2">

                We'd love to hear from you.

            </p>

        </div>

    </section>


    <!-- =========================================================
     CONTACT INFORMATION
========================================================= -->

    <section class="contact-section">

        <div class="contact-container">

            <div class="contact-heading">

                <div class="contact-label scroll-heading">

                    <span></span>

                    CONTACT US

                </div>


                <h2 class="scroll-heading scroll-delay-3">

                    Let's Stay

                    <span>
                        Connected
                    </span>

                </h2>

            </div>


            <div class="contact-info-grid">


                <!-- EMAIL -->

                <a
                    href="https://mail.google.com/mail/?view=cm&fs=1&to=sevarthafoundation7@gmail.com&su=Contact%20Sevartha%20Foundation&body=Hello%20Sevartha%20Foundation%2C%0A%0AI%20would%20like%20to%20get%20in%20touch%20with%20you%20regarding%3A%0A%0A"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="contact-info-card contact-card-left">
                    <div class="contact-info-icon">

                        <i class="fa-solid fa-envelope"></i>

                    </div>


                    <div>

                        <span class="contact-info-label">

                            EMAIL

                        </span>


                        <h3>

                            sevarthafoundation7@gmail.com

                        </h3>

                    </div>

                </a>


                <!-- PHONE -->

                <a
                    href="tel:+919867983524"
                    class="contact-info-card contact-card-right">

                    <div class="contact-info-icon">

                        <i class="fa-solid fa-phone"></i>

                    </div>


                    <div>

                        <span class="contact-info-label">

                            PHONE

                        </span>


                        <h3>

                            +91 98679 83524

                        </h3>

                    </div>

                </a>


                <!-- SOCIAL MEDIA -->

                <div class="social-section">

                    <h2 class="scroll-heading">
                        Follow & Connect 
                        <span>
                        With Us
                        </span>
                    </h2>

                    <div class="social-links scroll-content scroll-delay-4">

                        <!-- INSTAGRAM -->

                        <a
                            href="https://www.instagram.com/sevarthafoundation/reels"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="social-link instagram scroll-content scroll-delay-5">

                            <span class="social-icon ">
                                <i class="fa-brands fa-instagram"></i>
                            </span>

                        </a>


                        <!-- FACEBOOK -->

                        <a
                            href="https://www.facebook.com/sevarthafoundation"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="social-link facebook scroll-content scroll-delay-6">

                            <span class="social-icon">
                                <i class="fa-brands fa-facebook-f"></i>
                            </span>

                        </a>


                        <!-- WHATSAPP -->

                        <a
                            href="https://wa.me/919867983524"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="social-link whatsapp scroll-content scroll-delay-7">

                            <span class="social-icon">
                                <i class="fa-brands fa-whatsapp"></i>
                            </span>


                        </a>

                    </div>

                </div>
            </div>

        </div>

    </section>


    <!-- =========================================================
     CONTACT FORM
========================================================= -->

    <section class="contact-form-section">

        <div class="contact-container">

            <div class="contact-form-wrapper">


                <!-- LEFT SIDE -->

                <div class="contact-form-content">

                    <div class="contact-label scroll-heading scroll-delay-1">

                        <span></span>

                        HAVE A QUESTION?

                    </div>


                    <h2 class="scroll-heading scroll-delay-2">

                        We'd Love to

                        <span>
                            Hear From You
                        </span>

                    </h2>


                    <p class="scroll-paragraph scroll-delay-3">

                        Have a question, suggestion, or want to know
                        more about our work? Send us a message and
                        we'll get back to you.

                    </p>

                </div>


                <!-- RIGHT SIDE -->

                <form
                    class="contact-form"
                    id="contactForm"
                    action="contact.php"
                    method="POST">


                    <!-- SUCCESS MESSAGE -->

                    <?php if ($successMessage !== ''): ?>

                        <div
                            class="contact-form-message success"
                            role="alert">

                            <i class="fa-solid fa-circle-check"></i>

                            <?php
                            echo htmlspecialchars($successMessage);
                            ?>

                        </div>

                    <?php endif; ?>


                    <!-- ERROR MESSAGE -->

                    <?php if ($errorMessage !== ''): ?>

                        <div
                            class="contact-form-message error"
                            role="alert">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            <?php
                            echo htmlspecialchars($errorMessage);
                            ?>

                        </div>

                    <?php endif; ?>


                    <!-- NAME -->

                    <div class="form-group scroll-content scroll-delay-2">

                        <label for="name">
                            Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            maxlength="100"
                            required>

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group scroll-content scroll-delay-2">

                        <label for="email">
                            Email
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

                    <div class="form-group scroll-content scroll-delay-3">

                        <label for="phone">
                            Phone
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            value="<?php echo htmlspecialchars($phone); ?>"
                            maxlength="30">

                    </div>


                    <!-- MESSAGE -->

                    <div class="form-group scroll-content scroll-delay-4">

                        <label for="message">
                            Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            placeholder="Write your message here..."
                            maxlength="5000"
                            required><?php echo htmlspecialchars($message); ?></textarea>

                    </div>


                    <!-- SUBMIT BUTTON -->

                    <button
                        type="submit"
                        class="contact-submit scroll-content scroll-delay-5">

                        SEND MESSAGE

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


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


    <!-- BOOTSTRAP JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../js/scroll-content.js"></script>
</body>

</html>