<?php

session_start();

include "../../config/db.php";


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {

    header("Location: ../login.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| IMAGE CONFIGURATION
|--------------------------------------------------------------------------
*/

$maxOriginalSize = 25 * 1024 * 1024; // 25 MB

$maxWidth  = 1920;
$maxHeight = 1920;

$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];


/*
|--------------------------------------------------------------------------
| IMAGE PROCESSING FUNCTION
|--------------------------------------------------------------------------
|
| Takes the uploaded image and:
|
| 1. Validates it
| 2. Resizes it if necessary
| 3. Compresses it
| 4. Returns optimized binary data
|
|--------------------------------------------------------------------------
*/

function processUploadedImage(
    string $tmpPath,
    string $originalMime,
    int $originalSize,
    int $maxWidth,
    int $maxHeight
): array {


    /*
    |--------------------------------------------------------------------------
    | Check Original File Size
    |--------------------------------------------------------------------------
    */

    if ($originalSize > 25 * 1024 * 1024) {

        throw new Exception(
            "Image '{$originalSize}' is larger than the allowed 25 MB upload limit."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Get Image Information
    |--------------------------------------------------------------------------
    */

    $imageInfo = @getimagesize($tmpPath);

    if ($imageInfo === false) {

        throw new Exception(
            "The uploaded file is not a valid image."
        );

    }


    $originalWidth  = $imageInfo[0];
    $originalHeight = $imageInfo[1];


    /*
    |--------------------------------------------------------------------------
    | Create Source Image
    |--------------------------------------------------------------------------
    */

    switch ($originalMime) {

        case 'image/jpeg':

            $source = @imagecreatefromjpeg($tmpPath);

            break;


        case 'image/png':

            $source = @imagecreatefrompng($tmpPath);

            break;


        case 'image/webp':

            if (!function_exists('imagecreatefromwebp')) {

                throw new Exception(
                    "Your PHP installation does not support WebP images."
                );

            }

            $source = @imagecreatefromwebp($tmpPath);

            break;


        default:

            throw new Exception(
                "Unsupported image format."
            );

    }


    if (!$source) {

        throw new Exception(
            "Unable to process the uploaded image."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Calculate New Dimensions
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Create Destination Image
    |--------------------------------------------------------------------------
    */

    $destination = imagecreatetruecolor(
        $newWidth,
        $newHeight
    );


    if (!$destination) {

        imagedestroy($source);

        throw new Exception(
            "Unable to create optimized image."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Preserve Transparency
    |--------------------------------------------------------------------------
    */

    imagealphablending(
        $destination,
        false
    );

    imagesavealpha(
        $destination,
        true
    );


    /*
    |--------------------------------------------------------------------------
    | Transparent Background
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Output Buffer
    |--------------------------------------------------------------------------
    */

    ob_start();


    /*
    |--------------------------------------------------------------------------
    | Prefer WebP
    |--------------------------------------------------------------------------
    */

    if (function_exists('imagewebp')) {

        imagewebp(
            $destination,
            null,
            82
        );

        $optimizedMime =
            'image/webp';

    }

    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    else {

        /*
         * JPEG source → JPEG
         */

        if ($originalMime === 'image/jpeg') {

            imagejpeg(
                $destination,
                null,
                82
            );

            $optimizedMime =
                'image/jpeg';

        }

        /*
         * PNG source → PNG
         */

        else {

            imagepng(
                $destination,
                null,
                6
            );

            $optimizedMime =
                'image/png';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Get Optimized Binary
    |--------------------------------------------------------------------------
    */

    $optimizedData =
        ob_get_clean();


    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */

    imagedestroy($source);

    imagedestroy($destination);


    if (
        $optimizedData === false ||
        $optimizedData === ''
    ) {

        throw new Exception(
            "Failed to optimize the uploaded image."
        );

    }


    return [
        'data' => $optimizedData,
        'mime' => $optimizedMime,
        'width' => $newWidth,
        'height' => $newHeight,
        'size' => strlen($optimizedData)
    ];

}


/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryQuery = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$categories =
    mysqli_query(
        $conn,
        $categoryQuery
    );


/*
|--------------------------------------------------------------------------
| ADD EVENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_event'])) {


    $category_id =
        intval(
            $_POST['category_id'] ?? 0
        );


    $title =
        trim(
            $_POST['title'] ?? ''
        );


    /*
     * Event date is optional.
     */

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


    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($category_id) ||
        empty($title) ||
        empty($location) ||
        empty($description)
    ) {

        $error =
            "Please fill all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | COVER IMAGE REQUIRED
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {

        if (
            !isset($_FILES['cover_image']) ||
            $_FILES['cover_image']['error']
            !== UPLOAD_ERR_OK
        ) {

            $error =
                "Please select a cover image.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | COVER IMAGE VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {


        $coverTmpName =
            $_FILES['cover_image']['tmp_name'];


        $coverOriginalName =
            basename(
                $_FILES['cover_image']['name']
            );


        $coverOriginalSize =
            (int)
            $_FILES['cover_image']['size'];


        /*
        |--------------------------------------------------------------------------
        | Check Size
        |--------------------------------------------------------------------------
        */

        if (
            $coverOriginalSize >
            $maxOriginalSize
        ) {

            $error =
                "Cover image is too large. "
                . "Maximum allowed size is 25 MB.";

        }


        /*
        |--------------------------------------------------------------------------
        | Detect MIME
        |--------------------------------------------------------------------------
        */

        if (!isset($error)) {

            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );


            $coverMimeType =
                finfo_file(
                    $finfo,
                    $coverTmpName
                );


            finfo_close($finfo);


            if (
                !in_array(
                    $coverMimeType,
                    $allowedMimeTypes,
                    true
                )
            ) {

                $error =
                    "Invalid cover image format. "
                    . "Only JPG, PNG and WebP images are allowed.";

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Check Actual Image
        |--------------------------------------------------------------------------
        */

        if (!isset($error)) {

            if (
                @getimagesize(
                    $coverTmpName
                ) === false
            ) {

                $error =
                    "The selected cover file is not a valid image.";

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | DATABASE TRANSACTION
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {


        mysqli_begin_transaction(
            $conn
        );


        try {


            /*
            |--------------------------------------------------------------------------
            | INSERT EVENT
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO events
                (
                    category_id,
                    title,
                    event_date,
                    location,
                    description
                )
                VALUES (?, ?, ?, ?, ?)
            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    "Failed to prepare event query."
                );

            }


            mysqli_stmt_bind_param(
                $stmt,
                "issss",
                $category_id,
                $title,
                $event_date,
                $location,
                $description
            );


            if (
                !mysqli_stmt_execute(
                    $stmt
                )
            ) {

                throw new Exception(
                    "Failed to create event: "
                    . mysqli_stmt_error($stmt)
                );

            }


            /*
            |--------------------------------------------------------------------------
            | EVENT ID
            |--------------------------------------------------------------------------
            */

            $event_id =
                mysqli_insert_id(
                    $conn
                );


            /*
            |--------------------------------------------------------------------------
            | PROCESS COVER IMAGE
            |--------------------------------------------------------------------------
            */

            $coverImage =
                processUploadedImage(
                    $coverTmpName,
                    $coverMimeType,
                    $coverOriginalSize,
                    $maxWidth,
                    $maxHeight
                );


            $coverImageData =
                $coverImage['data'];


            $coverStoredMime =
                $coverImage['mime'];


            $coverStoredSize =
                $coverImage['size'];


            /*
            |--------------------------------------------------------------------------
            | HASH OPTIMIZED IMAGE
            |--------------------------------------------------------------------------
            */

            $coverHash =
                hash(
                    'sha256',
                    $coverImageData
                );


            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE
            |--------------------------------------------------------------------------
            */

            $duplicateSql = "
                SELECT id
                FROM images
                WHERE image_hash = ?
                LIMIT 1
            ";


            $duplicateStmt =
                mysqli_prepare(
                    $conn,
                    $duplicateSql
                );


            mysqli_stmt_bind_param(
                $duplicateStmt,
                "s",
                $coverHash
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
                    "This cover image already exists in the database."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | INSERT IMAGE
            |--------------------------------------------------------------------------
            */

            $imageSql = "
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
            ";


            $imageStmt =
                mysqli_prepare(
                    $conn,
                    $imageSql
                );


            if (!$imageStmt) {

                throw new Exception(
                    "Failed to prepare cover image query."
                );

            }


            $imageCategory =
                "event";


            $imageRole =
                "cover";


            $nullImage =
                null;


            mysqli_stmt_bind_param(
                $imageStmt,
                "ibssisss",
                $event_id,
                $nullImage,
                $coverOriginalName,
                $coverStoredMime,
                $coverStoredSize,
                $coverHash,
                $imageCategory,
                $imageRole
            );


            /*
            |--------------------------------------------------------------------------
            | SEND BINARY DATA
            |--------------------------------------------------------------------------
            */

            if (
                !mysqli_stmt_send_long_data(
                    $imageStmt,
                    1,
                    $coverImageData
                )
            ) {

                throw new Exception(
                    "Failed to send cover image data."
                );

            }


            if (
                !mysqli_stmt_execute(
                    $imageStmt
                )
            ) {

                throw new Exception(
                    "Failed to save cover image: "
                    . mysqli_stmt_error($imageStmt)
                );

            }


            /*
            |--------------------------------------------------------------------------
            | GALLERY IMAGES
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $_FILES['gallery_images']
                ) &&
                isset(
                    $_FILES['gallery_images']['name']
                ) &&
                is_array(
                    $_FILES['gallery_images']['name']
                )
            ) {


                $galleryNames =
                    $_FILES['gallery_images']['name'];


                $galleryTmpNames =
                    $_FILES['gallery_images']['tmp_name'];


                $galleryErrors =
                    $_FILES['gallery_images']['error'];


                $gallerySizes =
                    $_FILES['gallery_images']['size'];


                /*
                |--------------------------------------------------------------------------
                | LOOP GALLERY
                |--------------------------------------------------------------------------
                */

                foreach (
                    $galleryNames
                    as $key => $originalName
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | Skip Empty
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty($originalName)
                    ) {

                        continue;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Skip Failed Upload
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !isset(
                            $galleryErrors[$key]
                        ) ||
                        $galleryErrors[$key]
                        !== UPLOAD_ERR_OK
                    ) {

                        continue;

                    }


                    $tmpName =
                        $galleryTmpNames[$key];


                    $originalSize =
                        (int)
                        $gallerySizes[$key];


                    /*
                    |--------------------------------------------------------------------------
                    | Size
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $originalSize >
                        $maxOriginalSize
                    ) {

                        continue;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MIME
                    |--------------------------------------------------------------------------
                    */

                    $finfo =
                        finfo_open(
                            FILEINFO_MIME_TYPE
                        );


                    $mimeType =
                        finfo_file(
                            $finfo,
                            $tmpName
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


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Image
                    |--------------------------------------------------------------------------
                    */

                    if (
                        @getimagesize(
                            $tmpName
                        ) === false
                    ) {

                        continue;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Process Image
                    |--------------------------------------------------------------------------
                    */

                    $galleryImage =
                        processUploadedImage(
                            $tmpName,
                            $mimeType,
                            $originalSize,
                            $maxWidth,
                            $maxHeight
                        );


                    $imageData =
                        $galleryImage['data'];


                    $storedMime =
                        $galleryImage['mime'];


                    $storedSize =
                        $galleryImage['size'];


                    /*
                    |--------------------------------------------------------------------------
                    | HASH
                    |--------------------------------------------------------------------------
                    */

                    $imageHash =
                        hash(
                            'sha256',
                            $imageData
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | DUPLICATE CHECK
                    |--------------------------------------------------------------------------
                    */

                    $duplicateStmt =
                        mysqli_prepare(
                            $conn,
                            $duplicateSql
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


                    /*
                    |--------------------------------------------------------------------------
                    | INSERT GALLERY IMAGE
                    |--------------------------------------------------------------------------
                    */

                    $imageRole =
                        "gallery";


                    $imageStmt =
                        mysqli_prepare(
                            $conn,
                            $imageSql
                        );


                    if (!$imageStmt) {

                        throw new Exception(
                            "Failed to prepare gallery image query."
                        );

                    }


                    $nullImage =
                        null;


                    mysqli_stmt_bind_param(
                        $imageStmt,
                        "ibssisss",
                        $event_id,
                        $nullImage,
                        $originalName,
                        $storedMime,
                        $storedSize,
                        $imageHash,
                        $imageCategory,
                        $imageRole
                    );


                    if (
                        !mysqli_stmt_send_long_data(
                            $imageStmt,
                            1,
                            $imageData
                        )
                    ) {

                        throw new Exception(
                            "Failed to send gallery image data."
                        );

                    }


                    if (
                        !mysqli_stmt_execute(
                            $imageStmt
                        )
                    ) {

                        throw new Exception(
                            "Failed to save gallery image: "
                            . mysqli_stmt_error($imageStmt)
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | ADDITIONAL INFORMATION
            |--------------------------------------------------------------------------
            */

            if (
                isset($_POST['field_name']) &&
                isset($_POST['field_value'])
            ) {


                $fieldNames =
                    $_POST['field_name'];


                $fieldValues =
                    $_POST['field_value'];


                foreach (
                    $fieldNames
                    as $key => $fieldName
                ) {


                    $fieldName =
                        trim($fieldName);


                    $fieldValue =
                        trim(
                            $fieldValues[$key] ?? ''
                        );


                    if (
                        empty($fieldName) ||
                        empty($fieldValue)
                    ) {

                        continue;

                    }


                    $detailSql = "
                        INSERT INTO event_details
                        (
                            event_id,
                            field_name,
                            field_value
                        )
                        VALUES (?, ?, ?)
                    ";


                    $detailStmt =
                        mysqli_prepare(
                            $conn,
                            $detailSql
                        );


                    mysqli_stmt_bind_param(
                        $detailStmt,
                        "iss",
                        $event_id,
                        $fieldName,
                        $fieldValue
                    );


                    if (
                        !mysqli_stmt_execute(
                            $detailStmt
                        )
                    ) {

                        throw new Exception(
                            "Failed to save additional information."
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit(
                $conn
            );


            header(
                "Location: index.php?success=1"
            );

            exit();


        } catch (Exception $e) {


            /*
            |--------------------------------------------------------------------------
            | SAFE ROLLBACK
            |--------------------------------------------------------------------------
            */

            if (
                mysqli_ping($conn)
            ) {

                mysqli_rollback(
                    $conn
                );

            }


            $error =
                $e->getMessage();

        }

    }

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
        Add Event | Admin
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="../../css/admin/admin.css?v=<?= time(); ?>"
    >

</head>


<body class="admin-dashboard">


<?php
$activeNav = 'events';
include __DIR__ . '/../includes/navbar.php';
?>


<!-- =========================================================
     PAGE
========================================================== -->

<main class="admin-container">


    <!-- HEADER -->

    <div class="admin-header">

        <div>

            <h1>
                Add New Event
            </h1>

            <p>
                Add an event to your NGO website.
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


    <!-- ERROR -->

    <?php if (isset($error)) { ?>

        <div class="admin-alert">

            <i
                class="fa-solid fa-circle-exclamation me-2"
            ></i>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- FORM -->

    <div class="admin-form-card">

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- CATEGORY -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Category

                    <span class="admin-required">
                        *
                    </span>

                </label>


                <select
                    name="category_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select Category
                    </option>


                    <?php while (
                        $category =
                        mysqli_fetch_assoc(
                            $categories
                        )
                    ) { ?>

                        <option
                            value="<?= $category['id']; ?>"
                            <?= (
                                isset(
                                    $_POST['category_id']
                                ) &&
                                $_POST['category_id']
                                ==
                                $category['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $category['name']
                            ); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- EVENT NAME -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Event Name

                    <span class="admin-required">
                        *
                    </span>

                </label>


                <input
                    type="text"
                    name="title"
                    class="form-control"
                    placeholder="Enter event name"
                    value="<?= htmlspecialchars(
                        $_POST['title'] ?? ''
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
                            $_POST['event_date'] ?? ''
                        ); ?>"
                    >

                </div>


                <div class="col-md-6 mb-4">

                    <label class="admin-form-label">

                        Location

                        <span class="admin-required">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        name="location"
                        class="form-control"
                        placeholder="Mumbai, Maharashtra"
                        value="<?= htmlspecialchars(
                            $_POST['location'] ?? ''
                        ); ?>"
                        required
                    >

                </div>

            </div>


            <!-- DESCRIPTION -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Description

                    <span class="admin-required">
                        *
                    </span>

                </label>


                <textarea
                    name="description"
                    class="form-control"
                    rows="7"
                    placeholder="Enter complete event description..."
                    required
                ><?= htmlspecialchars(
                    $_POST['description'] ?? ''
                ); ?></textarea>

            </div>


            <!-- COVER IMAGE -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Cover Image

                    <span class="admin-required">
                        *
                    </span>

                </label>


                <input
                    type="file"
                    name="cover_image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >


                <div class="admin-image-note">

                    JPG, PNG and WebP are supported.
                    Images are automatically resized and
                    compressed before being stored in the database.

                    Maximum original upload size: 25 MB.

                </div>

            </div>


            <!-- GALLERY -->

            <div class="mb-4">

                <label class="admin-form-label">

                    Gallery Images

                </label>


                <input
                    type="file"
                    name="gallery_images[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                >


                <div class="admin-image-note">

                    You can select multiple images.
                    Images are automatically resized and
                    compressed before being stored in the database.

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
                            Add any extra information specific
                            to this event.
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


                <div
                    id="detailsContainer"
                ></div>

            </div>


            <hr class="my-4">


            <!-- SUBMIT -->

            <div class="admin-form-actions">

                <button
                    type="submit"
                    name="add_event"
                    class="admin-btn-primary"
                >

                    <i
                        class="fa-solid fa-plus"
                    ></i>

                    Add Event

                </button>

            </div>


        </form>

    </div>


</main>


<!-- =========================================================
     DYNAMIC FIELDS
========================================================== -->

<script>

const addDetailButton =
    document.getElementById("addDetail");

const detailsContainer =
    document.getElementById("detailsContainer");


addDetailButton.addEventListener(
    "click",
    function()
    {

        const row =
            document.createElement("div");


        row.className =
            "admin-detail-row";


        row.innerHTML = `

            <div class="row align-items-center">

                <div class="col-md-5 mb-2 mb-md-0">

                    <input
                        type="text"
                        name="field_name[]"
                        class="form-control"
                        placeholder="Field name"
                    >

                </div>


                <div class="col-md-6 mb-2 mb-md-0">

                    <input
                        type="text"
                        name="field_value[]"
                        class="form-control"
                        placeholder="Field value"
                    >

                </div>


                <div class="col-md-1 text-end">

                    <button
                        type="button"
                        class="admin-delete-detail"
                    >

                        <i
                            class="fa-solid fa-xmark"
                        ></i>

                    </button>

                </div>

            </div>

        `;


        detailsContainer.appendChild(
            row
        );


        const removeButton =
            row.querySelector(
                ".admin-delete-detail"
            );


        removeButton.addEventListener(
            "click",
            function()
            {

                row.remove();

            }
        );

    }
);

</script>


</body>

</html>