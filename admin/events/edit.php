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
   IMAGE CONFIGURATION
========================================================= */

$maxOriginalSize = 25 * 1024 * 1024;

$maxWidth = 1920;
$maxHeight = 1920;

$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];


/* =========================================================
   IMAGE PROCESSING FUNCTION
========================================================= */

function processUploadedImage(
    string $tmpPath,
    string $mimeType,
    int $originalSize,
    int $maxWidth,
    int $maxHeight
): array {

    if (!extension_loaded('gd')) {

        throw new Exception(
            "PHP GD extension is not enabled."
        );

    }


    if ($originalSize > 25 * 1024 * 1024) {

        throw new Exception(
            "Image is larger than the allowed 25 MB limit."
        );

    }


    $imageInfo = @getimagesize($tmpPath);

    if ($imageInfo === false) {

        throw new Exception(
            "The uploaded file is not a valid image."
        );

    }


    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];


    /* Create source */

    switch ($mimeType) {

        case 'image/jpeg':

            $source =
                @imagecreatefromjpeg($tmpPath);

            break;


        case 'image/png':

            $source =
                @imagecreatefrompng($tmpPath);

            break;


        case 'image/webp':

            if (!function_exists('imagecreatefromwebp')) {

                throw new Exception(
                    "WebP support is not enabled."
                );

            }

            $source =
                @imagecreatefromwebp($tmpPath);

            break;


        default:

            throw new Exception(
                "Unsupported image format."
            );

    }


    if (!$source) {

        throw new Exception(
            "Unable to process image."
        );

    }


    /* Calculate dimensions */

    $scale = min(
        $maxWidth / $originalWidth,
        $maxHeight / $originalHeight,
        1
    );


    $newWidth = max(
        1,
        (int) round(
            $originalWidth * $scale
        )
    );


    $newHeight = max(
        1,
        (int) round(
            $originalHeight * $scale
        )
    );


    /* Destination */

    $destination =
        imagecreatetruecolor(
            $newWidth,
            $newHeight
        );


    if (!$destination) {

        imagedestroy($source);

        throw new Exception(
            "Unable to create optimized image."
        );

    }


    /* Transparency */

    imagealphablending(
        $destination,
        false
    );

    imagesavealpha(
        $destination,
        true
    );


    $transparent =
        imagecolorallocatealpha(
            $destination,
            0,
            0,
            0,
            127
        );


    imagefilledrectangle(
        $destination,
        0,
        0,
        $newWidth,
        $newHeight,
        $transparent
    );


    /* Resize */

    imagecopyresampled(
        $destination,
        $source,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    );


    /* Output */

    ob_start();


    if (function_exists('imagewebp')) {

        imagewebp(
            $destination,
            null,
            82
        );

        $storedMime = "image/webp";

    } else {

        imagejpeg(
            $destination,
            null,
            82
        );

        $storedMime = "image/jpeg";

    }


    $data =
        ob_get_clean();


    imagedestroy($source);
    imagedestroy($destination);


    if (
        $data === false ||
        $data === ''
    ) {

        throw new Exception(
            "Failed to optimize image."
        );

    }


    return [
        'data' => $data,
        'mime' => $storedMime,
        'size' => strlen($data)
    ];

}


/* =========================================================
   GET EVENT
========================================================= */

$sql = "
    SELECT *
    FROM events
    WHERE id = ?
";

$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $event_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result(
        $stmt
    );


if (
    mysqli_num_rows($result) == 0
) {

    die("Event not found.");

}


$event =
    mysqli_fetch_assoc(
        $result
    );


/* =========================================================
   GET CATEGORIES
========================================================= */

$categories =
    mysqli_query(
        $conn,
        "SELECT id, name
         FROM categories
         ORDER BY name ASC"
    );


/* =========================================================
   GET CUSTOM DETAILS
========================================================= */

$detailStmt =
    mysqli_prepare(
        $conn,
        "
        SELECT id, field_name, field_value
        FROM event_details
        WHERE event_id = ?
        ORDER BY id ASC
        "
    );

