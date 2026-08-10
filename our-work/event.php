<?php

include "../config/db.php";


/* =========================================================
   CHECK EVENT ID
========================================================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {

    die("Event not found.");

}

$id = (int) $_GET['id'];


/* =========================================================
   GET EVENT DETAILS
========================================================= */

$sql = "
    SELECT *
    FROM events
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) == 0) {

    die("Event not found.");

}


$event = mysqli_fetch_assoc($result);


/* =========================================================
   GET GALLERY IMAGES
========================================================= */

$sql2 = "
    SELECT *
    FROM event_images
    WHERE event_id = ?
    ORDER BY id ASC
";

$stmt2 = mysqli_prepare($conn, $sql2);

mysqli_stmt_bind_param(
    $stmt2,
    "i",
    $id
);

mysqli_stmt_execute($stmt2);

$gallery = mysqli_stmt_get_result($stmt2);

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
        <?= htmlspecialchars($event['title']); ?>
        | Sevartha Foundation
    </title>


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
         GLOBAL CSS
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
         DETAILED EVENT CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/our-work/detailed_event.css"
    >


    <!-- =====================================================
         FOOTER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/footer.css"
    >

</head>


<body class="event-page">


<!-- =========================================================
     NAVBAR
========================================================== -->

<?php include "../includes/navbar.php"; ?>


<!-- =========================================================
     EVENT HERO / COVER IMAGE
========================================================== -->

<section class="hero">

    <?php if (!empty($event['cover_image'])) { ?>

        <img
            src="../uploads/events/<?= htmlspecialchars(
                $event['cover_image']
            ); ?>"
            alt="<?= htmlspecialchars(
                $event['title']
            ); ?>"
        >

    <?php } else { ?>

        <div class="event-cover-placeholder">

            <i class="fa-regular fa-image"></i>

        </div>

    <?php } ?>

</section>


<!-- =========================================================
     EVENT CONTENT
========================================================== -->

<main class="container py-5">


    <!-- =====================================================
         EVENT TITLE
    ====================================================== -->

    <h1>

        <?= htmlspecialchars(
            $event['title']
        ); ?>

    </h1>


    <!-- =====================================================
         EVENT DETAILS
    ====================================================== -->

    <div class="row mt-4">


        <!-- =================================================
             DESCRIPTION
        ================================================== -->

        <div class="col-lg-8">

            <h3>
                About This Event
            </h3>


            <?php if (!empty($event['description'])) { ?>

                <div class="event-description">

                    <?= nl2br(
                        htmlspecialchars(
                            $event['description']
                        )
                    ); ?>

                </div>

            <?php } else { ?>

                <p class="event-description">

                    No description has been provided for this event.

                </p>

            <?php } ?>

        </div>


        <!-- =================================================
             INFORMATION BOX
        ================================================== -->

        <div class="col-lg-4">

            <div class="info-box">


                <!-- LOCATION
                     Core field
                -->

                <?php if (!empty($event['location'])) { ?>

                    <p>

                        <span>📍</span>

                        <strong>
                            Location:
                        </strong>

                        <span>
                            <?= htmlspecialchars(
                                $event['location']
                            ); ?>
                        </span>

                    </p>

                <?php } ?>


                <!-- DATE
                     Optional field
                -->

                <?php if (!empty($event['event_date'])) { ?>

                    <p>

                        <span>📅</span>

                        <strong>
                            Date:
                        </strong>

                        <span>

                            <?= date(
                                'd M Y',
                                strtotime(
                                    $event['event_date']
                                )
                            ); ?>

                        </span>

                    </p>

                <?php } ?>


                <!-- SUPPORTED BY
                     Optional field
                -->

                <?php if (
                    !empty($event['supported_by'])
                ) { ?>

                    <p>

                        <span>🤝</span>

                        <strong>
                            Supported By:
                        </strong>

                        <span>

                            <?= htmlspecialchars(
                                $event['supported_by']
                            ); ?>

                        </span>

                    </p>

                <?php } ?>


                <!-- BENEFICIARIES
                     Optional field
                -->

                <?php if (
                    !empty($event['beneficiaries'])
                ) { ?>

                    <p>

                        <span>👥</span>

                        <strong>
                            Beneficiaries:
                        </strong>

                        <span>

                            <?= htmlspecialchars(
                                $event['beneficiaries']
                            ); ?>

                        </span>

                    </p>

                <?php } ?>


                <!--
                    If no optional information exists,
                    the box still contains the core location.
                -->

            </div>

        </div>

    </div>


    <!-- =====================================================
         GALLERY
    ====================================================== -->

    <?php if (mysqli_num_rows($gallery) > 0) { ?>

        <hr class="my-5">


        <h2 class="mb-4">

            Gallery

        </h2>


        <div class="row gallery">


            <?php while (
                $image = mysqli_fetch_assoc($gallery)
            ) { ?>


                <div class="col-lg-4 col-md-6 mb-4">


                    <?php if (
                        !empty($image['image_path'])
                    ) { ?>

                        <img
                            src="../uploads/events/<?= htmlspecialchars(
                                $image['image_path']
                            ); ?>"
                            alt="<?= htmlspecialchars(
                                $event['title']
                            ); ?>"
                            class="img-fluid"
                        >

                    <?php } ?>


                </div>


            <?php } ?>


        </div>

    <?php } ?>


    <!-- =====================================================
         BACK BUTTON
    ====================================================== -->

    <a
        href="javascript:history.back()"
        class="btn btn-primary mt-3"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back

    </a>


</main>


<!-- =========================================================
     FOOTER
========================================================== -->

<?php include "../includes/footer.php"; ?>


<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>