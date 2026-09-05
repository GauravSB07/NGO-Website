<?php
$pageTitle = "Institutional Appreciation | Sevartha Foundation";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $pageTitle; ?>
    </title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Main CSS -->
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

    <!-- Institutional Testimonials CSS -->
    <link
        rel="stylesheet"
        href="css/institutional.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../css/scroll-content.css">
</head>


<body>


    <?php include "../includes/navbar.php"; ?>


    <!-- =====================================================
     PAGE HERO
===================================================== -->

    <section class="testimonial-page-hero">

        <div class="testimonial-page-container">

            <div class="testimonial-eyebrow scroll-content scroll-delay-1">

                <span></span>

                TESTIMONIALS

            </div>


            <h1>
                Institutional
                <span>Appreciation</span>
            </h1>


            <p class="scroll-paragraph scroll-delay-2">

                Official acknowledgements received by Sevartha
                Foundation for its initiatives and contributions
                towards the community.

            </p>

        </div>

    </section>



    <!-- =====================================================
     DOCUMENTS
===================================================== -->

    <section class="testimonial-documents">

        <div class="testimonial-page-container">


            <div class="testimonial-section-heading">

                <span>01</span>

                <div>

                    <h2 class="scroll-heading scroll-delay-1">
                        Institutional <span>Appreciation</span>
                    </h2>

                    <p class="scroll-paragraph scroll-delay-2">
                        Recognitions and acknowledgements from
                        institutions associated with our work.
                    </p>

                </div>

            </div>



            <div class="testimonial-document-grid">


                <!-- =================================================
                 TESTIMONIAL 1
            ================================================== -->

                <article class="testimonial-document-card">


                    <div class="testimonial-image-wrapper">

                        <img
                            src="../static_image.php?name=testimonial1.png"
                            alt="Institutional Appreciation Document">


                        <button
                            type="button"
                            class="document-view-button"
                            data-image="../static_image.php?name=testimonial1.png"
                            data-title="Institutional Appreciation">

                            <i class="fa-solid fa-expand"></i>

                            View Document

                        </button>

                    </div>


                    <div class="testimonial-document-content">

                        <span class="testimonial-category-label">

                            INSTITUTIONAL APPRECIATION

                        </span>


                        <h3>
                            Educational Initiative
                        </h3>


                        <p>

                            An official acknowledgement recognising
                            Sevartha Foundation's efforts in supporting
                            students through educational initiatives
                            and learning materials.

                        </p>


                        <button
                            type="button"
                            class="document-link-button"
                            data-image="../static_image.php?name=testimonial1.png"
                            data-title="Institutional Appreciation">

                            View Full Document

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    </div>

                </article>



                <!-- =================================================
                 TESTIMONIAL 2
            ================================================== -->

                <article class="testimonial-document-card">


                    <div class="testimonial-image-wrapper">

                        <img
                            src="../static_image.php?name=testimonial2.png"
                            alt="Educational Support Appreciation">


                        <button
                            type="button"
                            class="document-view-button"
                            data-image="../static_image.php?name=testimonial2.png"
                            data-title="Educational Support Appreciation">

                            <i class="fa-solid fa-expand"></i>

                            View Document

                        </button>

                    </div>


                    <div class="testimonial-document-content">

                        <span class="testimonial-category-label">

                            INSTITUTIONAL APPRECIATION

                        </span>


                        <h3>
                            Educational Support
                        </h3>


                        <p>

                            An acknowledgement expressing appreciation
                            for the educational materials and support
                            provided to children.

                        </p>


                        <button
                            type="button"
                            class="document-link-button"
                            data-image="../static_image.php?name=testimonial2.png"
                            data-title="Educational Support Appreciation">

                            View Full Document

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    </div>

                </article>


            </div>

        </div>

    </section>



    <!-- =====================================================
     DOCUMENT MODAL
===================================================== -->

    <div
        class="testimonial-modal"
        id="testimonialModal">

        <div class="testimonial-modal-content">


            <button
                type="button"
                class="testimonial-modal-close"
                id="testimonialModalClose"
                aria-label="Close">

                <i class="fa-solid fa-xmark"></i>

            </button>


            <h3 id="testimonialModalTitle">
                Document
            </h3>


            <div class="testimonial-modal-image">

                <img
                    src=""
                    id="testimonialModalImage"
                    alt="Testimonial Document">

            </div>

        </div>

    </div>



    <?php include "../includes/footer.php"; ?>



    <script src="js/institutional.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>
    <script src="../js/scroll-content.js"></script>

</body>

</html>