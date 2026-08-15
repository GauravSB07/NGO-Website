<?php

session_start();

include "../../config/db.php";


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {

    header("Location: ../login.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Validate Image ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid image.");

}


$image_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Image
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, event_id, image_role
    FROM images
    WHERE id = ?
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $image_id
);


mysqli_stmt_execute(
    $stmt
);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


if (
    mysqli_num_rows($result) === 0
) {

    die("Image not found.");

}


$image =
    mysqli_fetch_assoc(
        $result
    );


/*
|--------------------------------------------------------------------------
| Prevent Cover Image Deletion
|--------------------------------------------------------------------------
|
| The cover image should be replaced through edit.php,
| not deleted as a gallery image.
|
|--------------------------------------------------------------------------
*/

if (
    $image['image_role'] === 'cover'
) {

    die(
        "The cover image cannot be deleted here. "
        . "Use Edit Event to replace it."
    );

}


/*
|--------------------------------------------------------------------------
| Delete Image From Database
|--------------------------------------------------------------------------
*/

$deleteSql = "
    DELETE FROM images
    WHERE id = ?
";


$deleteStmt =
    mysqli_prepare(
        $conn,
        $deleteSql
    );


mysqli_stmt_bind_param(
    $deleteStmt,
    "i",
    $image_id
);


if (
    !mysqli_stmt_execute(
        $deleteStmt
    )
) {

    die(
        "Failed to delete image."
    );

}


/*
|--------------------------------------------------------------------------
| Return To Edit Page
|--------------------------------------------------------------------------
*/

header(
    "Location: edit.php?id="
    . (int) $image['event_id']
);


exit();

?>