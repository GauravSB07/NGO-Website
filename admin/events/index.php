<?php

session_start();

include "../../config/db.php";


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: ../login.php");

    exit();

}


/* =========================================================
   GET EVENTS
========================================================= */

$sql = "
    SELECT 
        events.id,
        events.title,
        events.event_date,
        events.location,
        events.status,
        categories.name AS category_name,
        images.id AS cover_image_id

    FROM events

    INNER JOIN categories
        ON events.category_id = categories.id

    LEFT JOIN images
        ON events.id = images.event_id
        AND images.image_role = 'cover'

    ORDER BY events.event_date DESC
";


$result = mysqli_query(
    $conn,
    $sql
);

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
        Manage Events | Admin
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
        href="../../css/admin/admin.css"
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
            href="../dashboard.php"
        >

            Sevartha Foundation

            <span class="text-muted">
                | Admin
            </span>

        </a>


        <div class="admin-user">

            <span>

                <i class="fa-solid fa-user me-1"></i>

                <?= htmlspecialchars(
                    $_SESSION['admin_name']
                ); ?>

            </span>


            <a
                href="../logout.php"
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
     MAIN CONTENT
========================================================== -->

<main class="admin-container">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="admin-header">

        <div>

            <h1>
                Manage Events
            </h1>

            <p>
                View and manage all NGO events.
            </p>

        </div>


        <a
            href="add.php"
            class="admin-btn-primary"
        >

            <i class="fa-solid fa-plus"></i>

            Add New Event

        </a>

    </div>


    <!-- =====================================================
         SUCCESS MESSAGES
    ====================================================== -->

    <?php if (
        isset($_GET['success'])
    ) { ?>

        <div class="admin-alert admin-alert-success">

            <i
                class="fa-solid fa-circle-check me-2"
            ></i>

            Event added successfully!

        </div>

    <?php } ?>


    <?php if (
        isset($_GET['updated'])
    ) { ?>

        <div class="admin-alert admin-alert-success">

            <i
                class="fa-solid fa-circle-check me-2"
            ></i>

            Event updated successfully!

        </div>

    <?php } ?>


    <?php if (
        isset($_GET['deleted'])
    ) { ?>

        <div class="admin-alert admin-alert-success">

            <i
                class="fa-solid fa-circle-check me-2"
            ></i>

            Event deleted successfully!

        </div>

    <?php } ?>


    <!-- =====================================================
         EVENTS TABLE
    ====================================================== -->

    <div class="admin-table-card">


        <div class="table-responsive">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            Image
                        </th>

                        <th>
                            Event
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Location
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (
                    mysqli_num_rows($result) > 0
                ) { ?>


                    <?php while (
                        $event =
                        mysqli_fetch_assoc($result)
                    ) { ?>


                        <tr>


                            <!-- IMAGE -->

                            <td>

                                <?php if (
                                    !empty($event['cover_image_id'])
                                ) { ?>

                                    <img
                                        src="../../event_image.php?id=<?= (int) $event['cover_image_id']; ?>"
                                        class="event-image"
                                        alt="<?= htmlspecialchars($event['title']); ?>"
                                    >

                                <?php } else { ?>

                                    <span class="text-muted">
                                        No Image
                                    </span>

                                <?php } ?>

                            </td>


                            <!-- EVENT -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $event['title']
                                    ); ?>

                                </strong>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <span class="admin-badge">

                                    <?= htmlspecialchars(
                                        $event['category_name']
                                    ); ?>

                                </span>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php if (
                                    !empty(
                                        $event['event_date']
                                    )
                                ) { ?>

                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $event['event_date']
                                        )
                                    ); ?>

                                <?php } else { ?>

                                    <span
                                        class="text-muted"
                                    >

                                        —

                                    </span>

                                <?php } ?>

                            </td>


                            <!-- LOCATION -->

                            <td>

                                <?= htmlspecialchars(
                                    $event['location']
                                ); ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    $event['status']
                                    === 'Active'
                                ) { ?>

                                    <span
                                        class="admin-status active"
                                    >

                                        Active

                                    </span>

                                <?php } else { ?>

                                    <span
                                        class="admin-status inactive"
                                    >

                                        Inactive

                                    </span>

                                <?php } ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div
                                    class="admin-table-actions"
                                >


                                    <!-- VIEW -->

                                    <a
                                        href="../../our-work/event.php?id=<?= $event['id']; ?>"
                                        target="_blank"
                                        class="admin-action-btn view"
                                        title="View Event"
                                    >

                                        <i
                                            class="fa-solid fa-eye"
                                        ></i>

                                    </a>


                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?= $event['id']; ?>"
                                        class="admin-action-btn edit"
                                        title="Edit Event"
                                    >

                                        <i
                                            class="fa-solid fa-pen"
                                        ></i>

                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="delete.php?id=<?= $event['id']; ?>"
                                        class="admin-action-btn delete"
                                        title="Delete Event"
                                        onclick="return confirm('Are you sure you want to delete this event?');"
                                    >

                                        <i
                                            class="fa-solid fa-trash"
                                        ></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php } ?>


                <?php } else { ?>


                    <!-- EMPTY STATE -->

                    <tr>

                        <td
                            colspan="7"
                            class="admin-empty-table"
                        >

                            <div
                                class="admin-empty-icon"
                            >

                                <i
                                    class="fa-regular fa-calendar-xmark"
                                ></i>

                            </div>


                            <h3>
                                No Events Found
                            </h3>


                            <p>

                                Start by adding your
                                first event.

                            </p>


                            <a
                                href="add.php"
                                class="admin-btn-primary"
                            >

                                <i
                                    class="fa-solid fa-plus"
                                ></i>

                                Add Event

                            </a>

                        </td>

                    </tr>


                <?php } ?>


                </tbody>

            </table>

        </div>


    </div>


</main>


</body>

</html>