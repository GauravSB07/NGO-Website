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
| Get Categories
|--------------------------------------------------------------------------
*/

$categoryQuery = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$categories = mysqli_query($conn, $categoryQuery);


/*
|--------------------------------------------------------------------------
| Add Event
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_event'])) {

    $category_id = intval($_POST['category_id']);

    $title = trim($_POST['title']);

    $event_date = $_POST['event_date'];

    $location = trim($_POST['location']);

    $description = trim($_POST['description']);


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($category_id) ||
        empty($title) ||
        empty($location) ||
        empty($description)
    ) {

        $error = "Please fill all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | Cover Image
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {

        if (
            !isset($_FILES['cover_image']) ||
            $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK
        ) {

            $error = "Please select a cover image.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Upload Directory
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {

        $uploadDir = "../../uploads/events/";

        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0777, true);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Upload Cover Image
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {

        $coverOriginalName = $_FILES['cover_image']['name'];

        $coverExtension = strtolower(
            pathinfo(
                $coverOriginalName,
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
                $coverExtension,
                $allowedExtensions
            )
        ) {

            $error = "Invalid cover image format.";

        } else {

            $coverImage =
                uniqid('event_', true)
                . '.'
                . $coverExtension;


            if (
                !move_uploaded_file(
                    $_FILES['cover_image']['tmp_name'],
                    $uploadDir . $coverImage
                )
            ) {

                $error = "Failed to upload cover image.";

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Event
    |--------------------------------------------------------------------------
    */

    if (!isset($error)) {

        $sql = "
            INSERT INTO events
            (
                category_id,
                title,
                event_date,
                location,
                description,
                cover_image
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";


        $stmt = mysqli_prepare(
            $conn,
            $sql
        );


        mysqli_stmt_bind_param(
            $stmt,
            "isssss",
            $category_id,
            $title,
            $event_date,
            $location,
            $description,
            $coverImage
        );


        if (mysqli_stmt_execute($stmt)) {

            $event_id = mysqli_insert_id($conn);


            /*
            |--------------------------------------------------------------------------
            | Gallery Images
            |--------------------------------------------------------------------------
            */

            if (
                isset($_FILES['gallery_images']) &&
                isset($_FILES['gallery_images']['name']) &&
                is_array($_FILES['gallery_images']['name'])
            ) {


                foreach (
                    $_FILES['gallery_images']['name']
                    as $key => $originalName
                ) {


                    if (
                        $_FILES['gallery_images']['error'][$key]
                        !== UPLOAD_ERR_OK
                    ) {

                        continue;

                    }


                    $extension = strtolower(
                        pathinfo(
                            $originalName,
                            PATHINFO_EXTENSION
                        )
                    );


                    if (
                        !in_array(
                            $extension,
                            $allowedExtensions
                        )
                    ) {

                        continue;

                    }


                    $imageName =
                        uniqid('gallery_', true)
                        . '.'
                        . $extension;


                    $imageUploaded =
                        move_uploaded_file(
                            $_FILES['gallery_images']['tmp_name'][$key],
                            $uploadDir . $imageName
                        );


                    if ($imageUploaded) {


                        $imageSql = "
                            INSERT INTO event_images
                            (
                                event_id,
                                image_path
                            )
                            VALUES (?, ?)
                        ";


                        $imageStmt =
                            mysqli_prepare(
                                $conn,
                                $imageSql
                            );


                        mysqli_stmt_bind_param(
                            $imageStmt,
                            "is",
                            $event_id,
                            $imageName
                        );


                        mysqli_stmt_execute(
                            $imageStmt
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Additional Custom Fields
            |--------------------------------------------------------------------------
            */

            if (
                isset($_POST['field_name']) &&
                isset($_POST['field_value'])
            ) {


                $fieldNames = $_POST['field_name'];

                $fieldValues = $_POST['field_value'];


                foreach (
                    $fieldNames as $key => $fieldName
                ) {


                    $fieldName =
                        trim($fieldName);


                    $fieldValue =
                        trim(
                            $fieldValues[$key] ?? ''
                        );


                    /*
                    | Skip completely empty rows
                    */

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


                    mysqli_stmt_execute(
                        $detailStmt
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            header(
                "Location: index.php?success=1"
            );

            exit();


        } else {

            $error = "Failed to add event.";

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

<title>Add Event | Admin</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>


<style>

body {

    background: #f5f6fa;

}


.admin-container {

    max-width: 1000px;

    margin: 50px auto;

}


.card {

    border: none;

    border-radius: 12px;

}


.form-label {

    font-weight: 600;

}


.required {

    color: red;

}


.detail-row {

    background: #f8f9fa;

    border: 1px solid #dee2e6;

    border-radius: 8px;

    padding: 15px;

    margin-bottom: 10px;

}


.remove-detail {

    height: 38px;

}


.image-note {

    font-size: 13px;

    color: #6c757d;

}

</style>

</head>


<body>


<div class="admin-container">


    <!-- Header -->

    <div
    class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold">

                Add New Event

            </h2>

            <p class="text-muted mb-0">

                Add an event to your NGO website.

            </p>

        </div>


        <a
        href="index.php"
        class="btn btn-secondary"
        >

            ← Back

        </a>

    </div>


    <!-- Error -->

    <?php if (isset($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- Form Card -->

    <div class="card shadow-sm">

        <div class="card-body p-4">


            <form
            method="POST"
            enctype="multipart/form-data"
            >


                <!-- Category -->

                <div class="mb-4">

                    <label class="form-label">

                        Category

                        <span class="required">*</span>

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
                            mysqli_fetch_assoc($categories)
                        ) { ?>

                            <option
                            value="<?= $category['id']; ?>"
                            >

                                <?= htmlspecialchars(
                                    $category['name']
                                ); ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- Event Name -->

                <div class="mb-4">

                    <label class="form-label">

                        Event Name

                        <span class="required">*</span>

                    </label>


                    <input
                    type="text"
                    name="title"
                    class="form-control"
                    placeholder="Enter event name"
                    required
                    >

                </div>


                <!-- Date + Location -->

                <div class="row">


                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Event Date

                        </label>


                        <input
                        type="date"
                        name="event_date"
                        class="form-control"
                        >

                    </div>


                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Location

                            <span class="required">*</span>

                        </label>


                        <input
                        type="text"
                        name="location"
                        class="form-control"
                        placeholder="Mumbai, Maharashtra"
                        required
                        >

                    </div>

                </div>


                <!-- Description -->

                <div class="mb-4">

                    <label class="form-label">

                        Description

                        <span class="required">*</span>

                    </label>


                    <textarea
                    name="description"
                    class="form-control"
                    rows="7"
                    placeholder="Enter complete event description..."
                    required
                    ></textarea>

                </div>


                <!-- Cover Image -->

                <div class="mb-4">

                    <label class="form-label">

                        Cover Image

                        <span class="required">*</span>

                    </label>


                    <input
                    type="file"
                    name="cover_image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                    >


                    <div class="image-note mt-2">

                        This image will appear on the event card
                        and as the main event image.

                    </div>

                </div>


                <!-- Gallery -->

                <div class="mb-4">

                    <label class="form-label">

                        Gallery Images

                    </label>


                    <input
                    type="file"
                    name="gallery_images[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                    >


                    <div class="image-note mt-2">

                        You can select multiple images.

                    </div>

                </div>


                <hr class="my-4">


                <!-- Additional Information -->

                <div class="mb-4">


                    <div
                    class="d-flex justify-content-between align-items-center mb-3"
                    >

                        <div>

                            <h5 class="fw-bold mb-1">

                                Additional Information

                            </h5>

                            <small class="text-muted">

                                Add any extra information specific
                                to this event.

                            </small>

                        </div>


                        <button
                        type="button"
                        class="btn btn-outline-primary"
                        id="addDetail"
                        >

                            + Add Another Field

                        </button>

                    </div>


                    <!-- Dynamic Fields -->

                    <div id="detailsContainer">

                    </div>


                </div>


                <hr class="my-4">


                <!-- Submit -->

                <div
                class="d-flex justify-content-end"
                >

                    <button
                    type="submit"
                    name="add_event"
                    class="btn btn-primary px-5"
                    >

                        Add Event

                    </button>

                </div>


            </form>


        </div>

    </div>

</div>


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
            "detail-row";


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
                    class="btn btn-outline-danger remove-detail"
                    >

                        ×

                    </button>

                </div>

            </div>

        `;


        detailsContainer.appendChild(row);


        const removeButton =
            row.querySelector(".remove-detail");


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