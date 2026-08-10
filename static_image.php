<?php

include "config/db.php";


/*
|--------------------------------------------------------------------------
| GET IMAGE NAME
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['name']) ||
    trim($_GET['name']) === ''
) {

    http_response_code(404);
    exit();

}


$imageName =
    basename(
        trim($_GET['name'])
    );


/*
|--------------------------------------------------------------------------
| FIND IMAGE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        image_data,
        image_type
    FROM static_images
    WHERE image_name = ?
    LIMIT 1
";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!$stmt) {

    http_response_code(500);
    exit();

}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $imageName
);


mysqli_stmt_execute(
    $stmt
);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


/*
|--------------------------------------------------------------------------
| IMAGE NOT FOUND
|--------------------------------------------------------------------------
*/

if (
    mysqli_num_rows($result) === 0
) {

    http_response_code(404);
    exit();

}


$image =
    mysqli_fetch_assoc(
        $result
    );


/*
|--------------------------------------------------------------------------
| SEND IMAGE
|--------------------------------------------------------------------------
*/

header(
    "Content-Type: "
    . $image['image_type']
);


header(
    "Cache-Control: public, max-age=31536000"
);


echo $image['image_data'];

exit();

?>