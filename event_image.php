<?php

include "config/db.php";


/*
|--------------------------------------------------------------------------
| GET IMAGE ID
|--------------------------------------------------------------------------
*/

$imageId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($imageId <= 0) {

    http_response_code(404);
    exit();

}


/*
|--------------------------------------------------------------------------
| GET IMAGE FROM images TABLE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        image_data,
        image_type
    FROM images
    WHERE id = ?
    LIMIT 1
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    http_response_code(500);
    exit();

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $imageId
);


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

if (
    !$result ||
    mysqli_num_rows($result) === 0
) {

    http_response_code(404);
    exit();

}


$image = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| VALIDATE IMAGE TYPE
|--------------------------------------------------------------------------
*/

$allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];


if (
    !in_array(
        $image['image_type'],
        $allowedTypes,
        true
    )
) {

    http_response_code(415);
    exit();

}


/*
|--------------------------------------------------------------------------
| SEND IMAGE
|--------------------------------------------------------------------------
*/

header(
    "Content-Type: " . $image['image_type']
);


header(
    "Content-Length: " .
    strlen($image['image_data'])
);


header(
    "Cache-Control: public, max-age=31536000"
);


echo $image['image_data'];

exit();

?>