<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Contact Us | Sevartha Foundation</title>


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Main Website CSS -->
    <link
        rel="stylesheet"
        href="../css/style.css">


    <!-- Navbar CSS -->
    <link
        rel="stylesheet"
        href="../css/navbar.css">


    <!-- Footer CSS -->
    <link
        rel="stylesheet"
        href="../css/footer.css">


    <!-- Contact Page CSS -->
    <link
        rel="stylesheet"
        href="css/contact.css">


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>


<body>


    <?php include '../includes/navbar.php'; ?>


    <!-- =========================================================
     CONTACT HERO
========================================================= -->

    <section class="contact-hero">

        <div class="contact-container">

            <div class="contact-eyebrow">

                <span></span>

                SEVARTHA FOUNDATION

            </div>


            <h1 class="contact-title">

                Get in Touch
                <span>With Us</span>

            </h1>


            <p class="contact-intro">

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

                <div class="contact-label">

                    <span></span>

                    CONTACT US

                </div>


                <h2>

                    Let's Stay
                    <span>Connected</span>

                </h2>

            </div>



            <!-- CONTACT CARDS -->

            <div class="contact-info-grid">


                <!-- EMAIL -->

                <a
                    href="mailto:sevarthafoundation7@gmail.com"
                    class="contact-info-card">

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
                    href="tel:+919221008669"
                    class="contact-info-card">

                    <div class="contact-info-icon">

                        <i class="fa-solid fa-phone"></i>

                    </div>


                    <div>

                        <span class="contact-info-label">
                            PHONE
                        </span>

                        <h3>
                            +91 922-100-8669
                        </h3>

                    </div>

                </a>



                <!-- =================================================
             SOCIAL MEDIA
        ================================================== -->

                <div class="social-section">

                    <h3>
                        Follow & Connect With Us
                    </h3>


                    <div class="social-links">


                        <!-- INSTAGRAM -->

                        <a
                            href="https://www.instagram.com/sevarthafoundation/reels"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="social-link instagram"
                            aria-label="Instagram">

                            <i class="fa-brands fa-instagram"></i>

                            <span>
                                Instagram
                            </span>

                        </a>



                        <!-- FACEBOOK -->

                        <a
                            href="https://www.facebook.com/sevarthafoundation"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="social-link facebook"
                            aria-label="Facebook">

                            <i class="fa-brands fa-facebook-f"></i>

                            <span>
                                Facebook
                            </span>

                        </a>



                        <!-- WHATSAPP -->

                        <div
                            class="social-link whatsapp social-disabled"
                            aria-label="WhatsApp">

                            <i class="fa-brands fa-whatsapp"></i>

                            <span>
                                WhatsApp
                            </span>

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

                    <div class="contact-label">

                        <span></span>

                        HAVE A QUESTION?

                    </div>


                    <h2>

                        We'd Love to
                        <span>Hear From You</span>

                    </h2>


                    <p>

                        Have a question, suggestion, or want to know
                        more about our work? Send us a message and
                        we'll get back to you.

                    </p>

                </div>



                <!-- RIGHT SIDE -->

                <form
                    class="contact-form"
                    id="contactForm"
                    action=""
                    method="POST">


                    <!-- NAME -->

                    <div class="form-group">

                        <label for="name">
                            Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
                            required>

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required>

                    </div>



                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="phone">
                            Phone
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number">

                    </div>



                    <!-- MESSAGE -->

                    <div class="form-group">

                        <label for="message">
                            Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            placeholder="Write your message here..."
                            required></textarea>

                    </div>

                    <div
                        id="contactFormMessage"
                        class="contact-form-message"
                        aria-live="polite"></div>



                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="contact-submit">

                        SEND MESSAGE

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>

            </div>

        </div>

    </section>



    <?php include '../includes/footer.php'; ?>



    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <script src="js/contact.js"></script>


</body>

</html>