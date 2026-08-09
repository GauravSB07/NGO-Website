<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

/* Count Events */

$eventQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM events"
);

$eventData = mysqli_fetch_assoc($eventQuery);

$totalEvents = $eventData['total'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1"
>

<title>Admin Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
>

<style>

body {
    background: #f5f6fa;
}


/* Navbar */

.admin-navbar {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}


.admin-container {
    max-width: 1200px;
    margin: auto;
}


/* Cards */

.dashboard-card {

    border: none;
    border-radius: 15px;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

}

.dashboard-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.1);

}


.icon-box {

    width: 65px;
    height: 65px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #f0f3ff;

}


</style>

</head>


<body>


<!-- Admin Navbar -->

<nav class="navbar admin-navbar">

    <div
    class="container-fluid px-4"
    >

        <a
        class="navbar-brand fw-bold"
        href="dashboard.php"
        >

            Sevartha Foundation

            <span class="text-muted">
                | Admin
            </span>

        </a>


        <div class="d-flex align-items-center gap-3">

            <span class="text-muted">

                <i class="fa-solid fa-user me-1"></i>

                <?= htmlspecialchars(
                    $_SESSION['admin_name']
                ); ?>

            </span>


            <a
            href="logout.php"
            class="btn btn-danger btn-sm"
            >

                <i class="fa-solid fa-right-from-bracket me-1"></i>

                Logout

            </a>

        </div>

    </div>

</nav>


<!-- Dashboard -->

<div class="admin-container px-3 px-md-4 py-5">


    <!-- Heading -->

    <div class="mb-5">

        <h1 class="fw-bold">

            Welcome,
            <?= htmlspecialchars(
                $_SESSION['admin_name']
            ); ?>

        </h1>

        <p class="text-muted">

            Manage your Sevartha Foundation website
            from here.

        </p>

    </div>


    <!-- Dashboard Cards -->

    <div class="row g-4">


        <!-- EVENTS -->

        <div class="col-md-6 col-lg-4">

            <div
            class="card dashboard-card h-100 shadow-sm"
            >

                <div class="card-body p-4">


                    <div
                    class="d-flex justify-content-between align-items-start"
                    >

                        <div>

                            <h5 class="fw-bold mb-1">

                                Events

                            </h5>

                            <p class="text-muted mb-3">

                                Total Events

                            </p>

                            <h2 class="fw-bold mb-3">

                                <?= $totalEvents; ?>

                            </h2>

                        </div>


                        <div class="icon-box">

                            <i
                            class="fa-solid fa-calendar-days fa-2x"
                            ></i>

                        </div>

                    </div>


                    <div class="d-flex gap-2">

                        <a
                        href="events/add.php"
                        class="btn btn-primary"
                        >

                            <i
                            class="fa-solid fa-plus me-1"
                            ></i>

                            Add Event

                        </a>


                        <a
                        href="events/index.php"
                        class="btn btn-outline-dark"
                        >

                            Manage

                        </a>

                    </div>


                </div>

            </div>

        </div>


        <!-- CATEGORIES -->

        <div class="col-md-6 col-lg-4">

            <div
            class="card dashboard-card h-100 shadow-sm"
            >

                <div class="card-body p-4">


                    <div
                    class="d-flex justify-content-between align-items-start"
                    >

                        <div>

                            <h5 class="fw-bold mb-1">

                                Categories

                            </h5>

                            <p class="text-muted">

                                Manage work categories

                            </p>

                        </div>


                        <div class="icon-box">

                            <i
                            class="fa-solid fa-layer-group fa-2x"
                            ></i>

                        </div>

                    </div>


                    <a
                    href="categories/index.php"
                    class="btn btn-outline-dark"
                    >

                        Manage Categories

                    </a>


                </div>

            </div>

        </div>


        <!-- WEBSITE -->

        <div class="col-md-6 col-lg-4">

            <div
            class="card dashboard-card h-100 shadow-sm"
            >

                <div class="card-body p-4">


                    <div
                    class="d-flex justify-content-between align-items-start"
                    >

                        <div>

                            <h5 class="fw-bold mb-1">

                                Website

                            </h5>

                            <p class="text-muted">

                                View public website

                            </p>

                        </div>


                        <div class="icon-box">

                            <i
                            class="fa-solid fa-globe fa-2x"
                            ></i>

                        </div>

                    </div>


                    <a
                    href="../index.php"
                    target="_blank"
                    class="btn btn-outline-dark"
                    >

                        Open Website

                    </a>


                </div>

            </div>

        </div>


    </div>


</div>


</body>

</html>