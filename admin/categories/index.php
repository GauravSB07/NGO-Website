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
   VARIABLES
========================================================= */

$editMode = false;

$editCategory = null;

$error = "";

$success = "";


/* =========================================================
   DELETE CATEGORY
========================================================= */

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $category_id =
        (int) $_GET['delete'];


    /* Check if category has events */

    $checkSql = "
        SELECT COUNT(*) AS total
        FROM events
        WHERE category_id = ?
    ";


    $checkStmt =
        mysqli_prepare(
            $conn,
            $checkSql
        );


    mysqli_stmt_bind_param(
        $checkStmt,
        "i",
        $category_id
    );


    mysqli_stmt_execute(
        $checkStmt
    );


    $checkResult =
        mysqli_stmt_get_result(
            $checkStmt
        );


    $eventData =
        mysqli_fetch_assoc(
            $checkResult
        );


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


        if (
            mysqli_stmt_execute(
                $deleteStmt
            )
        ) {

            $success =
                "Category deleted successfully.";

        } else {

            $error =
                "Failed to delete category.";

        }

    }

}


/* =========================================================
   LOAD CATEGORY FOR EDIT
========================================================= */

if (
    isset($_GET['edit']) &&
    is_numeric($_GET['edit'])
) {

    $category_id =
        (int) $_GET['edit'];


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


    mysqli_stmt_execute(
        $editStmt
    );


    $editResult =
        mysqli_stmt_get_result(
            $editStmt
        );


    if (
        mysqli_num_rows(
            $editResult
        ) == 1
    ) {

        $editCategory =
            mysqli_fetch_assoc(
                $editResult
            );

        $editMode = true;

    } else {

        $error =
            "Category not found.";

    }

}


/* =========================================================
   ADD CATEGORY
========================================================= */

