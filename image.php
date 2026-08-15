<?php

include "config/db.php";


/* =========================================================
   VALIDATE IMAGE ID
========================================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    http_response_code(404);

    exit();

}


$image_id = (int) $_GET['id'];


/* =========================================================
   GET IMAGE
========================================================= */

$sql = "
    SELECT
        image_data,
        image_type
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

    http_response_code(404);

    exit();

}


$image =
    mysqli_fetch_assoc($result);


/* =========================================================
   SEND IMAGE TO BROWSER
========================================================= */

header(
    "Content-Type: " .
    $image['image_type']
);


header(
    "Content-Length: " .
    strlen($image['image_data'])
);


echo $image['image_data'];

exit();

?>