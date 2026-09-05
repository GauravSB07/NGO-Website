<?php

session_start();


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: login.php");

    exit();

}


include "../config/db.php";


/* =========================================================
   TOTAL EVENTS
========================================================= */

$eventQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM events"
);

$eventData = mysqli_fetch_assoc($eventQuery);

$totalEvents = $eventData['total'];


/* =========================================================
   TOTAL CATEGORIES
========================================================= */

$categoryQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$categoryData = mysqli_fetch_assoc($categoryQuery);

$totalCategories = $categoryData['total'];


/* =========================================================
   TOTAL CONTACT MESSAGES
========================================================= */

$contactQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM contact_messages"
);

$totalContacts = 0;

if ($contactQuery) {

    $contactData =
        mysqli_fetch_assoc($contactQuery);

    $totalContacts =
        $contactData['total'];

}


/* =========================================================
   TOTAL VOLUNTEER APPLICATIONS
========================================================= */

$volunteerQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM volunteer_applications"
);

$totalVolunteers = 0;

if ($volunteerQuery) {

    $volunteerData =
        mysqli_fetch_assoc($volunteerQuery);

    $totalVolunteers =
        $volunteerData['total'];

}

/* =========================================================
   PENDING DONATIONS
========================================================= */

$donationQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM donations
    WHERE payment_status = 'pending'
    "
);

$totalDonations = 0;

if ($donationQuery) {

    $donationData =
        mysqli_fetch_assoc($donationQuery);

    $totalDonations =
        $donationData['total'] ?? 0;
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
        Admin Dashboard | Sevartha Foundation
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
         ADMIN CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/admin/admin.css"
    >

</head>


<body class="admin-dashboard">


<!-- =========================================================
     ADMIN NAVBAR
========================================================== -->

<nav class="admin-navbar">

    <div class="container-fluid px-4">


        <a
            class="admin-brand"
            href="dashboard.php"
        >

            Sevartha Foundation

            <span class="text-muted">

                | Admin

            </span>

        </a>


        <div class="admin-user">


            <span>

                <i
                    class="fa-solid fa-user me-1"
                ></i>

                <?= htmlspecialchars(
                    $_SESSION['admin_name']
                ); ?>

            </span>


            <a
                href="logout.php"
                class="admin-logout"
            >

                <i
                    class="fa-solid fa-right-from-bracket"
                ></i>

                Logout

            </a>


        </div>


    </div>

</nav>


<!-- =========================================================
     DASHBOARD
========================================================== -->

<main class="admin-container">


    <!-- =====================================================
         HEADING
    ====================================================== -->

    <div class="admin-header">

        <div>

            <h1>

                Welcome,

                <?= htmlspecialchars(
                    $_SESSION['admin_name']
                ); ?>

            </h1>


            <p>

                Manage your Sevartha Foundation website
                from here.

            </p>

        </div>

    </div>


    <!-- =====================================================
         DASHBOARD CARDS
    ====================================================== -->

    <div class="row g-4">


        <!-- =================================================
             EVENTS
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">


                <div class="admin-stat">


                    <div>

                        <h3>
                            Events
                        </h3>


                        <p class="admin-stat-label">

                            Total Events

                        </p>


                        <div class="admin-stat-number">

                            <?= $totalEvents; ?>

                        </div>

                    </div>


                    <div class="admin-card-icon">

                        <i
                            class="fa-solid fa-calendar-days"
                        ></i>

                    </div>


                </div>


                <div class="d-flex gap-2 mt-4">


                    <a
                        href="events/add.php"
                        class="admin-btn-primary"
                    >

                        <i
                            class="fa-solid fa-plus"
                        ></i>

                        Add Event

                    </a>


                    <a
                        href="events/index.php"
                        class="admin-btn-secondary"
                    >

                        Manage

                    </a>


                </div>


            </div>

        </div>



        <!-- =================================================
             CATEGORIES
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">


                <div class="admin-stat">


                    <div>

                        <h3>
                            Categories
                        </h3>


                        <p>

                            Manage work categories

                        </p>

                    </div>


                    <div class="admin-card-icon">

                        <i
                            class="fa-solid fa-layer-group"
                        ></i>

                    </div>


                </div>


                <div class="mt-4">

                    <a
                        href="categories/index.php"
                        class="admin-btn-secondary"
                    >

                        Manage Categories

                    </a>

                </div>


            </div>

        </div>



        <!-- =================================================
             CONTACT MESSAGES
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">


                <div class="admin-stat">


                    <div>

                        <h3>
                            Contact Messages
                        </h3>


                        <p class="admin-stat-label">

                            Total Messages

                        </p>


                        <div class="admin-stat-number">

                            <?= $totalContacts; ?>

                        </div>

                    </div>


                    <div class="admin-card-icon">

                        <i
                            class="fa-solid fa-envelope"
                        ></i>

                    </div>


                </div>


                <div class="mt-4">

                    <a
                        href="contact/index.php"
                        class="admin-btn-secondary"
                    >

                        <i
                            class="fa-solid fa-eye"
                        ></i>

                        View Messages

                    </a>

                </div>


            </div>

        </div>



        <!-- =================================================
             VOLUNTEER APPLICATIONS
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">


                <div class="admin-stat">


                    <div>

                        <h3>
                            Volunteers
                        </h3>


                        <p class="admin-stat-label">

                            Applications

                        </p>


                        <div class="admin-stat-number">

                            <?= $totalVolunteers; ?>

                        </div>

                    </div>


                    <div class="admin-card-icon">

                        <i
                            class="fa-solid fa-hand-holding-heart"
                        ></i>

                    </div>


                </div>


                <div class="mt-4">

                    <a
                        href="volunteers/index.php"
                        class="admin-btn-secondary"
                    >

                        <i
                            class="fa-solid fa-users"
                        ></i>

                        View Applications

                    </a>

                </div>


            </div>

        </div>

        <!-- =================================================
            DONATIONS
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">

                <div class="admin-stat">

                    <div>

                        <h3>
                            Donations
                        </h3>

                        <p class="admin-stat-label">
                            Donations
                        </p>

                        <div class="admin-stat-number">
                            <?= $totalDonations; ?>
                        </div>

                    </div>


                    <div class="admin-card-icon">

                        <i class="fa-solid fa-hand-holding-dollar"></i>

                    </div>

                </div>


                <div class="mt-4">

                    <a
                        href="donations/index.php"
                        class="admin-btn-secondary"
                    >

                        <i class="fa-solid fa-eye"></i>

                        View Donations

                    </a>

                </div>

            </div>

        </div>

        <!-- =================================================
             WEBSITE
        ================================================== -->

        <div class="col-md-6 col-lg-4">

            <div class="admin-card">


                <div class="admin-stat">


                    <div>

                        <h3>
                            Website
                        </h3>


                        <p>

                            View public website

                        </p>

                    </div>


                    <div class="admin-card-icon">

                        <i
                            class="fa-solid fa-globe"
                        ></i>

                    </div>


                </div>


                <div class="mt-4">

                    <a
                        href="../index.php"
                        target="_blank"
                        class="admin-btn-secondary"
                    >

                        Open Website

                    </a>

                </div>


            </div>

        </div>


    </div>


</main>



<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>