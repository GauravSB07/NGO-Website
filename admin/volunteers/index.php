<?php

session_start();


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: ../login.php");

    exit();

}


include "../../config/db.php";


/* =========================================================
   UPDATE APPLICATION STATUS
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_status'])
) {

    $applicationId =
        isset($_POST['application_id'])
            ? (int) $_POST['application_id']
            : 0;

    $newStatus =
        trim($_POST['status'] ?? '');


    /*
     * Only allow the four statuses
     * that exist in the database.
     */

    $allowedStatuses = [
        'New',
        'Contacted',
        'Accepted',
        'Rejected'
    ];


    if (
        $applicationId > 0 &&
        in_array(
            $newStatus,
            $allowedStatuses,
            true
        )
    ) {


        $updateSql = "
            UPDATE volunteer_applications
            SET status = ?
            WHERE id = ?
        ";


        $updateStmt =
            mysqli_prepare(
                $conn,
                $updateSql
            );


        if ($updateStmt) {

            mysqli_stmt_bind_param(
                $updateStmt,
                "si",
                $newStatus,
                $applicationId
            );


            mysqli_stmt_execute(
                $updateStmt
            );


            mysqli_stmt_close(
                $updateStmt
            );

        }

    }


    /*
     * Redirect after updating.
     *
     * This prevents the form from being
     * submitted again when the page is refreshed.
     */

    header(
        "Location: index.php?updated=1"
    );

    exit();

}


/* =========================================================
   DELETE APPLICATION
========================================================= */

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $deleteId =
        (int) $_GET['delete'];


    if ($deleteId > 0) {


        $deleteSql = "
            DELETE FROM volunteer_applications
            WHERE id = ?
        ";


        $deleteStmt =
            mysqli_prepare(
                $conn,
                $deleteSql
            );


        if ($deleteStmt) {

            mysqli_stmt_bind_param(
                $deleteStmt,
                "i",
                $deleteId
            );


            mysqli_stmt_execute(
                $deleteStmt
            );


            mysqli_stmt_close(
                $deleteStmt
            );

        }

    }


    /*
     * Redirect after deletion.
     */

    header(
        "Location: index.php?deleted=1"
    );

    exit();

}


/* =========================================================
   GET VOLUNTEER APPLICATIONS
========================================================= */

$sql = "
    SELECT
        id,
        full_name,
        city,
        email,
        phone,
        interests,
        availability,
        previous_experience,
        message,
        status,
        created_at
    FROM volunteer_applications
    ORDER BY created_at DESC
";


$result =
    mysqli_query(
        $conn,
        $sql
    );


/* =========================================================
   COUNT APPLICATIONS
========================================================= */

$totalApplications = 0;


$countQuery = mysqli_query(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM volunteer_applications
    "
);


if ($countQuery) {

    $countData =
        mysqli_fetch_assoc(
            $countQuery
        );


    $totalApplications =
        (int) $countData['total'];

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
        Volunteer Applications | Admin
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


    <style>

        /* =================================================
           PAGE
        ================================================= */

        .volunteer-page {

            padding: 40px;

        }


        /* =================================================
           HEADER
        ================================================= */

        .volunteer-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 30px;

        }


        .volunteer-header h1 {

            margin: 0;

        }


        .volunteer-header p {

            margin: 5px 0 0;

            color: #777;

        }


        .volunteer-count {

            background: #f1f1f1;

            padding: 10px 18px;

            border-radius: 8px;

            font-weight: 600;

            white-space: nowrap;

        }


        /* =================================================
           TABLE WRAPPER
        ================================================= */

        .volunteer-table-wrapper {

            background: #fff;

            border-radius: 12px;

            overflow-x: auto;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.06);

        }


        /* =================================================
           TABLE
        ================================================= */

        .volunteer-table {

            width: 100%;

            margin: 0;

            border-collapse: collapse;

        }


        .volunteer-table th {

            background: #f5f5f5;

            padding: 16px;

            text-align: left;

            font-size: 14px;

            white-space: nowrap;

        }


        .volunteer-table td {

            padding: 16px;

            border-top: 1px solid #eee;

            vertical-align: middle;

        }


        /* =================================================
           NAME
        ================================================= */

        .volunteer-name {

            font-weight: 600;

            white-space: nowrap;

        }


        /* =================================================
           EMAIL
        ================================================= */

        .volunteer-email {

            color: #555;

            word-break: break-word;

        }


        /* =================================================
           INTERESTS
        ================================================= */

        .volunteer-interests {

            max-width: 250px;

            line-height: 1.5;

        }


        /* =================================================
           DATE
        ================================================= */

        .volunteer-date {

            white-space: nowrap;

            color: #666;

        }


        /* =================================================
           ACTIONS
        ================================================= */

        .volunteer-actions {

            display: flex;

            align-items: center;

            gap: 8px;

            white-space: nowrap;

        }


        /* =================================================
           VIEW BUTTON
        ================================================= */

        .btn-view {

            border: none;

            background: #333;

            color: #fff;

            padding: 7px 12px;

            border-radius: 6px;

        }


        .btn-view:hover {

            background: #222;

            color: #fff;

        }


        /* =================================================
           DELETE BUTTON
        ================================================= */

        .btn-delete {

            border: none;

            background: #dc3545;

            color: #fff;

            padding: 7px 12px;

            border-radius: 6px;

        }


        .btn-delete:hover {

            background: #bb2d3b;

        }


        /* =================================================
           STATUS
        ================================================= */

        .status-form {

            display: flex;

            align-items: center;

            gap: 6px;

        }


        .status-select {

            min-width: 120px;

            padding: 6px 8px;

            border: 1px solid #ddd;

            border-radius: 6px;

            background: #fff;

            font-size: 13px;

        }


        .status-save {

            border: none;

            background: #333;

            color: #fff;

            padding: 6px 9px;

            border-radius: 6px;

        }


        .status-save:hover {

            background: #222;

        }


        /* =================================================
           EMPTY STATE
        ================================================= */

        .empty-state {

            text-align: center;

            padding: 60px 20px;

            color: #777;

        }


        .empty-state i {

            font-size: 45px;

            margin-bottom: 15px;

            opacity: 0.5;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 768px) {

            .volunteer-page {

                padding: 20px;

            }


            .volunteer-header {

                flex-direction: column;

                align-items: flex-start;

            }

        }

    </style>

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

                <i
                    class="fa-solid fa-user me-1"
                ></i>

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
     VOLUNTEER APPLICATIONS
