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
| Variables
|--------------------------------------------------------------------------
*/

$editMode = false;
$editCategory = null;
$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| DELETE CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {

    $category_id = (int) $_GET['delete'];


    /*
    | Check if category has events
    */

    $checkSql = "
        SELECT COUNT(*) AS total
        FROM events
        WHERE category_id = ?
    ";

    $checkStmt = mysqli_prepare(
        $conn,
        $checkSql
    );

    mysqli_stmt_bind_param(
        $checkStmt,
        "i",
        $category_id
    );

    mysqli_stmt_execute($checkStmt);

    $checkResult =
        mysqli_stmt_get_result($checkStmt);

    $eventData =
        mysqli_fetch_assoc($checkResult);


    if ($eventData['total'] > 0) {

        $error =
            "This category cannot be deleted because it has events assigned to it.";

    } else {

        $deleteSql = "
            DELETE FROM categories
            WHERE id = ?
        ";

        $deleteStmt =
            mysqli_prepare(
                $conn,
                $deleteSql
            );

        mysqli_stmt_bind_param(
            $deleteStmt,
            "i",
            $category_id
        );

        if (mysqli_stmt_execute($deleteStmt)) {

            $success =
                "Category deleted successfully.";

        } else {

            $error =
                "Failed to delete category.";

        }

    }

}


