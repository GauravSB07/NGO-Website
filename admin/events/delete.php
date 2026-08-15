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
   VALIDATE EVENT ID
========================================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid event.");

}

$event_id = (int) $_GET['id'];


/* =========================================================
   CHECK EVENT EXISTS
========================================================= */

$checkStmt =
    mysqli_prepare(
        $conn,
        "
        SELECT id, title
        FROM events
        WHERE id = ?
        "
    );


mysqli_stmt_bind_param(
    $checkStmt,
    "i",
    $event_id
);


mysqli_stmt_execute(
    $checkStmt
);


$result =
    mysqli_stmt_get_result(
        $checkStmt
    );


if (
    mysqli_num_rows($result) === 0
) {

    die("Event not found.");

}


$event =
    mysqli_fetch_assoc(
        $result
    );


/* =========================================================
   DELETE EVENT
========================================================= */

try {


    mysqli_begin_transaction(
        $conn
    );


    /* =====================================================
       DELETE IMAGES
    ====================================================== */

    $imageStmt =
        mysqli_prepare(
            $conn,
            "
            DELETE FROM images
            WHERE event_id = ?
            "
        );


    mysqli_stmt_bind_param(
        $imageStmt,
        "i",
        $event_id
    );


    if (
        !mysqli_stmt_execute(
            $imageStmt
        )
    ) {

        throw new Exception(
            "Failed to delete event images."
        );

    }


    /* =====================================================
       DELETE CUSTOM DETAILS
    ====================================================== */

    $detailStmt =
        mysqli_prepare(
            $conn,
            "
            DELETE FROM event_details
            WHERE event_id = ?
            "
        );


    mysqli_stmt_bind_param(
        $detailStmt,
        "i",
        $event_id
    );


    if (
        !mysqli_stmt_execute(
            $detailStmt
        )
    ) {

        throw new Exception(
            "Failed to delete event details."
        );

    }


    /* =====================================================
       DELETE EVENT
    ====================================================== */

    $eventStmt =
        mysqli_prepare(
            $conn,
            "
            DELETE FROM events
            WHERE id = ?
            "
        );


    mysqli_stmt_bind_param(
        $eventStmt,
        "i",
        $event_id
    );


    if (
        !mysqli_stmt_execute(
            $eventStmt
        )
    ) {

        throw new Exception(
            "Failed to delete event."
        );

    }


    /* =====================================================
       COMMIT
    ====================================================== */

    mysqli_commit(
        $conn
    );


    header(
        "Location: index.php?deleted=1"
    );

    exit();


} catch (Exception $e) {


    if (mysqli_ping($conn)) {

        mysqli_rollback(
            $conn
        );

    }


    die(
        "Unable to delete event: "
        . htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>