========================================================== -->

<main
    class="admin-container volunteer-page"
>


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="volunteer-header">


        <div>

            <h1>

                Volunteer Applications

            </h1>


            <p>

                Manage applications submitted through
                the Volunteer With Us form.

            </p>

        </div>


        <div class="volunteer-count">

            <i
                class="fa-solid fa-users"
            ></i>

            <?= $totalApplications; ?>

            Applications

        </div>


    </div>



    <!-- =====================================================
         UPDATED MESSAGE
    ====================================================== -->

    <?php if (isset($_GET['updated'])): ?>

        <div
            class="alert alert-success"
            role="alert"
        >

            <i
                class="fa-solid fa-circle-check"
            ></i>

            Application status updated successfully.

        </div>

    <?php endif; ?>



    <!-- =====================================================
         DELETED MESSAGE
    ====================================================== -->

    <?php if (isset($_GET['deleted'])): ?>

        <div
            class="alert alert-success"
            role="alert"
        >

            <i
                class="fa-solid fa-circle-check"
            ></i>

            Volunteer application deleted successfully.

        </div>

    <?php endif; ?>



    <!-- =====================================================
         APPLICATION TABLE
    ====================================================== -->

    <div class="volunteer-table-wrapper">


        <?php if (
            $result &&
            mysqli_num_rows($result) > 0
        ): ?>


            <table class="volunteer-table">


                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            City
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Interests
                        </th>

                        <th>
                            Availability
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $number = 1;

                while (
                    $row =
                    mysqli_fetch_assoc($result)
                ):

                ?>


                    <tr>


                        <!-- =================================================
                             NUMBER
                        ================================================== -->

                        <td>

                            <?= $number++; ?>

                        </td>



                        <!-- =================================================
                             NAME
                        ================================================== -->

                        <td>

                            <div class="volunteer-name">

                                <?= htmlspecialchars(
                                    $row['full_name']
                                ); ?>

                            </div>

                        </td>



                        <!-- =================================================
                             CITY
                        ================================================== -->

                        <td>

                            <?= htmlspecialchars(
                                $row['city']
                            ); ?>

                        </td>



                        <!-- =================================================
                             EMAIL
                        ================================================== -->

                        <td>

                            <div class="volunteer-email">

                                <?= htmlspecialchars(
                                    $row['email']
                                ); ?>

                            </div>

                        </td>



                        <!-- =================================================
                             PHONE
                        ================================================== -->

                        <td>

                            <?= htmlspecialchars(
                                $row['phone']
                            ); ?>

                        </td>



                        <!-- =================================================
                             INTERESTS
                        ================================================== -->

                        <td>

                            <div
                                class="volunteer-interests"
                            >

                                <?= htmlspecialchars(
                                    $row['interests']
                                ); ?>

                            </div>

                        </td>



                        <!-- =================================================
                             AVAILABILITY
                        ================================================== -->

                        <td>

                            <?= htmlspecialchars(
                                $row['availability']
                            ); ?>

                        </td>



                        <!-- =================================================
                             STATUS
                        ================================================== -->

                        <td>


                            <form
                                method="POST"
                                class="status-form"
                            >


                                <input
                                    type="hidden"
                                    name="application_id"
                                    value="<?= (int) $row['id']; ?>"
                                >


                                <select
                                    name="status"
                                    class="status-select"
                                >


                                    <option
                                        value="New"
                                        <?= (
                                            $row['status'] === 'New'
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        New

                                    </option>


                                    <option
                                        value="Contacted"
                                        <?= (
                                            $row['status'] === 'Contacted'
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        Contacted

                                    </option>


                                    <option
                                        value="Accepted"
                                        <?= (
                                            $row['status'] === 'Accepted'
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        Accepted

                                    </option>


                                    <option
                                        value="Rejected"
                                        <?= (
                                            $row['status'] === 'Rejected'
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        Rejected

                                    </option>


                                </select>


                                <button
                                    type="submit"
                                    name="update_status"
                                    class="status-save"
                                    title="Update status"
                                >

                                    <i
                                        class="fa-solid fa-check"
                                    ></i>

                                </button>


                            </form>


                        </td>



                        <!-- =================================================
                             DATE
                        ================================================== -->

                        <td>

                            <div
                                class="volunteer-date"
                            >

                                <?= date(
                                    'd M Y, h:i A',
                                    strtotime(
                                        $row['created_at']
                                    )
                                ); ?>

                            </div>

                        </td>



                        <!-- =================================================
                             ACTIONS
                        ================================================== -->

                        <td>


                            <div
                                class="volunteer-actions"
                            >


                                <!-- VIEW -->

                                <button
                                    type="button"
                                    class="btn-view"
                                    data-bs-toggle="modal"
                                    data-bs-target="#applicationModal<?= (int) $row['id']; ?>"
                                    title="View application"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                </button>



                                <!-- DELETE -->

                                <button
                                    type="button"
                                    class="btn-delete"
                                    onclick="confirmDelete(
                                        <?= (int) $row['id']; ?>
                                    )"
                                    title="Delete application"
                                >

                                    <i
                                        class="fa-solid fa-trash"
                                    ></i>

                                </button>


                            </div>


                        </td>


                    </tr>



                    <!-- =================================================
                         VIEW APPLICATION MODAL
                    ================================================== -->

                    <div
                        class="modal fade"
                        id="applicationModal<?= (int) $row['id']; ?>"
                        tabindex="-1"
                        aria-hidden="true"
                    >

                        <div
                            class="modal-dialog modal-lg modal-dialog-centered"
                        >

                            <div
                                class="modal-content"
                            >


                                <!-- MODAL HEADER -->

                                <div
                                    class="modal-header"
                                >

                                    <h5
                                        class="modal-title"
                                    >

                                        Volunteer Application

                                    </h5>


                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                    ></button>

                                </div>



                                <!-- MODAL BODY -->

                                <div
                                    class="modal-body"
                                >


                                    <!-- NAME -->

                                    <div class="mb-3">

                                        <strong>
                                            Full Name
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['full_name']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- CITY -->

                                    <div class="mb-3">

                                        <strong>
                                            City
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['city']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- EMAIL -->

                                    <div class="mb-3">

                                        <strong>
                                            Email
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['email']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- PHONE -->

                                    <div class="mb-3">

                                        <strong>
                                            Phone
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['phone']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- INTERESTS -->

                                    <div class="mb-3">

                                        <strong>
                                            Areas of Interest
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['interests']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- AVAILABILITY -->

                                    <div class="mb-3">

                                        <strong>
                                            Availability
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['availability']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- PREVIOUS EXPERIENCE -->

                                    <div class="mb-3">

                                        <strong>
                                            Previous Volunteering Experience
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['previous_experience']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- STATUS -->

                                    <div class="mb-3">

                                        <strong>
                                            Current Status
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['status']
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- DATE -->

                                    <div class="mb-3">

                                        <strong>
                                            Submitted
                                        </strong>

                                        <div>

                                            <?= date(
                                                'd M Y, h:i A',
                                                strtotime(
                                                    $row['created_at']
                                                )
                                            ); ?>

                                        </div>

                                    </div>



                                    <!-- MESSAGE -->

                                    <div>

                                        <strong>
                                            Message
                                        </strong>


                                        <div
                                            class="mt-2 p-3 bg-light rounded"
                                            style="white-space: pre-wrap;"
                                        >

                                            <?= htmlspecialchars(
                                                $row['message']
                                            ); ?>

                                        </div>

                                    </div>


                                </div>



                                <!-- MODAL FOOTER -->

                                <div
                                    class="modal-footer"
                                >

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal"
                                    >

                                        Close

                                    </button>

                                </div>


                            </div>

                        </div>

                    </div>


                <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state">


                <i
                    class="fa-solid fa-users"
                ></i>


                <h3>

                    No Volunteer Applications

                </h3>


                <p>

                    No one has submitted a volunteer
                    application yet.

                </p>


            </div>


        <?php endif; ?>


    </div>


</main>



<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>



<script>

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

function confirmDelete(id) {


    const confirmed =
        confirm(
            "Are you sure you want to delete this volunteer application?"
        );


    if (confirmed) {

        window.location.href =
            "index.php?delete=" + id;

    }

}

</script>


</body>

</html>