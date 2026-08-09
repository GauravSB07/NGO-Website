<?php

session_start();

include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid event.");
}

$event_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Event
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT cover_image
    FROM events
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $event_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Event not found.");
}

$event = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| Get Gallery Images
|--------------------------------------------------------------------------
*/

$gallerySql = "
    SELECT image_path
    FROM event_images
    WHERE event_id = ?
";

$galleryStmt = mysqli_prepare(
    $conn,
    $gallerySql
);

mysqli_stmt_bind_param(
    $galleryStmt,
    "i",
    $event_id
);

mysqli_stmt_execute($galleryStmt);

$galleryResult =
    mysqli_stmt_get_result($galleryStmt);


/*
|--------------------------------------------------------------------------
| Delete Physical Cover
|--------------------------------------------------------------------------
*/

$uploadDir = "../../uploads/events/";

if (
    !empty($event['cover_image']) &&
    file_exists(
        $uploadDir . $event['cover_image']
    )
) {

    unlink(
        $uploadDir . $event['cover_image']
    );

}


/*
|--------------------------------------------------------------------------
| Delete Physical Gallery Images
|--------------------------------------------------------------------------
*/

while (
    $image =
    mysqli_fetch_assoc($galleryResult)
) {

    $imageFile =
        $uploadDir . $image['image_path'];

    if (file_exists($imageFile)) {

        unlink($imageFile);

    }

}


/*
|--------------------------------------------------------------------------
| Delete Event
|--------------------------------------------------------------------------
|
| Because event_images and event_details have
| ON DELETE CASCADE, their database records
| will automatically be deleted.
|
*/

$deleteSql = "
    DELETE FROM events
    WHERE id = ?
";

$deleteStmt = mysqli_prepare(
    $conn,
    $deleteSql
);

mysqli_stmt_bind_param(
    $deleteStmt,
    "i",
    $event_id
);

mysqli_stmt_execute($deleteStmt);


/*
|--------------------------------------------------------------------------
| Return
|--------------------------------------------------------------------------
*/

header(
    "Location: index.php?deleted=1"
);

exit();

?>