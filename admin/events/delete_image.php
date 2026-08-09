<?php

session_start();

include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid image.");
}

$image_id = (int) $_GET['id'];


/* Get image */

$sql = "
    SELECT image_path, event_id
    FROM event_images
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $image_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Image not found.");
}

$image = mysqli_fetch_assoc($result);


/* Delete physical file */

$file = "../../uploads/events/" . $image['image_path'];

if (file_exists($file)) {
    unlink($file);
}


/* Delete database record */

$deleteSql = "
    DELETE FROM event_images
    WHERE id = ?
";

$deleteStmt = mysqli_prepare(
    $conn,
    $deleteSql
);

mysqli_stmt_bind_param(
    $deleteStmt,
    "i",
    $image_id
);

mysqli_stmt_execute($deleteStmt);


/* Return to Edit */

header(
    "Location: edit.php?id=" . $image['event_id']
);

exit();

?>