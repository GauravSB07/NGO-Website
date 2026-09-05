<?php

/* =========================================================
   SESSION
========================================================= */

session_start();


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: ../login.php");
    exit();

}


/* =========================================================
   DATABASE CONNECTION
========================================================= */

include "../../config/db.php";


/* =========================================================
   MESSAGE VARIABLES
========================================================= */

$successMessage = '';
$errorMessage = '';


/* =========================================================
   HANDLE APPROVE / REJECT
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    isset($_POST['donation_id'])
) {

    $donationId = (int) $_POST['donation_id'];
    $action = $_POST['action'];


    /* =====================================================
       APPROVE DONATION
       pending -> completed
    ===================================================== */

    if ($action === 'approve') {

        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE donations
            SET
                payment_status = 'completed',
                updated_at = CURRENT_TIMESTAMP
            WHERE
                id = ?
                AND payment_status = 'pending'
            "
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $donationId
            );


            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {

                    $successMessage =
                        "Donation approved successfully.";

                } else {

                    $errorMessage =
                        "Donation could not be approved. It may already have been processed.";
                }

            } else {

                $errorMessage =
                    "Database error while approving donation: " .
                    mysqli_stmt_error($stmt);
            }


            mysqli_stmt_close($stmt);

        } else {

            $errorMessage =
                "Unable to prepare approval request: " .
                mysqli_error($conn);
        }
    }


    /* =====================================================
       REJECT DONATION
       pending -> failed
    ===================================================== */

    elseif ($action === 'reject') {

        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE donations
            SET
                payment_status = 'failed',
                updated_at = CURRENT_TIMESTAMP
            WHERE
                id = ?
                AND payment_status = 'pending'
            "
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $donationId
            );


            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {

                    $successMessage =
                        "Donation rejected successfully.";

                } else {

                    $errorMessage =
                        "Donation could not be rejected. It may already have been processed.";
                }

            } else {

                $errorMessage =
                    "Database error while rejecting donation: " .
                    mysqli_stmt_error($stmt);
            }


            mysqli_stmt_close($stmt);

        } else {

            $errorMessage =
                "Unable to prepare rejection request: " .
                mysqli_error($conn);
        }
    }
}


/* =========================================================
   GET DONATIONS
========================================================= */

$donations = [];

$query = mysqli_query(
    $conn,
    "
    SELECT
        id,
        donor_name,
        donor_email,
        donor_phone,
        donation_purpose,
        donation_amount,
        payment_status,
        payment_submitted_at,
        payment_method,
        transaction_id,
        created_at,
        updated_at
    FROM donations
    ORDER BY
        CASE
            WHEN payment_status = 'pending' THEN 1
            WHEN payment_status = 'completed' THEN 2
            WHEN payment_status = 'failed' THEN 3
            WHEN payment_status = 'cancelled' THEN 4
            ELSE 5
        END,
        COALESCE(
            payment_submitted_at,
            created_at
        ) DESC
    "
);


if ($query) {

    while (
        $row = mysqli_fetch_assoc($query)
    ) {

        $donations[] = $row;
    }

} else {

    $errorMessage =
        "Unable to load donations: " .
        mysqli_error($conn);
}


/* =========================================================
   COUNT PENDING DONATIONS
========================================================= */

$pendingCount = 0;

$pendingQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM donations
    WHERE payment_status = 'pending'
    "
);


if ($pendingQuery) {

    $pendingRow =
        mysqli_fetch_assoc($pendingQuery);

    $pendingCount =
        (int) ($pendingRow['total'] ?? 0);
}


/* =========================================================
   COUNT COMPLETED DONATIONS
========================================================= */

$completedCount = 0;

$completedQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM donations
    WHERE payment_status = 'completed'
    "
);


