<?php

include "config/db.php";


/* =========================================================
   GET ANNUAL EVENT IMAGES
========================================================= */

$sql = "
    SELECT
        id,
        image_name,
        image_type
    FROM static_images
    WHERE image_category = 'annual_event'
    ORDER BY id ASC
";


$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Unable to load annual event gallery.");
}


$images = [];

while ($row = mysqli_fetch_assoc($result)) {

    $images[] = $row;

}


$totalImages = count($images);


/* =========================================================
   FEATURED IMAGE
========================================================= */

$featuredImage = null;

if ($totalImages > 0) {

    $featuredImage = $images[0];

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
        Annual Event | Sevartha Foundation
    </title>


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <!-- =====================================================
         NAVBAR
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/navbar.css"
    >


    <!-- =====================================================
         ANNUAL EVENT CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/annual-event/annual-event.css"
    >

</head>


<body class="annual-event-page">


<?php include "includes/navbar.php"; ?>


<!-- =========================================================
     HERO
========================================================== -->

<section class="annual-hero">


    <div class="annual-hero-decoration decoration-one"></div>

    <div class="annual-hero-decoration decoration-two"></div>


    <div class="container">


        <div class="annual-hero-content">


            <div class="annual-eyebrow">

                <span></span>

                SEVARTHA FOUNDATION

            </div>


            <h1>

                Annual Event

                <em>
                    2026
                </em>

            </h1>


            <p class="annual-intro">

                A celebration of service, compassion,
                community and the meaningful impact
                created together.

            </p>


            <div class="annual-meta">

                <div>

                    <i class="fa-regular fa-calendar"></i>

                    <span>
                        2026
                    </span>

                </div>


                <div>

                    <i class="fa-solid fa-camera"></i>

                    <span>

                        <?= $totalImages; ?>

                        Photos

                    </span>

                </div>


                <div>

                    <i class="fa-solid fa-heart"></i>

                    <span>
                        Moments That Matter
                    </span>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     FEATURED IMAGE
========================================================== -->

<?php if ($featuredImage) { ?>


<section class="featured-section">


    <div class="container">


        <div class="featured-card">


            <button
                type="button"
                class="featured-image-button"
                data-lightbox-index="0"
                aria-label="Open featured photograph"
            >


                <img
                    src="static_image.php?id=<?= (int) $featuredImage['id']; ?>"
                    alt="<?= htmlspecialchars(
                        $featuredImage['image_name']
                    ); ?>"
                    loading="eager"
                >


                <span class="featured-overlay">

                    <span>

                        <i class="fa-solid fa-expand"></i>

                        View Gallery

                    </span>

                </span>


            </button>


            <div class="featured-caption">


                <div>

                    <span class="caption-label">
                        FEATURED MOMENT
                    </span>

                    <h2>
                        Celebrating Together
                    </h2>

                </div>


                <p>

                    Every photograph tells a story
                    of people coming together to create
                    positive change.

                </p>


            </div>


        </div>


    </div>

</section>


<?php } ?>


<!-- =========================================================
     GALLERY
========================================================== -->

<section class="annual-gallery-section">


    <div class="container">


        <div class="gallery-heading">


            <div>

                <span class="section-label">
                    THE COLLECTION
                </span>

                <h2>
                    Moments From Our Annual Event
                </h2>

            </div>


            <p>

                Browse through memories,
                celebrations and moments
                shared by our community.

            </p>


        </div>


        <?php if ($totalImages > 0) { ?>


        <div
            class="masonry-gallery"
            id="annualGallery"
        >


            <?php foreach (
                $images as $index => $image
            ) { ?>


                <button
                    type="button"
                    class="gallery-item
                    <?= $index >= 16
                        ? 'gallery-item-hidden'
                        : ''; ?>"
                    data-index="<?= $index; ?>"
                    aria-label="View photograph <?= $index + 1; ?>"
                >


                    <img
                        src="static_image.php?id=<?= (int) $image['id']; ?>"
                        alt="<?= htmlspecialchars(
                            $image['image_name']
                        ); ?>"
                        loading="lazy"
                    >


                    <span class="gallery-hover">

                        <i class="fa-solid fa-expand"></i>

                    </span>


                    <span class="gallery-number">

                        <?= str_pad(
                            $index + 1,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ); ?>

                    </span>


                </button>


            <?php } ?>


        </div>


        <?php if ($totalImages > 16) { ?>


            <div class="load-more-wrapper">


                <button
                    type="button"
                    id="loadMoreBtn"
                    class="load-more-btn"
                >

                    <span>
                        Load More Photos
                    </span>

                    <i class="fa-solid fa-arrow-down"></i>

                </button>


                <p id="galleryCount">

                    Showing
                    <strong>16</strong>
                    of
                    <strong><?= $totalImages; ?></strong>
                    photos

                </p>


            </div>


        <?php } ?>


        <?php } else { ?>


            <div class="empty-gallery">


                <div class="empty-gallery-icon">

                    <i class="fa-regular fa-images"></i>

                </div>


                <h3>
                    Photos Coming Soon
                </h3>


                <p>

                    Photographs from our annual event
                    will appear here.

                </p>


            </div>


        <?php } ?>


    </div>

</section>


<!-- =========================================================
     CLOSING MESSAGE
========================================================== -->

<section class="annual-closing">


    <div class="container">


        <div class="closing-inner">


            <span class="closing-line"></span>


            <p>

                Together, every moment becomes
                part of something meaningful.

            </p>


            <span class="closing-line"></span>


        </div>

    </div>

</section>


<!-- =========================================================
     LIGHTBOX
========================================================== -->

<div
    class="gallery-lightbox"
    id="galleryLightbox"
    aria-hidden="true"
>


    <div class="lightbox-backdrop"></div>


    <div class="lightbox-container">


        <button
            type="button"
            class="lightbox-close"
            id="lightboxClose"
            aria-label="Close gallery"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <button
            type="button"
            class="lightbox-prev"
            id="lightboxPrev"
            aria-label="Previous image"
        >

            <i class="fa-solid fa-chevron-left"></i>

        </button>


        <div class="lightbox-image-wrapper">


            <img
                id="lightboxImage"
                src=""
                alt=""
            >


            <div class="lightbox-loading">

                <i class="fa-solid fa-circle-notch fa-spin"></i>

            </div>


        </div>


        <button
            type="button"
            class="lightbox-next"
            id="lightboxNext"
            aria-label="Next image"
        >

            <i class="fa-solid fa-chevron-right"></i>

        </button>


        <div class="lightbox-footer">


            <div>

                <span id="lightboxCounter">
                    1 / <?= $totalImages; ?>
                </span>

            </div>


            <span class="lightbox-name" id="lightboxName"></span>


        </div>


    </div>

</div>


<!-- =========================================================
     FOOTER
========================================================== -->

<?php include "includes/footer.php"; ?>


<!-- =========================================================
     FOOTER CSS
========================================================== -->

<link
    rel="stylesheet"
    href="css/footer.css"
>


<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =========================================================
     PASS IMAGE DATA TO JAVASCRIPT
========================================================== -->

<script>

const annualEventImages = <?= json_encode(
    array_map(
        function ($image) {

            return [
                'id' => (int) $image['id'],
                'name' => $image['image_name']
            ];

        },
        $images
    ),
    JSON_UNESCAPED_SLASHES
); ?>;

</script>


<!-- =========================================================
     ANNUAL EVENT JS
========================================================== -->

<script
    src="js/annual-event.js"
></script>


</body>

</html>