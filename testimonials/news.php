<?php
$pageTitle = "In the News | Sevartha Foundation";
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

    <!-- Shared Testimonials CSS -->
    <link
        rel="stylesheet"
        href="css/institutional.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>


<body>


    <?php include "../includes/navbar.php"; ?>


    <!-- =====================================================
     PAGE HERO
===================================================== -->

    <section class="testimonial-page-hero">

        <div class="testimonial-page-container">

            <div class="testimonial-eyebrow">

                <span></span>

                TESTIMONIALS

            </div>


            <h1>
                In the
                <span>News</span>
            </h1>


            <p>

                Explore media coverage highlighting Sevartha
                Foundation's initiatives and efforts to support
                communities in need.

            </p>

        </div>

    </section>



    <!-- =====================================================
     NEWS DOCUMENTS
===================================================== -->

    <section class="testimonial-documents">

        <div class="testimonial-page-container">


            <div class="testimonial-section-heading">

                <span>03</span>

                <div>

                    <h2>
                        In the News
                    </h2>

                    <p>

                        Media coverage showcasing Sevartha
                        Foundation's initiatives and community
                        outreach.

                    </p>

                </div>

            </div>



            <div class="testimonial-document-grid">


                <!-- =================================================
                 NEWS DOCUMENT 1
            ================================================== -->

                <article class="testimonial-document-card">


                    <div class="testimonial-image-wrapper">

                        <img
                            src="../static_image.php?name=testimonial5.png"
                            alt="Sevartha Foundation newspaper coverage">


                        <button
                            type="button"
                            class="document-view-button"
                            data-image="../static_image.php?name=testimonial5.png"
                            data-title="Sevartha Foundation Media Coverage">

                            <i class="fa-solid fa-expand"></i>

                            View Document

                        </button>

                    </div>


                    <div class="testimonial-document-content">

                        <span class="testimonial-category-label">

                            MEDIA COVERAGE

                        </span>


                        <h3>
                            Educational Material Distribution
                        </h3>


                        <p>

                            Newspaper coverage highlighting
                            Sevartha Foundation's initiative to
                            distribute educational materials and
                            support students.

                        </p>


                        <button
                            type="button"
                            class="document-link-button"
                            data-image="../static_image.php?name=testimonial5.png"
                            data-title="Sevartha Foundation Media Coverage">

                            View Full Document

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    </div>

                </article>



                <!-- =================================================
                 NEWS DOCUMENT 2
            ================================================== -->

                <article class="testimonial-document-card">


                    <div class="testimonial-image-wrapper">

                        <img
                            src="../static_image.php?name=testimonial6.png"
                            alt="Punya Nagari newspaper coverage">


                        <button
                            type="button"
                            class="document-view-button"
                            data-image="../static_image.php?name=testimonial6.png"
                            data-title="Punya Nagari Coverage">

                            <i class="fa-solid fa-expand"></i>

                            View Document

                        </button>

                    </div>


                    <div class="testimonial-document-content">

                        <span class="testimonial-category-label">

                            MEDIA COVERAGE

                        </span>


                        <h3>
                            Punya Nagari Coverage
                        </h3>


                        <p>

                            Media coverage of Sevartha Foundation's
                            efforts to provide educational materials
                            and support learning opportunities for
                            school students.

                        </p>


                        <button
                            type="button"
                            class="document-link-button"
                            data-image="../static_image.php?name=testimonial6.png"
                            data-title="Punya Nagari Coverage">

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
                    alt="News Document">

            </div>

        </div>

    </div>



    <?php include "../includes/footer.php"; ?>



    <!-- Existing testimonial JavaScript -->
    <script src="js/institutional.js"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>