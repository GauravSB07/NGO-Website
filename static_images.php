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
|--------------------------------------------------------------------------
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

    if (
        $originalSize > 25 * 1024 * 1024
    ) {

        throw new Exception(
            "Image is larger than the allowed 25 MB limit."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY IMAGE
    |--------------------------------------------------------------------------
    */

    $imageInfo =
        @getimagesize($tmpPath);


    if ($imageInfo === false) {

        throw new Exception(
            "The selected file is not a valid image."
        );

    }


    $originalWidth =
        $imageInfo[0];

    $originalHeight =
        $imageInfo[1];


    /*
    |--------------------------------------------------------------------------
    | CREATE SOURCE IMAGE
    |--------------------------------------------------------------------------
    */

    switch ($mimeType) {

        case 'image/jpeg':

            $source =
                @imagecreatefromjpeg(
                    $tmpPath
                );

            break;


        case 'image/png':

            $source =
                @imagecreatefrompng(
                    $tmpPath
                );

            break;


        case 'image/webp':

            if (
                !function_exists(
                    'imagecreatefromwebp'
                )
            ) {

                throw new Exception(
                    "WebP support is not enabled in PHP GD."
                );

            }

            $source =
                @imagecreatefromwebp(
                    $tmpPath
                );

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

    $scale =
        min(
            $maxWidth / $originalWidth,
            $maxHeight / $originalHeight,
            1
        );


    $newWidth =
        max(
            1,
            (int) round(
                $originalWidth * $scale
            )
        );


    $newHeight =
        max(
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


    if (
        function_exists('imagewebp')
    ) {

        imagewebp(
            $destination,
            null,
            82
        );

        $storedMime =
            'image/webp';

    } else {

        imagejpeg(
            $destination,
            null,
            82
        );

        $storedMime =
            'image/jpeg';

    }


    $imageData =
        ob_get_clean();


    /*
    |--------------------------------------------------------------------------
    | CLEAN MEMORY
    |--------------------------------------------------------------------------
    */

    imagedestroy(
        $source
    );

    imagedestroy(
        $destination
    );


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

if (
    isset($_POST['upload_image'])
) {

    try {

        /*
        |--------------------------------------------------------------------------
        | CHECK FILE
        |--------------------------------------------------------------------------
        */

        if (
            !isset($_FILES['image'])
        ) {

            throw new Exception(
                "Please select an image."
            );

        }


        if (
            $_FILES['image']['error']
            !== UPLOAD_ERR_OK
        ) {

            throw new Exception(
                "There was an error uploading the image."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ORIGINAL FILE INFORMATION
        |--------------------------------------------------------------------------
        */

        $tmpPath =
            $_FILES['image']['tmp_name'];


        $originalName =
            basename(
                $_FILES['image']['name']
            );


        $originalSize =
            (int)
            $_FILES['image']['size'];


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
        | CHECK FILE EXTENSION
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

            throw new Exception(
                "An image named '{$originalName}' already exists."
            );

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


            throw new Exception(
                "This image is already stored as "
                . $existing['image_name']
                . "."
            );

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
                image_hash
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
        |
        |--------------------------------------------------------------------------
        */

        mysqli_stmt_bind_param(
            $stmt,
            "sbsis",
            $originalName,
            $nullData,
            $imageType,
            $imageSize,
            $imageHash
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


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        $message =
            "Image uploaded successfully! "
            . "Database ID: "
            . $newImageId
            . " | Filename: "
            . $originalName;


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


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<div class="container py-5">


    <div class="mb-5">

        <h1>
            Static Website Images
        </h1>

        <p class="text-muted">

            Developer image storage

        </p>

    </div>


    <?php if ($message !== "") { ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($message); ?>

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

                Add Static Image

            </h3>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="mb-3">

                    <label class="form-label">

                        Select Image

                    </label>


                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                </div>


                <div class="mb-4">

                    <small class="text-muted">

                        Use meaningful filenames such as:

                        <br>

                        <strong>
                            homepage_image1.png
                        </strong>

                        <br>

                        <strong>
                            homepage_image2.png
                        </strong>

                        <br>

                        <strong>
                            about_image1.jpg
                        </strong>

                        <br>

                        <strong>
                            our_work_image1.webp
                        </strong>

                        <br><br>

                        Maximum original image size:
                        25 MB.

                        The image will automatically be
                        resized and compressed before storage.

                    </small>

                </div>


                <button
                    type="submit"
                    name="upload_image"
                    class="btn btn-primary"
                >

                    Store Image in Database

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


        <table class="table table-bordered align-middle">

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


                            <td>

                                <?= (int) $image['id']; ?>

                            </td>


                            <td>

                                <img
                                    src="static_image.php?id=<?= (int) $image['id']; ?>"
                                    style="
                                        width:120px;
                                        height:80px;
                                        object-fit:cover;
                                    "
                                    alt=""
                                >

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $image['image_name']
                                    ); ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $image['image_type']
                                ); ?>

                            </td>


                            <td>

                                <?= number_format(
                                    $image['image_size']
                                    / 1024,
                                    2
                                ); ?>

                                KB

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $image['uploaded_at']
                                ); ?>

                            </td>


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
                            colspan="7"
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