/*
|--------------------------------------------------------------------------
| LOAD CATEGORY FOR EDIT
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['edit']) &&
    is_numeric($_GET['edit'])
) {

    $category_id = (int) $_GET['edit'];


    $editSql = "
        SELECT *
        FROM categories
        WHERE id = ?
    ";

    $editStmt =
        mysqli_prepare(
            $conn,
            $editSql
        );

    mysqli_stmt_bind_param(
        $editStmt,
        "i",
        $category_id
    );

    mysqli_stmt_execute($editStmt);

    $editResult =
        mysqli_stmt_get_result($editStmt);


    if (mysqli_num_rows($editResult) == 1) {

        $editCategory =
            mysqli_fetch_assoc($editResult);

        $editMode = true;

    } else {

        $error =
            "Category not found.";

    }

}


/*
|--------------------------------------------------------------------------
| ADD CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_category'])) {

    $name =
        trim($_POST['name']);

    $slug =
        trim($_POST['slug']);

    $short_description =
        trim($_POST['short_description']);


    /*
    | Generate URL-friendly slug
    */

    $slug = strtolower($slug);

    $slug = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $slug
    );

    $slug = trim(
        $slug,
        '-'
    );


    if (
        empty($name) ||
        empty($slug)
    ) {

        $error =
            "Category name and slug are required.";

    } else {


        /*
        | Check duplicate slug
        */

        $checkSql = "
            SELECT id
            FROM categories
            WHERE slug = ?
        ";

        $checkStmt =
            mysqli_prepare(
                $conn,
                $checkSql
            );

        mysqli_stmt_bind_param(
            $checkStmt,
            "s",
            $slug
        );

        mysqli_stmt_execute($checkStmt);

        $checkResult =
            mysqli_stmt_get_result($checkStmt);


        if (
            mysqli_num_rows($checkResult) > 0
        ) {

            $error =
                "This slug already exists.";

        } else {


            $insertSql = "
                INSERT INTO categories
                (
                    name,
                    slug,
                    short_description
                )
                VALUES (?, ?, ?)
            ";

            $insertStmt =
                mysqli_prepare(
                    $conn,
                    $insertSql
                );

            mysqli_stmt_bind_param(
                $insertStmt,
                "sss",
                $name,
                $slug,
                $short_description
            );


            if (
                mysqli_stmt_execute(
                    $insertStmt
                )
            ) {

                $success =
                    "Category added successfully.";

            } else {

                $error =
                    "Failed to add category.";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| UPDATE CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_category'])) {

    $category_id =
        (int) $_POST['category_id'];

    $name =
        trim($_POST['name']);

    $slug =
        trim($_POST['slug']);

    $short_description =
        trim($_POST['short_description']);


    /*
    | Generate URL-friendly slug
    */

    $slug = strtolower($slug);

    $slug = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $slug
    );

    $slug = trim(
        $slug,
        '-'
    );


    if (
        empty($name) ||
        empty($slug)
    ) {

        $error =
            "Category name and slug are required.";

        $editMode = true;

        $editCategory = [
            'id' => $category_id,
            'name' => $name,
            'slug' => $slug,
            'short_description' =>
                $short_description
        ];

    } else {


        /*
        | Check duplicate slug
        | excluding current category
        */

        $checkSql = "
            SELECT id
            FROM categories
            WHERE slug = ?
            AND id != ?
        ";

        $checkStmt =
            mysqli_prepare(
                $conn,
                $checkSql
            );

        mysqli_stmt_bind_param(
            $checkStmt,
            "si",
            $slug,
            $category_id
        );

        mysqli_stmt_execute($checkStmt);

        $checkResult =
            mysqli_stmt_get_result(
                $checkStmt
            );


        if (
            mysqli_num_rows($checkResult) > 0
        ) {

            $error =
                "This slug is already used by another category.";

            $editMode = true;

            $editCategory = [
                'id' => $category_id,
                'name' => $name,
                'slug' => $slug,
                'short_description' =>
                    $short_description
            ];

        } else {


            $updateSql = "
                UPDATE categories
                SET
                    name = ?,
                    slug = ?,
                    short_description = ?
                WHERE id = ?
            ";

            $updateStmt =
                mysqli_prepare(
                    $conn,
                    $updateSql
                );

            mysqli_stmt_bind_param(
                $updateStmt,
                "sssi",
                $name,
                $slug,
                $short_description,
                $category_id
            );


            if (
                mysqli_stmt_execute(
                    $updateStmt
                )
            ) {

                $success =
                    "Category updated successfully.";

            } else {

                $error =
                    "Failed to update category.";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| GET ALL CATEGORIES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        categories.id,
        categories.name,
        categories.slug,
        categories.short_description,
        COUNT(events.id) AS total_events
    FROM categories

    LEFT JOIN events
        ON categories.id = events.category_id

    GROUP BY
        categories.id,
        categories.name,
        categories.slug,
        categories.short_description

    ORDER BY categories.id ASC
";

$result =
    mysqli_query(
        $conn,
        $sql
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1"
>

<title>
Manage Categories | Admin
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
>


<style>

body {

    background: #f5f6fa;

}


.admin-container {

    max-width: 1150px;

    margin: 50px auto;

}


.card {

    border: none;

    border-radius: 12px;

}


.form-label {

    font-weight: 600;

}


.category-table {

    background: white;

}


</style>

</head>


<body>


<div class="admin-container px-3">


    <!-- Header -->

    <div
    class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                Manage Categories

            </h2>

            <p class="text-muted mb-0">

                Add, edit and manage your
                Our Work categories.

            </p>

        </div>


        <a
        href="../dashboard.php"
        class="btn btn-outline-secondary"
        >

            ← Dashboard

        </a>

    </div>


    <!-- Messages -->


    <?php if (!empty($success)) { ?>

        <div class="alert alert-success">

            <i class="fa-solid fa-circle-check me-2"></i>

            <?= htmlspecialchars($success); ?>

        </div>

    <?php } ?>


    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- Add / Edit Form -->

    <div class="card shadow-sm mb-4">

        <div class="card-body p-4">


            <div
            class="d-flex justify-content-between align-items-center mb-4"
            >

                <div>

                    <h4 class="fw-bold mb-1">

                        <?= $editMode
                            ? 'Edit Category'
                            : 'Add New Category'
                        ?>

                    </h4>

                    <p class="text-muted mb-0">

                        <?= $editMode
                            ? 'Update category information.'
                            : 'Create a new category for Our Work.'
                        ?>

                    </p>

                </div>


                <?php if ($editMode) { ?>

                    <a
                    href="index.php"
                    class="btn btn-sm btn-outline-secondary"
                    >

                        Cancel Edit

                    </a>

                <?php } ?>

            </div>


            <form method="POST">


                <?php if ($editMode) { ?>

                    <input
                    type="hidden"
                    name="category_id"
                    value="<?= $editCategory['id']; ?>"
                    >

                <?php } ?>


                <div class="row">


                    <!-- Name -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Category Name

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                        type="text"
                        name="name"
                        id="categoryName"
                        class="form-control"
                        value="<?= $editMode
                            ? htmlspecialchars(
                                $editCategory['name']
                            )
                            : ''
                        ?>"
                        placeholder="Example: Education for All"
                        required
                        >

                    </div>


                    <!-- Slug -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Slug

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                        type="text"
                        name="slug"
                        id="categorySlug"
                        class="form-control"
                        value="<?= $editMode
                            ? htmlspecialchars(
                                $editCategory['slug']
                            )
                            : ''
                        ?>"
                        placeholder="education-for-all"
                        required
                        >


                        <small class="text-muted">

                            Used in the category URL.

                        </small>

                    </div>


                </div>


                <!-- Description -->

                <div class="mb-3">

                    <label class="form-label">

                        Short Description

                    </label>


                    <textarea
                    name="short_description"
                    class="form-control"
                    rows="3"
                    placeholder="Brief description of this category..."
                    ><?= $editMode
                        ? htmlspecialchars(
                            $editCategory[
                                'short_description'
                            ]
                        )
                        : ''
                    ?></textarea>

                </div>


                <div class="text-end">


                    <?php if ($editMode) { ?>

                        <button
                        type="submit"
                        name="update_category"
                        class="btn btn-primary px-4"
                        >

                            <i
                            class="fa-solid fa-save me-1"
                            ></i>

                            Update Category

                        </button>

                    <?php } else { ?>

                        <button
                        type="submit"
                        name="add_category"
                        class="btn btn-primary px-4"
                        >

                            <i
                            class="fa-solid fa-plus me-1"
                            ></i>

                            Add Category

                        </button>

                    <?php } ?>


                </div>


            </form>


        </div>

    </div>


    <!-- Categories Table -->

    <div class="card shadow-sm">


        <div class="card-body p-0">


            <div class="table-responsive">


                <table
                class="table table-hover align-middle mb-0"
                >


                    <thead class="table-light">


                        <tr>

                            <th class="px-4">
                                Category
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                Events
                            </th>

                            <th>
                                Description
                            </th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php if (
                        mysqli_num_rows($result) > 0
                    ) { ?>


                        <?php while (
                            $category =
                            mysqli_fetch_assoc($result)
                        ) { ?>


                            <tr>


                                <!-- Name -->

                                <td class="px-4">

                                    <strong>

                                        <?= htmlspecialchars(
                                            $category['name']
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- Slug -->

                                <td>

                                    <code>

                                        <?= htmlspecialchars(
                                            $category['slug']
                                        ); ?>

                                    </code>

                                </td>


                                <!-- Event Count -->

                                <td>

                                    <span
                                    class="badge bg-primary"
                                    >

                                        <?= $category[
                                            'total_events'
                                        ]; ?>

                                    </span>

                                </td>


                                <!-- Description -->

                                <td
                                style="max-width:300px;"
                                >

                                    <?php if (
                                        !empty(
                                            $category[
                                                'short_description'
                                            ]
                                        )
                                    ) { ?>

                                        <?= htmlspecialchars(
                                            $category[
                                                'short_description'
                                            ]
                                        ); ?>

                                    <?php } else { ?>

                                        <span
                                        class="text-muted"
                                        >

                                            No description

                                        </span>

                                    <?php } ?>

                                </td>


                                <!-- Actions -->

                                <td>


                                    <div
                                    class="d-flex justify-content-center gap-2"
                                    >


                                        <!-- View -->

                                        <a
                                        href="../../our-work/category.php?slug=<?= urlencode(
                                            $category['slug']
                                        ); ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                        >

                                            View

                                        </a>


                                        <!-- Edit -->

                                        <a
                                        href="?edit=<?= $category['id']; ?>"
                                        class="btn btn-sm btn-outline-warning"
                                        >

                                            Edit

                                        </a>


                                        <!-- Delete -->

                                        <?php if (
                                            $category[
                                                'total_events'
                                            ] == 0
                                        ) { ?>


                                            <a
                                            href="?delete=<?= $category['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this category?');"
                                            >

                                                Delete

                                            </a>


                                        <?php } else { ?>


                                            <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            disabled
                                            title="This category has events"
                                            >

                                                Delete

                                            </button>


                                        <?php } ?>


                                    </div>


                                </td>


                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <tr>

                            <td
                            colspan="5"
                            class="text-center py-5"
                            >

                                <h5>

                                    No categories found.

                                </h5>

                                <p
                                class="text-muted"
                                >

                                    Add your first category above.

                                </p>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>


                </table>


            </div>


        </div>


    </div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| Automatic Slug
|--------------------------------------------------------------------------
*/

const categoryName =
    document.getElementById(
        "categoryName"
    );

const categorySlug =
    document.getElementById(
        "categorySlug"
    );


let slugManuallyChanged = false;


/*
| Detect manual slug editing
*/

categorySlug.addEventListener(
    "input",
    function() {

        slugManuallyChanged = true;

    }
);


/*
| Generate slug while adding
*/

categoryName.addEventListener(
    "input",
    function() {

        /*
        | Only automatically change slug
        | when creating a category.
        */

        <?php if (!$editMode) { ?>

            if (!slugManuallyChanged) {

                categorySlug.value =
                    categoryName.value
                    .toLowerCase()
                    .trim()
                    .replace(
                        /[^a-z0-9]+/g,
                        "-"
                    )
                    .replace(
                        /^-+|-+$/g,
                        ""
                    );

            }

        <?php } ?>

    }
);

</script>


</body>

</html>