mysqli_stmt_bind_param(
    $detailStmt,
    "i",
    $event_id
);

mysqli_stmt_execute(
    $detailStmt
);

$details =
    mysqli_stmt_get_result(
        $detailStmt
    );


/* =========================================================
   UPDATE EVENT
========================================================= */

if (isset($_POST['update_event'])) {

    $category_id =
        (int) ($_POST['category_id'] ?? 0);

    $title =
        trim(
            $_POST['title'] ?? ''
        );

    $event_date =
        !empty($_POST['event_date'])
        ? $_POST['event_date']
        : null;

    $location =
        trim(
            $_POST['location'] ?? ''
        );

    $description =
        trim(
            $_POST['description'] ?? ''
        );

    $status =
        $_POST['status'] ?? 'Active';


    /* Validation */

    if (
        empty($category_id) ||
        empty($title) ||
        empty($location) ||
        empty($description)
    ) {

        $error =
            "Please fill all required fields.";

    }


    if (!isset($error)) {

        mysqli_begin_transaction($conn);

        try {


            /* =================================================
               UPDATE EVENT INFORMATION
            ================================================= */

            $updateSql = "
                UPDATE events
                SET
                    category_id = ?,
                    title = ?,
                    event_date = ?,
                    location = ?,
                    description = ?,
                    status = ?
                WHERE id = ?
            ";


            $updateStmt =
                mysqli_prepare(
                    $conn,
                    $updateSql
                );


            mysqli_stmt_bind_param(
                $updateStmt,
                "isssssi",
                $category_id,
                $title,
                $event_date,
                $location,
                $description,
                $status,
                $event_id
            );


            if (
                !mysqli_stmt_execute(
                    $updateStmt
                )
            ) {

                throw new Exception(
                    "Failed to update event."
                );

            }


            /* =================================================
               REPLACE COVER IMAGE
            ================================================= */

            if (
                isset($_FILES['cover_image']) &&
                $_FILES['cover_image']['error']
                    === UPLOAD_ERR_OK
            ) {


                $tmpPath =
                    $_FILES['cover_image']['tmp_name'];


                $originalName =
                    basename(
                        $_FILES['cover_image']['name']
                    );


                $originalSize =
                    (int)
                    $_FILES['cover_image']['size'];


                /* Detect MIME */

                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                $mimeType =
                    finfo_file(
                        $finfo,
                        $tmpPath
                    );


                finfo_close(
                    $finfo
                );


                if (
                    !in_array(
                        $mimeType,
                        $allowedMimeTypes,
                        true
                    )
                ) {

                    throw new Exception(
                        "Invalid cover image format."
                    );

                }


                /* Process */

                $processed =
                    processUploadedImage(
                        $tmpPath,
                        $mimeType,
                        $originalSize,
                        $maxWidth,
                        $maxHeight
                    );


                $imageData =
                    $processed['data'];

                $storedMime =
                    $processed['mime'];

                $storedSize =
                    $processed['size'];


                $imageHash =
                    hash(
                        'sha256',
                        $imageData
                    );


                /* Check duplicate */

                $duplicateStmt =
                    mysqli_prepare(
                        $conn,
                        "
                        SELECT id
                        FROM images
                        WHERE image_hash = ?
                        AND id != COALESCE(
                            (
                                SELECT id
                                FROM images
                                WHERE event_id = ?
                                AND image_role = 'cover'
                                LIMIT 1
                            ),
                            0
                        )
                        LIMIT 1
                        "
                    );


                mysqli_stmt_bind_param(
                    $duplicateStmt,
                    "si",
                    $imageHash,
                    $event_id
                );


                mysqli_stmt_execute(
                    $duplicateStmt
                );


                $duplicateResult =
                    mysqli_stmt_get_result(
                        $duplicateStmt
                    );


                if (
                    mysqli_num_rows(
                        $duplicateResult
                    ) > 0
                ) {

                    throw new Exception(
                        "This image already exists in the database."
                    );

                }


                /* Find existing cover */

                $oldCoverStmt =
                    mysqli_prepare(
                        $conn,
                        "
                        SELECT id
                        FROM images
                        WHERE event_id = ?
                        AND image_role = 'cover'
                        LIMIT 1
                        "
                    );


                mysqli_stmt_bind_param(
                    $oldCoverStmt,
                    "i",
                    $event_id
                );


                mysqli_stmt_execute(
                    $oldCoverStmt
                );


                $oldCoverResult =
                    mysqli_stmt_get_result(
                        $oldCoverStmt
                    );


                if (
                    mysqli_num_rows(
                        $oldCoverResult
                    ) > 0
                ) {


                    $oldCover =
                        mysqli_fetch_assoc(
                            $oldCoverResult
                        );


                    /* Update existing cover */

                    $coverUpdate =
                        mysqli_prepare(
                            $conn,
                            "
                            UPDATE images
                            SET
                                image_data = ?,
                                image_name = ?,
                                image_type = ?,
                                image_size = ?,
                                image_hash = ?
                            WHERE id = ?
                            "
                        );


                    $nullData = null;


                    mysqli_stmt_bind_param(
                        $coverUpdate,
                        "bsssii",
                        $nullData,
                        $originalName,
                        $storedMime,
                        $storedSize,
                        $imageHash,
                        $oldCover['id']
                    );


                    mysqli_stmt_send_long_data(
                        $coverUpdate,
                        0,
                        $imageData
                    );


                    if (
                        !mysqli_stmt_execute(
                            $coverUpdate
                        )
                    ) {

                        throw new Exception(
                            "Failed to update cover image."
                        );

                    }

                } else {


                    /* Insert new cover */

                    $imageRole =
                        "cover";

                    $imageCategory =
                        "event";

                    $nullData = null;


                    $coverInsert =
                        mysqli_prepare(
                            $conn,
                            "
                            INSERT INTO images
                            (
                                event_id,
                                image_data,
                                image_name,
                                image_type,
                                image_size,
                                image_hash,
                                image_category,
                                image_role
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            "
                        );


                    mysqli_stmt_bind_param(
                        $coverInsert,
                        "ibssisss",
                        $event_id,
                        $nullData,
                        $originalName,
                        $storedMime,
                        $storedSize,
                        $imageHash,
                        $imageCategory,
                        $imageRole
                    );


                    mysqli_stmt_send_long_data(
                        $coverInsert,
                        1,
                        $imageData
                    );


                    if (
                        !mysqli_stmt_execute(
                            $coverInsert
                        )
                    ) {

                        throw new Exception(
                            "Failed to add cover image."
                        );

                    }

                }

            }


            /* =================================================
               ADD NEW GALLERY IMAGES
            ================================================= */

            if (
                isset($_FILES['gallery_images']) &&
                isset($_FILES['gallery_images']['name']) &&
                is_array(
                    $_FILES['gallery_images']['name']
                )
            ) {


                foreach (
                    $_FILES['gallery_images']['name']
                    as $key => $originalName
                ) {


                    if (
                        empty($originalName) ||
                        $_FILES['gallery_images']['error'][$key]
                            !== UPLOAD_ERR_OK
                    ) {

                        continue;

                    }


                    $tmpPath =
                        $_FILES['gallery_images']['tmp_name'][$key];


                    $originalSize =
                        (int)
                        $_FILES['gallery_images']['size'][$key];


                    $finfo =
                        finfo_open(
                            FILEINFO_MIME_TYPE
                        );


                    $mimeType =
                        finfo_file(
                            $finfo,
                            $tmpPath
                        );


                    finfo_close(
                        $finfo
                    );


                    if (
                        !in_array(
                            $mimeType,
                            $allowedMimeTypes,
                            true
                        )
                    ) {

                        continue;

                    }


                    $processed =
                        processUploadedImage(
                            $tmpPath,
                            $mimeType,
                            $originalSize,
                            $maxWidth,
                            $maxHeight
                        );


                    $imageData =
                        $processed['data'];

                    $storedMime =
                        $processed['mime'];

                    $storedSize =
                        $processed['size'];


                    $imageHash =
                        hash(
                            'sha256',
                            $imageData
                        );


                    /* Duplicate check */

                    $duplicateStmt =
                        mysqli_prepare(
                            $conn,
                            "
                            SELECT id
                            FROM images
                            WHERE image_hash = ?
                            LIMIT 1
                            "
                        );


                    mysqli_stmt_bind_param(
                        $duplicateStmt,
                        "s",
                        $imageHash
                    );


                    mysqli_stmt_execute(
                        $duplicateStmt
                    );


                    $duplicateResult =
                        mysqli_stmt_get_result(
                            $duplicateStmt
                        );


                    if (
                        mysqli_num_rows(
                            $duplicateResult
                        ) > 0
                    ) {

                        continue;

                    }


                    /* Insert gallery image */

                    $imageRole =
                        "gallery";

                    $imageCategory =
                        "event";

                    $nullData = null;


                    $galleryStmt =
                        mysqli_prepare(
                            $conn,
                            "
                            INSERT INTO images
                            (
                                event_id,
                                image_data,
                                image_name,
                                image_type,
                                image_size,
                                image_hash,
                                image_category,
                                image_role
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            "
                        );


                    mysqli_stmt_bind_param(
                        $galleryStmt,
                        "ibssisss",
                        $event_id,
                        $nullData,
                        $originalName,
                        $storedMime,
                        $storedSize,
                        $imageHash,
                        $imageCategory,
                        $imageRole
                    );


                    mysqli_stmt_send_long_data(
                        $galleryStmt,
                        1,
                        $imageData
                    );


                    if (
                        !mysqli_stmt_execute(
                            $galleryStmt
                        )
                    ) {

                        throw new Exception(
                            "Failed to save gallery image."
                        );

                    }

                }

            }


            /* =================================================
               UPDATE CUSTOM DETAILS
            ================================================= */

            $deleteDetails =
                mysqli_prepare(
                    $conn,
                    "
                    DELETE FROM event_details
                    WHERE event_id = ?
                    "
                );


            mysqli_stmt_bind_param(
                $deleteDetails,
                "i",
                $event_id
            );


            mysqli_stmt_execute(
                $deleteDetails
            );


            if (
                isset($_POST['field_name']) &&
                isset($_POST['field_value'])
            ) {


                foreach (
                    $_POST['field_name']
                    as $key => $fieldName
                ) {


                    $fieldName =
                        trim($fieldName);


                    $fieldValue =
                        trim(
                            $_POST['field_value'][$key]
                            ?? ''
                        );


                    if (
                        empty($fieldName) ||
                        empty($fieldValue)
                    ) {

                        continue;

                    }


                    $detailStmt =
                        mysqli_prepare(
                            $conn,
                            "
                            INSERT INTO event_details
                            (
                                event_id,
                                field_name,
                                field_value
                            )
                            VALUES (?, ?, ?)
                            "
                        );


                    mysqli_stmt_bind_param(
                        $detailStmt,
                        "iss",
                        $event_id,
                        $fieldName,
                        $fieldValue
                    );


                    mysqli_stmt_execute(
                        $detailStmt
                    );

                }

            }


            /* =================================================
               COMMIT
            ================================================= */

            mysqli_commit($conn);


            header(
                "Location: edit.php?id="
                . $event_id
                . "&updated=1"
            );

            exit();


        } catch (Exception $e) {


            if (mysqli_ping($conn)) {

                mysqli_rollback($conn);

            }


            $error =
                $e->getMessage();

        }

    }

}


/* =========================================================
   GET CURRENT COVER
========================================================= */

$coverStmt =
    mysqli_prepare(
        $conn,
        "
        SELECT id, image_name, image_type
        FROM images
        WHERE event_id = ?
        AND image_role = 'cover'
        LIMIT 1
        "
    );

mysqli_stmt_bind_param(
    $coverStmt,
    "i",
    $event_id
);

mysqli_stmt_execute(
    $coverStmt
);

$coverResult =
    mysqli_stmt_get_result(
        $coverStmt
    );

$currentCover =
    mysqli_num_rows($coverResult) > 0
    ? mysqli_fetch_assoc($coverResult)
    : null;


/* =========================================================
   GET GALLERY
========================================================= */

$galleryStmt =
    mysqli_prepare(
        $conn,
        "
        SELECT id, image_name, image_type
        FROM images
        WHERE event_id = ?
        AND image_role = 'gallery'
        ORDER BY id ASC
        "
    );

mysqli_stmt_bind_param(
    $galleryStmt,
    "i",
    $event_id
);

mysqli_stmt_execute(
    $galleryStmt
);

$gallery =
    mysqli_stmt_get_result(
        $galleryStmt
    );

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
        Edit Event | Admin
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <link
        rel="stylesheet"
        href="../../css/admin/admin.css"
    >

</head>


<body class="admin-dashboard">


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
                    $_SESSION['admin_name']
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


<main class="admin-container">


    <div class="admin-header">

        <div>

            <h1>
                Edit Event
            </h1>

            <p>
                Update event information and images.
            </p>

        </div>


        <a
            href="index.php"
            class="admin-btn-secondary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back

        </a>

    </div>


    <?php if (isset($error)) { ?>

        <div class="admin-alert">

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <?php if (isset($_GET['updated'])) { ?>

        <div class="alert alert-success">

            Event updated successfully.

        </div>

    <?php } ?>


    <div class="admin-form-card">


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- CATEGORY -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Category

                    <span class="admin-required">*</span>

                </label>


                <select
                    name="category_id"
                    class="form-select"
                    required
                >

                    <?php while (
                        $category =
                        mysqli_fetch_assoc(
                            $categories
                        )
                    ) { ?>

                        <option
                            value="<?= $category['id']; ?>"
                            <?= $category['id']
                                == $event['category_id']
                                ? 'selected'
                                : ''; ?>
                        >

                            <?= htmlspecialchars(
                                $category['name']
                            ); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- TITLE -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Event Name

                    <span class="admin-required">*</span>

                </label>


                <input
                    type="text"
                    name="title"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $event['title']
                    ); ?>"
                    required
                >

            </div>


            <!-- DATE + LOCATION -->

            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="admin-form-label">

                        Event Date

                    </label>


                    <input
                        type="date"
                        name="event_date"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $event['event_date'] ?? ''
                        ); ?>"
                    >

                </div>


                <div class="col-md-6 mb-4">

                    <label class="admin-form-label">

                        Location

                        <span class="admin-required">*</span>

                    </label>


                    <input
                        type="text"
                        name="location"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $event['location']
                        ); ?>"
                        required
                    >

                </div>

            </div>


            <!-- DESCRIPTION -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Description

                    <span class="admin-required">*</span>

                </label>


                <textarea
                    name="description"
                    class="form-control"
                    rows="7"
                    required
                ><?= htmlspecialchars(
                    $event['description']
                ); ?></textarea>

            </div>


            <!-- CURRENT COVER -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Current Cover Image

                </label>


                <?php if ($currentCover) { ?>

                    <div class="admin-current-image">

                        <img
                            src="../../image.php?id=<?= (int) $currentCover['id']; ?>"
                            alt="Current cover image"
                        >

                    </div>

                <?php } else { ?>

                    <p class="admin-empty">

                        No cover image found.

                    </p>

                <?php } ?>

            </div>


            <!-- REPLACE COVER -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Replace Cover Image

                </label>


                <input
                    type="file"
                    name="cover_image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >


                <div class="admin-image-note">

                    Leave empty to keep the current image.
                    Images are automatically resized and compressed.

                </div>

            </div>


            <!-- GALLERY -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Current Gallery

                </label>


                <div class="row">


                    <?php if (
                        mysqli_num_rows($gallery) > 0
                    ) { ?>


                        <?php while (
                            $image =
                            mysqli_fetch_assoc(
                                $gallery
                            )
                        ) { ?>


                            <div
                                class="col-6 col-md-3 mb-4"
                            >

                                <div class="admin-gallery-item">


                                    <img
                                        src="../../image.php?id=<?= (int) $image['id']; ?>"
                                        alt="Gallery image"
                                    >


                                    <a
                                        href="delete_image.php?id=<?= (int) $image['id']; ?>"
                                        class="admin-delete-btn"
                                        onclick="return confirm('Delete this image?');"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                        Delete

                                    </a>


                                </div>

                            </div>


                        <?php } ?>


                    <?php } else { ?>

                        <div class="col-12">

                            <p class="admin-empty">

                                No gallery images.

                            </p>

                        </div>

                    <?php } ?>


                </div>

            </div>


            <!-- ADD GALLERY -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Add More Gallery Images

                </label>


                <input
                    type="file"
                    name="gallery_images[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                >


                <div class="admin-image-note">

                    Multiple images can be selected.
                    Images are automatically resized and compressed.

                </div>

            </div>


            <hr class="my-4">


            <!-- ADDITIONAL INFORMATION -->

            <div class="mb-4">

                <div class="admin-section-header">

                    <div>

                        <h3>
                            Additional Information
                        </h3>

                        <p>
                            Add event-specific information.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="admin-btn-secondary"
                        id="addDetail"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add Another Field

                    </button>

                </div>


                <div id="detailsContainer">


                    <?php while (
                        $detail =
                        mysqli_fetch_assoc(
                            $details
                        )
                    ) { ?>


                        <div class="admin-detail-row">

                            <div class="row align-items-center">


                                <div class="col-md-5 mb-2">

                                    <input
                                        type="text"
                                        name="field_name[]"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $detail['field_name']
                                        ); ?>"
                                        placeholder="Field name"
                                    >

                                </div>


                                <div class="col-md-6 mb-2">

                                    <input
                                        type="text"
                                        name="field_value[]"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $detail['field_value']
                                        ); ?>"
                                        placeholder="Field value"
                                    >

                                </div>


                                <div class="col-md-1">

                                    <button
                                        type="button"
                                        class="admin-delete-detail remove-detail"
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                    </button>

                                </div>


                            </div>

                        </div>


                    <?php } ?>


                </div>

            </div>


            <!-- STATUS -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Status

                </label>


                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="Active"
                        <?= $event['status'] == 'Active'
                            ? 'selected'
                            : ''; ?>
                    >

                        Active

                    </option>


                    <option
                        value="Inactive"
                        <?= $event['status'] == 'Inactive'
                            ? 'selected'
                            : ''; ?>
                    >

                        Inactive

                    </option>

                </select>

            </div>


            <!-- SAVE -->

            <div class="admin-form-actions">

                <button
                    type="submit"
                    name="update_event"
                    class="admin-btn-primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Changes

                </button>

            </div>


        </form>

    </div>

</main>


<script>

const addDetailButton =
    document.getElementById("addDetail");

const detailsContainer =
    document.getElementById("detailsContainer");


addDetailButton.addEventListener(
    "click",
    function () {

        const row =
            document.createElement("div");

        row.className =
            "admin-detail-row";


        row.innerHTML = `

            <div class="row align-items-center">

                <div class="col-md-5 mb-2">

                    <input
                        type="text"
                        name="field_name[]"
                        class="form-control"
                        placeholder="Field name"
                    >

                </div>

                <div class="col-md-6 mb-2">

                    <input
                        type="text"
                        name="field_value[]"
                        class="form-control"
                        placeholder="Field value"
                    >

                </div>

                <div class="col-md-1">

                    <button
                        type="button"
                        class="admin-delete-detail remove-detail"
                    >

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                </div>

            </div>

        `;


        detailsContainer.appendChild(row);


        row
            .querySelector(".remove-detail")
            .addEventListener(
                "click",
                function () {

                    row.remove();

                }
            );

    }
);


document
    .querySelectorAll(".remove-detail")
    .forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                button
                    .closest(".admin-detail-row")
                    .remove();

            }
        );

    });

</script>


</body>

</html>