if (
    isset($_POST['add_category'])
) {

    $name =
        trim($_POST['name']);

    $slug =
        trim($_POST['slug']);

    $short_description =
        trim(
            $_POST['short_description']
        );


    /* Generate URL-friendly slug */

    $slug =
        strtolower($slug);

    $slug =
        preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

    $slug =
        trim(
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


        /* Check duplicate slug */

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


        mysqli_stmt_execute(
            $checkStmt
        );


        $checkResult =
            mysqli_stmt_get_result(
                $checkStmt
            );


        if (
            mysqli_num_rows(
                $checkResult
            ) > 0
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


/* =========================================================
   UPDATE CATEGORY
========================================================= */

if (
    isset($_POST['update_category'])
) {

    $category_id =
        (int) $_POST['category_id'];

    $name =
        trim($_POST['name']);

    $slug =
        trim($_POST['slug']);

    $short_description =
        trim(
            $_POST['short_description']
        );


    /* Generate URL-friendly slug */

    $slug =
        strtolower($slug);

    $slug =
        preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

    $slug =
        trim(
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

            'id' =>
                $category_id,

            'name' =>
                $name,

            'slug' =>
                $slug,

            'short_description' =>
                $short_description

        ];

    } else {


        /* Check duplicate slug
           excluding current category */

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


        mysqli_stmt_execute(
            $checkStmt
        );


        $checkResult =
            mysqli_stmt_get_result(
                $checkStmt
            );


        if (
            mysqli_num_rows(
                $checkResult
            ) > 0
        ) {

            $error =
                "This slug is already used by another category.";

            $editMode = true;

            $editCategory = [

                'id' =>
                    $category_id,

                'name' =>
                    $name,

                'slug' =>
                    $slug,

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

                $editMode = false;

            } else {

                $error =
                    "Failed to update category.";

            }

        }

    }

}


/* =========================================================
   GET ALL CATEGORIES
========================================================= */

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

    ORDER BY
        categories.id ASC
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
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Manage Categories | Admin
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
$activeNav = 'categories';
include __DIR__ . '/../includes/navbar.php';
?>


<!-- =========================================================
     MAIN CONTENT
========================================================== -->

<main class="admin-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="admin-header">

        <div>

            <h1>
                Manage Categories
            </h1>

            <p>
                Add, edit and manage your Our Work categories.
            </p>

        </div>


        <a
            href="../dashboard.php"
            class="admin-btn-secondary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Dashboard

        </a>

    </div>


    <!-- =====================================================
         MESSAGES
    ====================================================== -->

    <?php if (
        !empty($success)
    ) { ?>

        <div class="admin-alert admin-alert-success">

            <i
                class="fa-solid fa-circle-check me-2"
            ></i>

            <?= htmlspecialchars(
                $success
            ); ?>

        </div>

    <?php } ?>


    <?php if (
        !empty($error)
    ) { ?>

        <div class="admin-alert">

            <i
                class="fa-solid fa-circle-exclamation me-2"
            ></i>

            <?= htmlspecialchars(
                $error
            ); ?>

        </div>

    <?php } ?>


    <!-- =====================================================
         ADD / EDIT FORM
    ====================================================== -->

    <div class="admin-form-card">


        <div class="admin-section-header">


            <div>

                <h3>

                    <?= $editMode
                        ? 'Edit Category'
                        : 'Add New Category'
                    ?>

                </h3>


                <p>

                    <?= $editMode
                        ? 'Update category information.'
                        : 'Create a new category for Our Work.'
                    ?>

                </p>

            </div>


            <?php if (
                $editMode
            ) { ?>

                <a
                    href="index.php"
                    class="admin-btn-secondary"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Cancel Edit

                </a>

            <?php } ?>


        </div>


        <form method="POST">


            <?php if (
                $editMode
            ) { ?>

                <input
                    type="hidden"
                    name="category_id"
                    value="<?= $editCategory['id']; ?>"
                >

            <?php } ?>


            <!-- NAME + SLUG -->

            <div class="row">


                <div class="col-md-6 mb-4">

                    <label
                        for="categoryName"
                        class="admin-form-label"
                    >

                        Category Name

                        <span class="admin-required">
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


                <div class="col-md-6 mb-4">

                    <label
                        for="categorySlug"
                        class="admin-form-label"
                    >

                        Slug

                        <span class="admin-required">
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


                    <div class="admin-image-note">

                        Used in the category URL.

                    </div>

                </div>


            </div>


            <!-- DESCRIPTION -->

            <div class="mb-4">

                <label
                    for="shortDescription"
                    class="admin-form-label"
                >

                    Short Description

                </label>


                <textarea
                    name="short_description"
                    id="shortDescription"
                    class="form-control"
                    rows="4"
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


            <!-- SUBMIT -->

            <div class="admin-form-actions">


                <?php if (
                    $editMode
                ) { ?>

                    <button
                        type="submit"
                        name="update_category"
                        class="admin-btn-primary"
                    >

                        <i
                            class="fa-solid fa-floppy-disk"
                        ></i>

                        Update Category

                    </button>

                <?php } else { ?>

                    <button
                        type="submit"
                        name="add_category"
                        class="admin-btn-primary"
                    >

                        <i
                            class="fa-solid fa-plus"
                        ></i>

                        Add Category

                    </button>

                <?php } ?>


            </div>


        </form>


    </div>


    <!-- =====================================================
         CATEGORIES TABLE
    ====================================================== -->

    <div class="admin-table-card mt-4">


        <div class="table-responsive">


            <table class="admin-table">


                <thead>

                    <tr>

                        <th>
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
                        mysqli_fetch_assoc(
                            $result
                        )
                    ) { ?>


                        <tr>


                            <!-- CATEGORY -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $category['name']
                                    ); ?>

                                </strong>

                            </td>


                            <!-- SLUG -->

                            <td>

                                <code class="admin-code">

                                    <?= htmlspecialchars(
                                        $category['slug']
                                    ); ?>

                                </code>

                            </td>


                            <!-- EVENT COUNT -->

                            <td>

                                <span
                                    class="admin-badge"
                                >

                                    <?= $category[
                                        'total_events'
                                    ]; ?>

                                    <?= $category[
                                        'total_events'
                                    ] == 1
                                        ? 'Event'
                                        : 'Events'
                                    ?>

                                </span>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <?php if (
                                    !empty(
                                        $category[
                                            'short_description'
                                        ]
                                    )
                                ) { ?>

                                    <div
                                        class="admin-description-cell"
                                    >

                                        <?= htmlspecialchars(
                                            $category[
                                                'short_description'
                                            ]
                                        ); ?>

                                    </div>

                                <?php } else { ?>

                                    <span
                                        class="text-muted"
                                    >

                                        No description

                                    </span>

                                <?php } ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div
                                    class="admin-table-actions"
                                >


                                    <!-- VIEW -->

                                    <a
                                        href="../../our-work/category.php?slug=<?= urlencode(
                                            $category['slug']
                                        ); ?>"
                                        target="_blank"
                                        class="admin-action-btn view"
                                        title="View Category"
                                    >

                                        <i
                                            class="fa-solid fa-eye"
                                        ></i>

                                    </a>


                                    <!-- EDIT -->

                                    <a
                                        href="?edit=<?= $category['id']; ?>"
                                        class="admin-action-btn edit"
                                        title="Edit Category"
                                    >

                                        <i
                                            class="fa-solid fa-pen"
                                        ></i>

                                    </a>


                                    <!-- DELETE -->

                                    <?php if (
                                        $category[
                                            'total_events'
                                        ] == 0
                                    ) { ?>

                                        <a
                                            href="?delete=<?= $category['id']; ?>"
                                            class="admin-action-btn delete"
                                            title="Delete Category"
                                            onclick="return confirm('Are you sure you want to delete this category?');"
                                        >

                                            <i
                                                class="fa-solid fa-trash"
                                            ></i>

                                        </a>

                                    <?php } else { ?>

                                        <button
                                            type="button"
                                            class="admin-action-btn disabled"
                                            disabled
                                            title="This category has events"
                                        >

                                            <i
                                                class="fa-solid fa-lock"
                                            ></i>

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
                            class="admin-empty-table"
                        >

                            <div
                                class="admin-empty-icon"
                            >

                                <i
                                    class="fa-solid fa-layer-group"
                                ></i>

                            </div>


                            <h3>
                                No Categories Found
                            </h3>


                            <p>
                                Add your first category above.
                            </p>

                        </td>

                    </tr>


                <?php } ?>


                </tbody>


            </table>


        </div>


    </div>


</main>


<!-- =========================================================
     AUTOMATIC SLUG
========================================================== -->

<script>

const categoryName =
    document.getElementById(
        "categoryName"
    );


const categorySlug =
    document.getElementById(
        "categorySlug"
    );


let slugManuallyChanged =
    false;


/*
|--------------------------------------------------------------------------
| Detect manual slug editing
|--------------------------------------------------------------------------
*/

categorySlug.addEventListener(
    "input",
    function () {

        slugManuallyChanged = true;

    }
);


/*
|--------------------------------------------------------------------------
| Generate slug while adding
|--------------------------------------------------------------------------
*/

categoryName.addEventListener(
    "input",
    function () {


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