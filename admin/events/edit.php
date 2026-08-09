<?php

session_start();

include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid event.");
}

$event_id = (int) $_GET['id'];

/* Get Event */

$sql = "SELECT * FROM events WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Event not found.");
}

$event = mysqli_fetch_assoc($result);


/* Get Categories */

$categories = mysqli_query(
    $conn,
    "SELECT id, name FROM categories ORDER BY name ASC"
);


/* Get Custom Details */

$detailSql = "
    SELECT id, field_name, field_value
    FROM event_details
    WHERE event_id = ?
    ORDER BY id ASC
";

$detailStmt = mysqli_prepare($conn, $detailSql);
mysqli_stmt_bind_param($detailStmt, "i", $event_id);
mysqli_stmt_execute($detailStmt);

$details = mysqli_stmt_get_result($detailStmt);


/* Get Gallery */

$gallerySql = "
    SELECT id, image_path
    FROM event_images
    WHERE event_id = ?
    ORDER BY id ASC
";

$galleryStmt = mysqli_prepare($conn, $gallerySql);
mysqli_stmt_bind_param($galleryStmt, "i", $event_id);
mysqli_stmt_execute($galleryStmt);

$gallery = mysqli_stmt_get_result($galleryStmt);


/* Update Event */

if (isset($_POST['update_event'])) {

    $category_id = (int) $_POST['category_id'];
    $title = trim($_POST['title']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (
        empty($category_id) ||
        empty($title) ||
        empty($event_date) ||
        empty($location) ||
        empty($description)
    ) {

        $error = "Please fill all required fields.";

    } else {

        $uploadDir = "../../uploads/events/";

        /*
        |--------------------------------------------------------------------------
        | Update Cover Image if New One Was Selected
        |--------------------------------------------------------------------------
        */

        $newCoverImage = $event['cover_image'];

        if (
            isset($_FILES['cover_image']) &&
            $_FILES['cover_image']['error'] === UPLOAD_ERR_OK
        ) {

            $extension = strtolower(
                pathinfo(
                    $_FILES['cover_image']['name'],
                    PATHINFO_EXTENSION
                )
            );

            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            if (!in_array($extension, $allowedExtensions)) {

                $error = "Invalid cover image format.";

            } else {

                $newCoverImage =
                    uniqid('event_', true) . '.' . $extension;

                if (
                    move_uploaded_file(
                        $_FILES['cover_image']['tmp_name'],
                        $uploadDir . $newCoverImage
                    )
                ) {

                    /*
                    | Delete old cover image
                    */

                    if (
                        !empty($event['cover_image']) &&
                        file_exists(
                            $uploadDir . $event['cover_image']
                        )
                    ) {

                        unlink(
                            $uploadDir . $event['cover_image']
                        );
                    }

                } else {

                    $error = "Failed to upload new cover image.";

                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update Main Event
        |--------------------------------------------------------------------------
        */

        if (!isset($error)) {

            $updateSql = "
                UPDATE events
                SET
                    category_id = ?,
                    title = ?,
                    event_date = ?,
                    location = ?,
                    description = ?,
                    cover_image = ?,
                    status = ?
                WHERE id = ?
            ";

            $updateStmt = mysqli_prepare(
                $conn,
                $updateSql
            );

            mysqli_stmt_bind_param(
                $updateStmt,
                "issssssi",
                $category_id,
                $title,
                $event_date,
                $location,
                $description,
                $newCoverImage,
                $status,
                $event_id
            );

            if (mysqli_stmt_execute($updateStmt)) {

                /*
                |--------------------------------------------------------------------------
                | Add New Gallery Images
                |--------------------------------------------------------------------------
                |
                | We do NOT delete existing gallery images here.
                | New images are simply added.
                |
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
                                [
                                    'jpg',
                                    'jpeg',
                                    'png',
                                    'webp'
                                ]
                            )
                        ) {
                            continue;
                        }

                        $imageName =
                            uniqid('gallery_', true)
                            . '.' . $extension;

                        if (
                            move_uploaded_file(
                                $_FILES['gallery_images']['tmp_name'][$key],
                                $uploadDir . $imageName
                            )
                        ) {

                            $imageSql = "
                                INSERT INTO event_images
                                (
                                    event_id,
                                    image_path
                                )
                                VALUES (?, ?)
                            ";

                            $imageStmt = mysqli_prepare(
                                $conn,
                                $imageSql
                            );

                            mysqli_stmt_bind_param(
                                $imageStmt,
                                "is",
                                $event_id,
                                $imageName
                            );

                            mysqli_stmt_execute($imageStmt);
                        }
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Update Custom Fields
                |--------------------------------------------------------------------------
                |
                | Remove existing custom fields and recreate them.
                |
                */

                $deleteDetails = mysqli_prepare(
                    $conn,
                    "DELETE FROM event_details WHERE event_id = ?"
                );

                mysqli_stmt_bind_param(
                    $deleteDetails,
                    "i",
                    $event_id
                );

                mysqli_stmt_execute($deleteDetails);


                if (
                    isset($_POST['field_name']) &&
                    isset($_POST['field_value'])
                ) {

                    $fieldNames = $_POST['field_name'];
                    $fieldValues = $_POST['field_value'];

                    foreach (
                        $fieldNames as $key => $fieldName
                    ) {

                        $fieldName = trim($fieldName);

                        $fieldValue = trim(
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

                        $detailStmt = mysqli_prepare(
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

                        mysqli_stmt_execute($detailStmt);
                    }
                }


                header(
                    "Location: index.php?updated=1"
                );

                exit();

            } else {

                $error = "Failed to update event.";

            }
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
content="width=device-width, initial-scale=1"
>

<title>Edit Event | Admin</title>

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

.gallery-image {
    width: 130px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

</style>

</head>

<body>

<div class="admin-container">

    <div
    class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold">
                Edit Event
            </h2>

            <p class="text-muted mb-0">
                Update event information and images.
            </p>

        </div>

        <a
        href="index.php"
        class="btn btn-secondary"
        >
            ← Back
        </a>

    </div>


    <?php if (isset($error)) { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


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

                        <?php while (
                            $category =
                            mysqli_fetch_assoc($categories)
                        ) { ?>

                            <option
                            value="<?= $category['id']; ?>"
                            <?= $category['id'] == $event['category_id']
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


                <!-- Title -->

                <div class="mb-4">

                    <label class="form-label">
                        Event Name
                        <span class="required">*</span>
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


                <!-- Date + Location -->

                <div class="row">

                    <div class="col-md-6 mb-4">

                        <label class="form-label">
                            Event Date
                            <span class="required">*</span>
                        </label>

                        <input
                        type="date"
                        name="event_date"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $event['event_date']
                        ); ?>"
                        required
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
                        value="<?= htmlspecialchars(
                            $event['location']
                        ); ?>"
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
                    required
                    ><?= htmlspecialchars(
                        $event['description']
                    ); ?></textarea>

                </div>


                <!-- Current Cover -->

                <div class="mb-3">

                    <label class="form-label">
                        Current Cover Image
                    </label>

                    <?php if (
                        !empty($event['cover_image'])
                    ) { ?>

                        <div class="mb-3">

                            <img
                            src="../../uploads/events/<?= htmlspecialchars(
                                $event['cover_image']
                            ); ?>"
                            class="gallery-image"
                            >

                        </div>

                    <?php } ?>

                </div>


                <!-- New Cover -->

                <div class="mb-4">

                    <label class="form-label">
                        Replace Cover Image
                    </label>

                    <input
                    type="file"
                    name="cover_image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="text-muted">
                        Leave empty to keep the current image.
                    </small>

                </div>


                <!-- Existing Gallery -->

                <div class="mb-4">

                    <label class="form-label">
                        Current Gallery
                    </label>

                    <div class="row">

                        <?php if (
                            mysqli_num_rows($gallery) > 0
                        ) { ?>

                            <?php while (
                                $image =
                                mysqli_fetch_assoc($gallery)
                            ) { ?>

                                <div
                                class="col-md-3 mb-3"
                                >

                                    <img
                                    src="../../uploads/events/<?= htmlspecialchars(
                                        $image['image_path']
                                    ); ?>"
                                    class="gallery-image"
                                    >

                                    <a
                                    href="delete_image.php?id=<?= $image['id']; ?>"
                                    class="btn btn-sm btn-outline-danger mt-2 w-100"
                                    onclick="return confirm('Delete this image?');"
                                    >

                                        Delete

                                    </a>

                                </div>

                            <?php } ?>

                        <?php } else { ?>

                            <p class="text-muted">
                                No gallery images.
                            </p>

                        <?php } ?>

                    </div>

                </div>


                <!-- Add Gallery Images -->

                <div class="mb-4">

                    <label class="form-label">
                        Add More Gallery Images
                    </label>

                    <input
                    type="file"
                    name="gallery_images[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                    >

                </div>


                <hr class="my-4">


                <!-- Custom Fields -->

                <div class="mb-4">

                    <div
                    class="d-flex justify-content-between align-items-center mb-3"
                    >

                        <div>

                            <h5 class="fw-bold">
                                Additional Information
                            </h5>

                            <small class="text-muted">
                                Add any event-specific information.
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


                    <div id="detailsContainer">

                        <?php while (
                            $detail =
                            mysqli_fetch_assoc($details)
                        ) { ?>

                            <div class="detail-row">

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
                                        class="btn btn-outline-danger remove-detail"
                                        >

                                            ×

                                        </button>

                                    </div>

                                </div>

                            </div>

                        <?php } ?>

                    </div>

                </div>


                <!-- Status -->

                <div class="mb-4">

                    <label class="form-label">
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


                <div class="text-end">

                    <button
                    type="submit"
                    name="update_event"
                    class="btn btn-primary px-5"
                    >

                        Save Changes

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
    function () {

        const row =
            document.createElement("div");

        row.className = "detail-row";

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
                    class="btn btn-outline-danger remove-detail"
                    >
                        ×
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


/* Existing remove buttons */

document
.querySelectorAll(".remove-detail")
.forEach(function(button) {

    button.addEventListener(
        "click",
        function() {

            button
            .closest(".detail-row")
            .remove();

        }
    );

});

</script>

</body>

</html>