<?php

session_start();

include "config/db.php";


/*
|--------------------------------------------------------------------------
| DEVELOPER ACCESS
|--------------------------------------------------------------------------
|
| This page is intended for developers only.
| Do not expose this page to the NGO/public in production.
|
*/


/*
|--------------------------------------------------------------------------
| IMAGE SETTINGS
|--------------------------------------------------------------------------
*/

$maxOriginalSize = 25 * 1024 * 1024; // 25 MB

$maxWidth = 1920;
$maxHeight = 1920;

$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];


/*
|--------------------------------------------------------------------------
| AVAILABLE CATEGORIES
|--------------------------------------------------------------------------
*/

$allowedCategories = [
    'general',
    'homepage',
    'about',
    'our_work',
    'annual_event'
];


$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| IMAGE PROCESSING FUNCTION
|--------------------------------------------------------------------------
*/

function processStaticImage(
    string $tmpPath,
    string $mimeType,
    int $originalSize,
    int $maxWidth,
    int $maxHeight
): array {

    /*
    |--------------------------------------------------------------------------
    | GD CHECK
    |--------------------------------------------------------------------------
    */

    if (!extension_loaded('gd')) {

        throw new Exception(
            "PHP GD extension is not enabled."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SIZE CHECK
    |--------------------------------------------------------------------------
    */

    if ($originalSize > 25 * 1024 * 1024) {

        throw new Exception(
            "Image is larger than the allowed 25 MB limit."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY IMAGE
    |--------------------------------------------------------------------------
    */

    $imageInfo = @getimagesize($tmpPath);


    if ($imageInfo === false) {

        throw new Exception(
            "The selected file is not a valid image."
        );

    }


    $originalWidth = $imageInfo[0];

    $originalHeight = $imageInfo[1];


    /*
    |--------------------------------------------------------------------------
    | CREATE SOURCE IMAGE
    |--------------------------------------------------------------------------
    */

    switch ($mimeType) {

        case 'image/jpeg':

            $source = @imagecreatefromjpeg($tmpPath);

            break;


        case 'image/png':

            $source = @imagecreatefrompng($tmpPath);

            break;


        case 'image/webp':

            if (
                !function_exists('imagecreatefromwebp')
            ) {

                throw new Exception(
                    "WebP support is not enabled in PHP GD."
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
            "Unable to process the image."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE NEW DIMENSIONS
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
    | CREATE DESTINATION
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
    | PRESERVE TRANSPARENCY
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


    $transparent = imagecolorallocatealpha(
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
    | RESIZE
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
    | COMPRESS
    |--------------------------------------------------------------------------
    */

    ob_start();


    if (function_exists('imagewebp')) {

        imagewebp(
            $destination,
            null,
            82
        );

        $storedMime = 'image/webp';

    } else {

        imagejpeg(
            $destination,
            null,
            82
        );

        $storedMime = 'image/jpeg';

    }


    $imageData = ob_get_clean();


    /*
    |--------------------------------------------------------------------------
    | CLEAN MEMORY
    |--------------------------------------------------------------------------
    */

    imagedestroy($source);

    imagedestroy($destination);


    if (
        $imageData === false ||
        $imageData === ''
    ) {

        throw new Exception(
            "Failed to optimize the image."
        );

    }


    return [

        'data' => $imageData,

        'type' => $storedMime,

        'size' => strlen($imageData)

    ];

}


/*
|--------------------------------------------------------------------------
| HANDLE UPLOAD
|--------------------------------------------------------------------------
*/

if (isset($_POST['upload_images'])) {

    try {

        /*
        |--------------------------------------------------------------------------
        | CHECK CATEGORY
        |--------------------------------------------------------------------------
        */

        $imageCategory =
            isset($_POST['image_category'])
                ? trim($_POST['image_category'])
                : 'general';


        if (
            !in_array(
                $imageCategory,
                $allowedCategories,
                true
            )
        ) {

            throw new Exception(
                "Invalid image category selected."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CHECK FILES
        |--------------------------------------------------------------------------
        */

        if (
            !isset($_FILES['images']) ||
            !is_array($_FILES['images']['name'])
        ) {

            throw new Exception(
                "Please select at least one image."
            );

        }


        $fileCount =
            count($_FILES['images']['name']);


        if ($fileCount === 0) {

            throw new Exception(
                "Please select at least one image."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPLOAD COUNTERS
        |--------------------------------------------------------------------------
        */

        $uploadedCount = 0;

        $skippedCount = 0;

        $failedCount = 0;

        $uploadMessages = [];


        /*
        |--------------------------------------------------------------------------
        | PROCESS EACH IMAGE
        |--------------------------------------------------------------------------
        */

        for (
            $fileIndex = 0;
            $fileIndex < $fileCount;
            $fileIndex++
        ) {


            try {

                /*
                |--------------------------------------------------------------------------
                | FILE ERROR
                |--------------------------------------------------------------------------
                */

                if (
                    $_FILES['images']['error'][$fileIndex]
                    !== UPLOAD_ERR_OK
                ) {

                    throw new Exception(
                        "Upload error."
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | FILE INFORMATION
                |--------------------------------------------------------------------------
                */

                $tmpPath =
                    $_FILES['images']['tmp_name'][$fileIndex];


                $originalName =
                    basename(
                        $_FILES['images']['name'][$fileIndex]
                    );


                $originalSize =
                    (int)
                    $_FILES['images']['size'][$fileIndex];


                /*
                |--------------------------------------------------------------------------
                | CLEAN FILE NAME
                |--------------------------------------------------------------------------
                */

                $originalName =
                    preg_replace(
                        '/[^A-Za-z0-9._-]/',
                        '_',
                        $originalName
                    );


                /*
                |--------------------------------------------------------------------------
                | CHECK EXTENSION
                |--------------------------------------------------------------------------
                */

                $extension =
                    strtolower(
                        pathinfo(
                            $originalName,
                            PATHINFO_EXTENSION
                        )
                    );


                $allowedExtensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];


                if (
                    !in_array(
                        $extension,
                        $allowedExtensions,
                        true
                    )
                ) {

                    throw new Exception(
                        "Only JPG, JPEG, PNG and WebP images are allowed."
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | DETECT REAL MIME TYPE
                |--------------------------------------------------------------------------
                */

                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                if (!$finfo) {

                    throw new Exception(
                        "Unable to detect image type."
                    );

                }


                $mimeType =
                    finfo_file(
                        $finfo,
                        $tmpPath
                    );


                finfo_close($finfo);


                if (
                    !in_array(
                        $mimeType,
                        $allowedMimeTypes,
                        true
                    )
                ) {

                    throw new Exception(
                        "Invalid image file."
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | PROCESS IMAGE
                |--------------------------------------------------------------------------
                */

                $processed =
                    processStaticImage(
                        $tmpPath,
                        $mimeType,
                        $originalSize,
                        $maxWidth,
                        $maxHeight
                    );


                $imageData =
                    $processed['data'];


                $imageType =
                    $processed['type'];


                $imageSize =
                    $processed['size'];


                /*
                |--------------------------------------------------------------------------
                | CREATE HASH
                |--------------------------------------------------------------------------
                */

                $imageHash =
                    hash(
                        'sha256',
                        $imageData
                    );


                /*
                |--------------------------------------------------------------------------
                | CHECK DUPLICATE NAME
                |--------------------------------------------------------------------------
                */

                $nameCheck =
                    mysqli_prepare(
                        $conn,
                        "
                        SELECT id
                        FROM static_images
                        WHERE image_name = ?
                        LIMIT 1
                        "
                    );


                if (!$nameCheck) {

                    throw new Exception(
                        "Unable to check image filename."
                    );

                }


                mysqli_stmt_bind_param(
                    $nameCheck,
                    "s",
                    $originalName
                );


                mysqli_stmt_execute(
                    $nameCheck
                );


                $nameResult =
                    mysqli_stmt_get_result(
                        $nameCheck
                    );


                if (
                    mysqli_num_rows(
                        $nameResult
                    ) > 0
                ) {

                    $skippedCount++;

                    $uploadMessages[] =
                        htmlspecialchars(
                            $originalName
                        )
                        . " skipped - filename already exists.";

                    continue;

                }


                /*
                |--------------------------------------------------------------------------
                | CHECK DUPLICATE IMAGE CONTENT
                |--------------------------------------------------------------------------
                */

                $hashCheck =
                    mysqli_prepare(
                        $conn,
                        "
                        SELECT id, image_name
                        FROM static_images
                        WHERE image_hash = ?
                        LIMIT 1
                        "
                    );


                if (!$hashCheck) {

                    throw new Exception(
                        "Unable to check duplicate image."
                    );

                }


                mysqli_stmt_bind_param(
                    $hashCheck,
                    "s",
                    $imageHash
                );


                mysqli_stmt_execute(
                    $hashCheck
                );


                $hashResult =
                    mysqli_stmt_get_result(
                        $hashCheck
                    );


                if (
                    mysqli_num_rows(
                        $hashResult
                    ) > 0
                ) {

                    $existing =
                        mysqli_fetch_assoc(
                            $hashResult
                        );


                    $skippedCount++;

                    $uploadMessages[] =
                        htmlspecialchars(
                            $originalName
                        )
                        . " skipped - same image already exists as "
                        . htmlspecialchars(
                            $existing['image_name']
                        )
                        . ".";

                    continue;

                }


                /*
                |--------------------------------------------------------------------------
                | INSERT IMAGE
                |--------------------------------------------------------------------------
                */

                $sql = "
                    INSERT INTO static_images
                    (
                        image_name,
                        image_data,
                        image_type,
                        image_size,
                        image_hash,
                        image_category
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ";


                $stmt =
                    mysqli_prepare(
                        $conn,
                        $sql
                    );


                if (!$stmt) {

                    throw new Exception(
                        "Failed to prepare database query."
                    );

                }


                $nullData = null;


                /*
                |--------------------------------------------------------------------------
                | BIND PARAMETERS
                |--------------------------------------------------------------------------
                |
                | s = string
                | b = blob
                | s = string
                | i = integer
                | s = string
                | s = string
                |
                */

                mysqli_stmt_bind_param(
                    $stmt,
                    "sbsiss",
                    $originalName,
                    $nullData,
                    $imageType,
                    $imageSize,
                    $imageHash,
                    $imageCategory
                );


                /*
                |--------------------------------------------------------------------------
                | SEND BLOB
                |--------------------------------------------------------------------------
                */

                mysqli_stmt_send_long_data(
                    $stmt,
                    1,
                    $imageData
                );


                /*
                |--------------------------------------------------------------------------
                | EXECUTE
                |--------------------------------------------------------------------------
                */

                if (
                    !mysqli_stmt_execute($stmt)
                ) {

                    throw new Exception(
                        "Failed to store image: "
                        . mysqli_stmt_error($stmt)
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | GET DATABASE ID
                |--------------------------------------------------------------------------
                */

                $newImageId =
                    mysqli_insert_id(
                        $conn
                    );


                $uploadedCount++;

                $uploadMessages[] =
                    htmlspecialchars(
                        $originalName
                    )
                    . " uploaded successfully "
                    . "(ID: "
                    . $newImageId
                    . ").";


            } catch (Exception $fileException) {

                $failedCount++;

                $uploadMessages[] =
                    htmlspecialchars(
                        $_FILES['images']['name'][$fileIndex]
                    )
                    . " failed - "
                    . htmlspecialchars(
                        $fileException->getMessage()
                    );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | FINAL MESSAGE
        |--------------------------------------------------------------------------
        */

        $message =
            "Upload completed. "
            . $uploadedCount
            . " image(s) uploaded, "
            . $skippedCount
            . " skipped, "
            . $failedCount
            . " failed."
            . "<br><br>"
            . implode(
                "<br>",
                $uploadMessages
            );


    } catch (Exception $e) {

        $error =
            $e->getMessage();

    }

}


/*
|--------------------------------------------------------------------------
| GET EXISTING STATIC IMAGES
|--------------------------------------------------------------------------
*/

$imagesResult =
    mysqli_query(
        $conn,
        "
        SELECT
            id,
            image_name,
            image_type,
            image_size,
            image_category,
            uploaded_at
        FROM static_images
        ORDER BY id DESC
        "
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
        Static Images
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background: #f6f3ec;
        }

        .category-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #e5dfd1;
            color: #4d4a42;
            font-size: 12px;
            font-weight: 600;
        }

        .annual-badge {
            background: #5a584f;
            color: #ffffff;
        }

        .upload-note {
            background: #f8f5ee;
            border: 1px solid #ddd7ca;
            border-radius: 10px;
            padding: 15px;
        }

    </style>

</head>


<body>


<div class="container py-5">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="mb-5">

        <h1>
            Static Website Images
        </h1>

        <p class="text-muted">

            Developer image storage

        </p>

    </div>


    <!-- =====================================================
         MESSAGES
    ====================================================== -->

    <?php if ($message !== "") { ?>

        <div class="alert alert-success">

            <?= $message; ?>

        </div>

    <?php } ?>


    <?php if ($error !== "") { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =====================================================
         UPLOAD
    ====================================================== -->

    <div class="card mb-5">

        <div class="card-body p-4">


            <h3 class="mb-4">

                Add Static Images

            </h3>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- =================================================
                     IMAGE CATEGORY
                ================================================== -->

                <div class="mb-4">

                    <label
                        class="form-label fw-semibold"
                    >

                        Image Category

                    </label>


                    <select
                        name="image_category"
                        class="form-select"
                        required
                    >

                        <option
                            value="general"
                        >
                            General
                        </option>


                        <option
                            value="homepage"
                        >
                            Homepage
                        </option>


                        <option
                            value="about"
                        >
                            About
                        </option>


                        <option
                            value="our_work"
                        >
                            Our Work
                        </option>


                        <option
                            value="annual_event"
                        >
                            Annual Event
                        </option>

                    </select>


                    <div class="form-text">

                        Choose
                        <strong>Annual Event</strong>
                        when uploading photographs
                        for the annual-event gallery.

                    </div>

                </div>


                <!-- =================================================
                     IMAGE FILES
                ================================================== -->

                <div class="mb-3">

                    <label
                        class="form-label fw-semibold"
                    >

                        Select Image(s)

                    </label>


                    <input
                        type="file"
                        name="images[]"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                        required
                    >

                </div>


                <!-- =================================================
                     INFORMATION
                ================================================== -->

                <div class="upload-note mb-4">

                    <small class="text-muted">

                        <strong>
                            You can select multiple images at once.
                        </strong>

                        <br><br>

                        For the annual event, select:

                        <br>

                        <strong>
                            Image Category → Annual Event
                        </strong>

                        <br><br>

                        Example filenames:

                        <br>

                        annual_event_01.jpg

                        <br>

                        annual_event_02.jpg

                        <br>

                        annual_event_03.jpg

                        <br>

                        annual_event_04.jpg

                        <br><br>

                        Maximum original image size:
                        25 MB per image.

                        <br>

                        Images are automatically resized
                        and compressed before being stored.

                    </small>

                </div>


                <!-- =================================================
                     SUBMIT
                ================================================== -->

                <button
                    type="submit"
                    name="upload_images"
                    class="btn btn-dark"
                >

                    <i class="bi bi-cloud-arrow-up"></i>

                    Store Images in Database

                </button>


            </form>


        </div>

    </div>


    <!-- =====================================================
         EXISTING IMAGES
    ====================================================== -->

    <h3 class="mb-4">

        Stored Images

    </h3>


    <div class="table-responsive">


        <table
            class="table table-bordered align-middle bg-white"
        >

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Preview
                    </th>

                    <th>
                        Filename
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Type
                    </th>

                    <th>
                        Size
                    </th>

                    <th>
                        Uploaded
                    </th>

                    <th>
                        Usage
                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if (
                    mysqli_num_rows(
                        $imagesResult
                    ) > 0
                ) { ?>


                    <?php while (
                        $image =
                        mysqli_fetch_assoc(
                            $imagesResult
                        )
                    ) { ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?= (int) $image['id']; ?>

                            </td>


                            <!-- PREVIEW -->

                            <td>

                                <img
                                    src="static_image.php?id=<?= (int) $image['id']; ?>"
                                    style="
                                        width:120px;
                                        height:80px;
                                        object-fit:cover;
                                        border-radius:6px;
                                    "
                                    alt=""
                                    loading="lazy"
                                >

                            </td>


                            <!-- FILENAME -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $image['image_name']
                                    ); ?>

                                </strong>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <span
                                    class="
                                        category-badge
                                        <?= $image['image_category'] === 'annual_event'
                                            ? 'annual-badge'
                                            : ''; ?>
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $image['image_category']
                                    ); ?>

                                </span>

                            </td>


                            <!-- TYPE -->

                            <td>

                                <?= htmlspecialchars(
                                    $image['image_type']
                                ); ?>

                            </td>


                            <!-- SIZE -->

                            <td>

                                <?= number_format(
                                    $image['image_size'] / 1024,
                                    2
                                ); ?>

                                KB

                            </td>


                            <!-- UPLOADED -->

                            <td>

                                <?= htmlspecialchars(
                                    $image['uploaded_at']
                                ); ?>

                            </td>


                            <!-- USAGE -->

                            <td>

                                <code>

                                    static_image.php?name=<?= urlencode(
                                        $image['image_name']
                                    ); ?>

                                </code>

                            </td>


                        </tr>


                    <?php } ?>


                <?php } else { ?>


                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-4"
                        >

                            No static images have been
                            uploaded yet.

                        </td>

                    </tr>


                <?php } ?>


            </tbody>

        </table>


    </div>


</div>


</body>

</html>