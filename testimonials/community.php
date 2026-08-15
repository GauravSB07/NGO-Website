<?php
$pageTitle = "Community & Partner Voices | Sevartha Foundation";
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
        <?php echo $pageTitle; ?>
    </title>


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

    <!-- Footer CSS -->
    <link
        rel="stylesheet"
        href="../css/footer.css"
    >

    <!-- Institutional/Shared Testimonial CSS -->
    <link
        rel="stylesheet"
        href="css/institutional.css"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

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
            Community &
            <span>Partner Voices</span>
        </h1>


        <p>

            Appreciation and communications from
            organisations connected with Sevartha
            Foundation's community work.

        </p>

    </div>

</section>



<!-- =====================================================
     DOCUMENTS
===================================================== -->

<section class="testimonial-documents">

    <div class="testimonial-page-container">


        <div class="testimonial-section-heading">

            <span>02</span>

            <div>

                <h2>
                    Community & Partner Voices
                </h2>

                <p>
                    Voices and communications from
                    organisations connected with Sevartha
                    Foundation's community initiatives.
                </p>

            </div>

        </div>



        <div class="testimonial-document-grid">


            <!-- =================================================
                 TESTIMONIAL 3
            ================================================== -->

            <article class="testimonial-document-card">


                <div class="testimonial-image-wrapper">

                    <img
                        src="../static_image.php?name=testimonial3.png"
                        alt="Humayaa Foundation Old Age Home appreciation letter"
                    >


                    <button
                        type="button"
                        class="document-view-button"
                        data-image="../static_image.php?name=testimonial3.png"
                        data-title="Humayaa Foundation Old Age Home"
                    >

                        <i class="fa-solid fa-expand"></i>

                        View Document

                    </button>

                </div>


                <div class="testimonial-document-content">

                    <span class="testimonial-category-label">

                        COMMUNITY PARTNER

                    </span>


                    <h3>
                        Humayaa Foundation Old Age Home
                    </h3>


                    <p>

                        Humayaa Foundation expresses heartfelt
                        gratitude to Sevartha Foundation for its
                        grocery donation and support for elderly
                        residents.

                    </p>


                    <button
                        type="button"
                        class="document-link-button"
                        data-image="../static_image.php?name=testimonial3.png"
                        data-title="Humayaa Foundation Old Age Home"
                    >

                        View Full Document

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </div>

            </article>



            <!-- =================================================
                 TESTIMONIAL 4
            ================================================== -->

            <article class="testimonial-document-card">


                <div class="testimonial-image-wrapper">

                    <img
                        src="../static_image.php?name=testimonial4.png"
                        alt="Vikalharti Apang Seva Sangh communication"
                    >


                    <button
                        type="button"
                        class="document-view-button"
                        data-image="../static_image.php?name=testimonial4.png"
                        data-title="Vikalharti Apang Seva Sangh"
                    >

                        <i class="fa-solid fa-expand"></i>

                        View Document

                    </button>

                </div>


                <div class="testimonial-document-content">

                    <span class="testimonial-category-label">

                        COMMUNITY PARTNER

                    </span>


                    <h3>
                        Vikalharti Apang Seva Sangh
                    </h3>


                    <p>

                        A communication highlighting the
                        importance of support and collaboration
                        for persons with disabilities and
                        community welfare.

                    </p>


                    <button
                        type="button"
                        class="document-link-button"
                        data-image="../static_image.php?name=testimonial4.png"
                        data-title="Vikalharti Apang Seva Sangh"
                    >

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
    id="testimonialModal"
>

    <div class="testimonial-modal-content">


        <button
            type="button"
            class="testimonial-modal-close"
            id="testimonialModalClose"
            aria-label="Close"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <h3 id="testimonialModalTitle">
            Document
        </h3>


        <div class="testimonial-modal-image">

            <img
                src=""
                id="testimonialModalImage"
                alt="Testimonial Document"
            >

        </div>

    </div>

</div>



<?php include "../includes/footer.php"; ?>



<!-- Use the same JS that is already working -->
<script src="js/institutional.js"></script>


</body>

</html>