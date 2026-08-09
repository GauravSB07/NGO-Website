<?php

session_start();

include "../../config/db.php";

/* Check Admin Login */

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}


/* Get Events */

$sql = "
    SELECT 
        events.id,
        events.title,
        events.event_date,
        events.location,
        events.cover_image,
        events.status,
        categories.name AS category_name
    FROM events
    INNER JOIN categories
        ON events.category_id = categories.id
    ORDER BY events.event_date DESC
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1"
>

<title>Manage Events | Admin</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>


<style>

body {

    background: #f5f6fa;

}

.admin-container {

    max-width: 1200px;

    margin: 50px auto;

}

.card {

    border: none;

    border-radius: 12px;

}

.event-image {

    width: 90px;

    height: 65px;

    object-fit: cover;

    border-radius: 8px;

}

</style>

</head>


<body>


<div class="admin-container">


    <!-- Header -->

    <div
    class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                Manage Events

            </h2>

            <p class="text-muted mb-0">

                View and manage all NGO events.

            </p>

        </div>


        <a
        href="add.php"
        class="btn btn-primary"
        >

            + Add New Event

        </a>

    </div>


    <!-- Success Message -->

    <?php if (isset($_GET['success'])) { ?>

        <div class="alert alert-success">
            ✓ Event added successfully!
        </div>

    <?php } ?>


    <?php if (isset($_GET['updated'])) { ?>

        <div class="alert alert-success">
            ✓ Event updated successfully!
        </div>

    <?php } ?>


    <?php if (isset($_GET['deleted'])) { ?>

        <div class="alert alert-success">
            ✓ Event deleted successfully!
        </div>

    <?php } ?>


    <!-- Events Table -->

    <div class="card shadow-sm">

        <div class="card-body p-0">


            <div class="table-responsive">

                <table
                class="table table-hover align-middle mb-0"
                >

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
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


                                <!-- Image -->

                                <td class="px-4">

                                    <?php if (
                                        !empty(
                                            $event['cover_image']
                                        )
                                    ) { ?>

                                        <img
                                        src="../../uploads/events/<?= htmlspecialchars(
                                            $event['cover_image']
                                        ); ?>"
                                        class="event-image"
                                        >

                                    <?php } else { ?>

                                        <span
                                        class="text-muted"
                                        >

                                            No Image

                                        </span>

                                    <?php } ?>

                                </td>


                                <!-- Event -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $event['title']
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- Category -->

                                <td>

                                    <span
                                    class="badge bg-info text-dark"
                                    >

                                        <?= htmlspecialchars(
                                            $event['category_name']
                                        ); ?>

                                    </span>

                                </td>


                                <!-- Date -->

                                <td>

                                    <?= !empty(
                                        $event['event_date']
                                    )
                                        ? date(
                                            "d M Y",
                                            strtotime(
                                                $event['event_date']
                                            )
                                        )
                                        : "-"
                                    ?>

                                </td>


                                <!-- Location -->

                                <td>

                                    <?= htmlspecialchars(
                                        $event['location']
                                    ); ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <?php if (
                                        $event['status']
                                        === 'Active'
                                    ) { ?>

                                        <span
                                        class="badge bg-success"
                                        >

                                            Active

                                        </span>

                                    <?php } else { ?>

                                        <span
                                        class="badge bg-secondary"
                                        >

                                            Inactive

                                        </span>

                                    <?php } ?>

                                </td>


                                <!-- Actions -->

                                <td>

                                    <div
                                    class="d-flex justify-content-center gap-2"
                                    >


                                        <!-- View -->

                                        <a
                                        href="../../our-work/event.php?id=<?= $event['id']; ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                        >

                                            View

                                        </a>


                                        <!-- Edit -->

                                        <a
                                        href="edit.php?id=<?= $event['id']; ?>"
                                        class="btn btn-sm btn-outline-warning"
                                        >

                                            Edit

                                        </a>


                                        <!-- Delete -->

                                        <a
                                        href="delete.php?id=<?= $event['id']; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm(
                                            'Are you sure you want to delete this event?'
                                        );"
                                        >

                                            Delete

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <tr>

                            <td
                            colspan="7"
                            class="text-center py-5"
                            >

                                <h5>

                                    No Events Found

                                </h5>

                                <p class="text-muted">

                                    Start by adding your first event.

                                </p>

                                <a
                                href="add.php"
                                class="btn btn-primary"
                                >

                                    + Add Event

                                </a>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>


</body>

</html>