if ($completedQuery) {

    $completedRow =
        mysqli_fetch_assoc($completedQuery);

    $completedCount =
        (int) ($completedRow['total'] ?? 0);
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
        Donations | Admin | Sevartha Foundation
    </title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- FONT AWESOME -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- ADMIN CSS -->

    <link
        rel="stylesheet"
        href="../../css/admin/admin.css"
    >


    <style>

        .donation-table-wrapper {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            overflow-x: auto;
        }


        .donation-table {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
        }


        .donation-table th {
            text-align: left;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666;
            background: #f7f7f7;
            white-space: nowrap;
        }


        .donation-table td {
            padding: 16px 15px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: middle;
        }


        .donor-name {
            font-weight: 600;
        }


        .donor-email {
            font-size: 14px;
            color: #666;
        }


        .donation-amount {
            font-weight: 700;
            white-space: nowrap;
        }


        .donation-phone {
            white-space: nowrap;
        }


        .donation-date {
            white-space: nowrap;
            font-size: 14px;
        }


        .transaction-id {
            font-family: monospace;
            font-size: 13px;
            background: #f6f6f6;
            padding: 6px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }


        /* =====================================================
           STATUS
        ===================================================== */

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }


        .payment-status.pending {
            background: #fff4d6;
            color: #9a6800;
        }


        .payment-status.completed {
            background: #e8f7ee;
            color: #16834b;
        }


        .payment-status.failed {
            background: #fdeaea;
            color: #c62828;
        }


        .payment-status.cancelled {
            background: #eeeeee;
            color: #666666;
        }


        /* =====================================================
           ACTION BUTTONS
        ===================================================== */

        .donation-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }


        .donation-action-btn {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }


        .approve-btn {
            background: #198754;
            color: #ffffff;
        }


        .approve-btn:hover {
            background: #157347;
        }


        .reject-btn {
            background: #dc3545;
            color: #ffffff;
        }


        .reject-btn:hover {
            background: #bb2d3b;
        }


        .view-only {
            color: #777;
            font-size: 13px;
        }


        /* =====================================================
           SUMMARY CARDS
        ===================================================== */

        .donation-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }


        .donation-summary-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }


        .donation-summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }


        .pending-icon {
            background: #fff4d6;
            color: #9a6800;
        }


        .completed-icon {
            background: #e8f7ee;
            color: #16834b;
        }


        .donation-summary-card span {
            display: block;
            font-size: 13px;
            color: #777;
            margin-bottom: 3px;
        }


        .donation-summary-card strong {
            display: block;
            font-size: 24px;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-donations {
            text-align: center;
            padding: 70px 20px;
            color: #777;
        }


        .empty-donations i {
            font-size: 42px;
            margin-bottom: 15px;
        }


        /* =====================================================
           ALERTS
        ===================================================== */

        .admin-alert {
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }


        @media (max-width: 768px) {

            .donation-summary {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body class="admin-dashboard">


<!-- =========================================================
     ADMIN NAVBAR
========================================================= -->

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
                    $_SESSION['admin_name'] ?? 'Admin'
                ); ?>

            </span>


            <a
                href="../logout.php"
                class="admin-logout"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </div>

    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="admin-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="admin-header">

        <div>

            <h1>
                Donations
            </h1>

            <p>
                Review and manage donor payment submissions.
            </p>

        </div>


        <a
            href="../dashboard.php"
            class="admin-btn-secondary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Dashboard

        </a>

    </div>


    <!-- =====================================================
         SUCCESS MESSAGE
    ====================================================== -->

    <?php if ($successMessage !== ''): ?>

        <div class="alert alert-success admin-alert">

            <i class="fa-solid fa-circle-check me-2"></i>

            <?= htmlspecialchars($successMessage); ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if ($errorMessage !== ''): ?>

        <div class="alert alert-danger admin-alert">

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            <?= htmlspecialchars($errorMessage); ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="donation-summary">


        <!-- PENDING -->

        <div class="donation-summary-card">

            <div class="donation-summary-icon pending-icon">

                <i class="fa-solid fa-clock"></i>

            </div>

            <div>

                <span>
                    Awaiting Approval
                </span>

                <strong>
                    <?= $pendingCount; ?>
                </strong>

            </div>

        </div>


        <!-- COMPLETED -->

        <div class="donation-summary-card">

            <div class="donation-summary-icon completed-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <span>
                    Approved Donations
                </span>

                <strong>
                    <?= $completedCount; ?>
                </strong>

            </div>

        </div>


    </div>


    <!-- =====================================================
         DONATION TABLE
    ====================================================== -->

    <div class="donation-table-wrapper">


        <?php if (empty($donations)): ?>


            <div class="empty-donations">

                <i class="fa-solid fa-hand-holding-dollar"></i>

                <h3>
                    No donations yet
                </h3>

                <p>
                    Payment submissions will appear here.
                </p>

            </div>


        <?php else: ?>


            <div class="table-responsive">

                <table class="donation-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Payer
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Purpose
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Method
                            </th>

                            <th>
                                Transaction ID
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $donations as $donation
                    ): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?= (int)
                                    $donation['id'];
                                ?>

                            </td>


                            <!-- NAME -->

                            <td>

                                <div class="donor-name">

                                    <?= htmlspecialchars(
                                        $donation['donor_name']
                                    ); ?>

                                </div>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <div class="donor-email">

                                    <?= htmlspecialchars(
                                        $donation['donor_email']
                                    ); ?>

                                </div>

                            </td>


                            <!-- PHONE -->

                            <td>

                                <div class="donation-phone">

                                    <?= htmlspecialchars(
                                        $donation['donor_phone'] ?: '—'
                                    ); ?>

                                </div>

                            </td>


                            <!-- PURPOSE -->

                            <td>

                                <?= htmlspecialchars(
                                    $donation['donation_purpose']
                                ); ?>

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <div class="donation-amount">

                                    ₹<?= number_format(
                                        (float)
                                        $donation['donation_amount'],
                                        2
                                    ); ?>

                                </div>

                            </td>


                            <!-- METHOD -->

                            <td>

                                <?= htmlspecialchars(
                                    $donation['payment_method']
                                    ?: 'UPI'
                                ); ?>

                            </td>


                            <!-- TRANSACTION ID -->

                            <td>

                                <span class="transaction-id">

                                    <?= htmlspecialchars(
                                        $donation['transaction_id']
                                        ?: '—'
                                    ); ?>

                                </span>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php

                                $status =
                                    $donation['payment_status'];

                                ?>


                                <?php if ($status === 'pending'): ?>

                                    <span
                                        class="payment-status pending"
                                    >

                                        <i class="fa-solid fa-clock"></i>

                                        Pending

                                    </span>


                                <?php elseif ($status === 'completed'): ?>

                                    <span
                                        class="payment-status completed"
                                    >

                                        <i class="fa-solid fa-circle-check"></i>

                                        Completed

                                    </span>


                                <?php elseif ($status === 'failed'): ?>

                                    <span
                                        class="payment-status failed"
                                    >

                                        <i class="fa-solid fa-circle-xmark"></i>

                                        Failed

                                    </span>


                                <?php elseif ($status === 'cancelled'): ?>

                                    <span
                                        class="payment-status cancelled"
                                    >

                                        <i class="fa-solid fa-ban"></i>

                                        Cancelled

                                    </span>


                                <?php endif; ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <div class="donation-date">

                                    <?php

                                    $date =
                                        $donation[
                                            'payment_submitted_at'
                                        ]
                                        ??
                                        $donation[
                                            'created_at'
                                        ];

                                    echo date(
                                        'd M Y, h:i A',
                                        strtotime($date)
                                    );

                                    ?>

                                </div>

                            </td>


                            <!-- ACTION -->

                            <td>


                                <?php if ($status === 'pending'): ?>


                                    <div class="donation-actions">


                                        <!-- APPROVE -->

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to approve this donation?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="donation_id"
                                                value="<?= (int) $donation['id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="approve"
                                            >

                                            <button
                                                type="submit"
                                                class="donation-action-btn approve-btn"
                                            >

                                                <i class="fa-solid fa-check"></i>

                                                Approve

                                            </button>

                                        </form>


                                        <!-- REJECT -->

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to reject this donation?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="donation_id"
                                                value="<?= (int) $donation['id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="reject"
                                            >

                                            <button
                                                type="submit"
                                                class="donation-action-btn reject-btn"
                                            >

                                                <i class="fa-solid fa-xmark"></i>

                                                Reject

                                            </button>

                                        </form>


                                    </div>


                                <?php else: ?>


                                    <span class="view-only">
                                        Processed
                                    </span>


                                <?php endif; ?>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>

    </div>

</main>

<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>