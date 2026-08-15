<?php

include "config/db.php";


/*
|--------------------------------------------------------------------------
| CHECK IMAGE REQUEST
|--------------------------------------------------------------------------
*/

$imageId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

$imageName = isset($_GET['name'])
    ? trim($_GET['name'])
    : '';


if ($imageId <= 0 && $imageName === '') {

    http_response_code(404);
    exit();

}


/*
|--------------------------------------------------------------------------
| FIND IMAGE BY ID
|--------------------------------------------------------------------------
*/

if ($imageId > 0) {

    $sql = "
        SELECT
            image_data,
            image_type
        FROM static_images
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        http_response_code(500);
        exit();

    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $imageId
    );


/*
|--------------------------------------------------------------------------
| FIND IMAGE BY NAME
|--------------------------------------------------------------------------
*/

} else {

    $imageName = basename($imageName);

    $sql = "
        SELECT
            image_data,
            image_type
        FROM static_images
        WHERE image_name = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        http_response_code(500);
        exit();

    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $imageName
    );

}


/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

if (!mysqli_stmt_execute($stmt)) {

    http_response_code(500);
    exit();

}


$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| IMAGE NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$result || mysqli_num_rows($result) === 0) {

    http_response_code(404);
    exit();

}


$image = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| SEND IMAGE
|--------------------------------------------------------------------------
*/

header(
    "Content-Type: " . $image['image_type']
);

header(
    "Content-Length: " . strlen($image['image_data'])
);

header(
    "Cache-Control: public, max-age=31536000"
);

echo $image['image_data'];

exit();

?>