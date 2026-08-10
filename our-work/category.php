<?php

include '../config/db.php';


/* =========================================================
   CHECK CATEGORY SLUG
========================================================= */

if (!isset($_GET['slug']) || empty($_GET['slug'])) {

    die("Category not found.");

}

$slug = $_GET['slug'];


/* =========================================================
   GET CATEGORY
========================================================= */

$categoryQuery = "
    SELECT *
    FROM categories
    WHERE slug = ?
";

$stmt = mysqli_prepare($conn, $categoryQuery);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $slug
);

mysqli_stmt_execute($stmt);

$categoryResult =
    mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($categoryResult) == 0) {

    die("Category not found.");

}


$category =
    mysqli_fetch_assoc($categoryResult);


/* =========================================================
   GET EVENTS
========================================================= */

$eventQuery = "
    SELECT *
    FROM events
    WHERE category_id = ?
    AND status = 'Active'
    ORDER BY event_date DESC
";

$stmt = mysqli_prepare($conn, $eventQuery);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $category['id']
);

mysqli_stmt_execute($stmt);

$events =
    mysqli_stmt_get_result($stmt);

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
        <?= htmlspecialchars($category['name']); ?>
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
         NAVBAR CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/navbar.css"
    >


    <!-- =====================================================
         OUR WORK CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/our-work/our_work.css"
    >


</head>


<body class="category-page">


<!-- =========================================================
     NAVBAR
========================================================== -->

<?php include '../includes/navbar.php'; ?>


<!-- =========================================================
     CATEGORY HERO
========================================================== -->

<section class="category-hero">

    <div class="category-hero-content">

        <div class="category-eyebrow">

            <span></span>

            Our Work

        </div>


        <h1 class="category-title">

            <?= htmlspecialchars(
                $category['name']
            ); ?>

        </h1>


        <?php if (
            !empty($category['short_description'])
        ) { ?>

            <p class="category-description">

                <?= htmlspecialchars(
                    $category['short_description']
                ); ?>

            </p>

        <?php } ?>

    </div>

</section>


<!-- =========================================================
     EVENTS
========================================================== -->

<section class="events-section">


    <div class="events-heading">

        <div>

            <h2>
                Our Events
            </h2>

            <p>
                Explore our initiatives and the impact we are creating.
            </p>

        </div>

    </div>


    <?php if (mysqli_num_rows($events) > 0) { ?>


        <div class="row g-4">


            <?php while (
                $event = mysqli_fetch_assoc($events)
            ) { ?>


                <div class="col-lg-4 col-md-6">


                    <article class="event-card">


                        <!-- =================================================
                             IMAGE
                        ================================================== -->

                        <div class="event-image-wrapper">


                            <?php if (
                                !empty($event['cover_image'])
                            ) { ?>


                                <img
                                    src="../uploads/events/<?= htmlspecialchars(
                                        $event['cover_image']
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $event['title']
                                    ); ?>"
                                    class="event-image"
                                >


                            <?php } else { ?>


                                <div
                                    class="event-image-placeholder"
                                >

                                    <i
                                        class="fa-solid fa-image"
                                    ></i>

                                </div>


                            <?php } ?>


                        </div>


                        <!-- =================================================
                             BODY
                        ================================================== -->

                        <div class="event-body">


                            <!-- EVENT TITLE -->

                            <h3 class="event-title">

                                <?= htmlspecialchars(
                                    $event['title']
                                ); ?>

                            </h3>


                            <!-- EVENT DESCRIPTION -->

                            <?php if (
                                !empty($event['description'])
                            ) { ?>

                                <p class="event-description">

                                    <?= htmlspecialchars(
                                        substr(
                                            strip_tags(
                                                $event['description']
                                            ),
                                            0,
                                            150
                                        )
                                    ); ?>


                                    <?php if (
                                        strlen(
                                            strip_tags(
                                                $event['description']
                                            )
                                        ) > 150
                                    ) { ?>

                                        ...

                                    <?php } ?>

                                </p>

                            <?php } ?>


                            <!-- EVENT DATE -->

                            <?php if (
                                !empty($event['event_date'])
                            ) { ?>

                                <div class="event-date">

                                    <i
                                        class="fa-regular fa-calendar"
                                    ></i>

                                    <span>

                                        <?= date(
                                            'd M Y',
                                            strtotime(
                                                $event['event_date']
                                            )
                                        ); ?>

                                    </span>

                                </div>

                            <?php } ?>


                            <!-- EVENT LOCATION -->

                            <?php if (
                                !empty($event['location'])
                            ) { ?>

                                <div class="event-location">

                                    <i
                                        class="fa-solid fa-location-dot"
                                    ></i>

                                    <span>

                                        <?= htmlspecialchars(
                                            $event['location']
                                        ); ?>

                                    </span>

                                </div>

                            <?php } ?>


                            <!-- VIEW EVENT -->

                            <a
                                href="event.php?id=<?= $event['id']; ?>"
                                class="event-button"
                            >

                                View Event

                                <i
                                    class="fa-solid fa-arrow-right"
                                ></i>

                            </a>


                        </div>


                    </article>


                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <!-- =====================================================
             NO EVENTS
        ====================================================== -->

        <div class="no-events">

            <i
                class="fa-regular fa-calendar-xmark"
            ></i>

            <h3>
                No Events Yet
            </h3>

            <p>
                There are currently no events listed under this category.
            </p>

        </div>


    <?php } ?>


</section>


<!-- =========================================================
     FOOTER
========================================================== -->

<?php include '../includes/footer.php'; ?>


<!-- =========================================================
     FOOTER CSS
========================================================== -->

<link
    rel="stylesheet"
    href="../css/footer.css"
>


<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>