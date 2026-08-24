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
   DELETE MESSAGE
========================================================= */

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $deleteId = (int) $_GET['delete'];

    $deleteSql = "
        DELETE FROM contact_messages
        WHERE id = ?
    ";

    $deleteStmt = mysqli_prepare(
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

    /*
     * Redirect so refreshing the page
     * does not delete the message again.
     */

    header(
        "Location: index.php?deleted=1"
    );

    exit();
}


/* =========================================================
   GET CONTACT MESSAGES
========================================================= */

$sql = "
    SELECT
        id,
        name,
        email,
        phone,
        message,
        created_at
    FROM contact_messages
    ORDER BY created_at DESC
";

$result = mysqli_query(
    $conn,
    $sql
);


/* =========================================================
   COUNT MESSAGES
========================================================= */

$totalMessages = 0;

$countQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM contact_messages"
);

if ($countQuery) {

    $countData = mysqli_fetch_assoc(
        $countQuery
    );

    $totalMessages = (int) $countData['total'];
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
        Contact Messages | Admin
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

        .contact-page {

            padding: 40px;

        }


        .contact-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 30px;

            gap: 20px;

        }


        .contact-header h1 {

            margin: 0;

        }


        .contact-header p {

            margin: 5px 0 0;

            color: #777;

        }


        .contact-count {

            background: #f1f1f1;

            padding: 10px 18px;

            border-radius: 8px;

            font-weight: 600;

        }


        .contact-table-wrapper {

            background: #fff;

            border-radius: 12px;

            overflow-x: auto;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.06);

        }


        .contact-table {

            width: 100%;

            margin: 0;

            border-collapse: collapse;

        }


        .contact-table th {

            background: #f5f5f5;

            padding: 16px;

            text-align: left;

            font-size: 14px;

            white-space: nowrap;

        }


        .contact-table td {

            padding: 16px;

            border-top: 1px solid #eee;

            vertical-align: middle;

        }


        .contact-name {

            font-weight: 600;

        }


        .contact-email {

            color: #555;

            word-break: break-word;

        }


        .contact-message-preview {

            max-width: 300px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

        }


        .contact-date {

            white-space: nowrap;

            color: #666;

        }


        .contact-actions {

            display: flex;

            gap: 8px;

            white-space: nowrap;

        }


        .btn-view {

            border: none;

            background: #333;

            color: #fff;

            padding: 7px 12px;

            border-radius: 6px;

            text-decoration: none;

            cursor: pointer;

        }


        .btn-view:hover {

            background: #222;

            color: #fff;

        }


        .btn-delete {

            border: none;

            background: #dc3545;

            color: #fff;

            padding: 7px 12px;

            border-radius: 6px;

            cursor: pointer;

        }


        .btn-delete:hover {

            background: #bb2d3b;

        }


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


        @media (max-width: 768px) {

            .contact-page {

                padding: 20px;

            }


            .contact-header {

                align-items: flex-start;

                flex-direction: column;

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
     CONTACT MESSAGES
========================================================== -->

<main class="admin-container contact-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="contact-header">


        <div>

            <h1>

                Contact Messages

            </h1>


            <p>

                Messages submitted through the
                Contact Us form.

            </p>

        </div>


        <div class="contact-count">

            <i
                class="fa-solid fa-envelope"
            ></i>

            <?= $totalMessages; ?>

            Messages

        </div>


    </div>


    <!-- =====================================================
         SUCCESS MESSAGE
    ====================================================== -->

    <?php if (isset($_GET['deleted'])): ?>

        <div
            class="alert alert-success"
            role="alert"
        >

            <i
                class="fa-solid fa-circle-check"
            ></i>

            Contact message deleted successfully.

        </div>

    <?php endif; ?>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="contact-table-wrapper">


        <?php if ($result && mysqli_num_rows($result) > 0): ?>


            <table class="contact-table">


                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Message
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


                        <!-- NUMBER -->

                        <td>

                            <?= $number++; ?>

                        </td>


                        <!-- NAME -->

                        <td>

                            <div class="contact-name">

                                <?= htmlspecialchars(
                                    $row['name']
                                ); ?>

                            </div>

                        </td>


                        <!-- EMAIL -->

                        <td>

                            <div class="contact-email">

                                <?= htmlspecialchars(
                                    $row['email']
                                ); ?>

                            </div>

                        </td>


                        <!-- PHONE -->

                        <td>

                            <?= htmlspecialchars(
                                $row['phone']
                            ); ?>

                        </td>


                        <!-- MESSAGE -->

                        <td>

                            <div
                                class="contact-message-preview"
                                title="<?= htmlspecialchars(
                                    $row['message']
                                ); ?>"
                            >

                                <?= htmlspecialchars(
                                    $row['message']
                                ); ?>

                            </div>

                        </td>


                        <!-- DATE -->

                        <td>

                            <div class="contact-date">

                                <?= date(
                                    'd M Y, h:i A',
                                    strtotime(
                                        $row['created_at']
                                    )
                                ); ?>

                            </div>

                        </td>


                        <!-- ACTIONS -->

                        <td>


                            <div
                                class="contact-actions"
                            >


                                <!-- VIEW -->

                                <button
                                    type="button"
                                    class="btn-view"
                                    data-bs-toggle="modal"
                                    data-bs-target="#messageModal<?= $row['id']; ?>"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                    View

                                </button>


                                <!-- DELETE -->

                                <button
                                    type="button"
                                    class="btn-delete"
                                    onclick="confirmDelete(
                                        <?= (int) $row['id']; ?>
                                    )"
                                >

                                    <i
                                        class="fa-solid fa-trash"
                                    ></i>

                                </button>


                            </div>


                        </td>


                    </tr>


                    <!-- =================================================
                         VIEW MESSAGE MODAL
                    ================================================== -->

                    <div
                        class="modal fade"
                        id="messageModal<?= $row['id']; ?>"
                        tabindex="-1"
                        aria-hidden="true"
                    >

                        <div
                            class="modal-dialog modal-dialog-centered"
                        >

                            <div class="modal-content">


                                <div class="modal-header">

                                    <h5 class="modal-title">

                                        Contact Message

                                    </h5>


                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                    ></button>

                                </div>


                                <div class="modal-body">


                                    <div class="mb-3">

                                        <strong>
                                            Name
                                        </strong>

                                        <div>

                                            <?= htmlspecialchars(
                                                $row['name']
                                            ); ?>

                                        </div>

                                    </div>


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


                                <div class="modal-footer">

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
                 NO MESSAGES
            ================================================== -->

            <div class="empty-state">


                <i
                    class="fa-regular fa-envelope"
                ></i>


                <h3>

                    No Contact Messages

                </h3>


                <p>

                    No one has submitted the
                    Contact Us form yet.

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
            "Are you sure you want to delete this contact message?"
        );


    if (confirmed) {

        window.location.href =
            "index.php?delete=" + id;

    }

}

</script>


</body>

</html>