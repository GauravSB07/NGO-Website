<?php
include '../config/db.php';

if (!isset($_GET['slug'])) {
    die("Category not found.");
}

$slug = $_GET['slug'];

// Get Category
$categoryQuery = "SELECT * FROM categories WHERE slug = ?";
$stmt = mysqli_prepare($conn, $categoryQuery);
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$categoryResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($categoryResult) == 0) {
    die("Category not found.");
}

$category = mysqli_fetch_assoc($categoryResult);

// Get Events
$eventQuery = "SELECT * FROM events WHERE category_id = ? AND status='Active' ORDER BY event_date DESC";
$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "i", $category['id']);
mysqli_stmt_execute($stmt);
$events = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <title><?= $category['name']; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php include "../includes/navbar.php"; ?>

<!-- Hero -->

<div class="bg-primary text-white py-5">

    <div class="container">

        <h1><?= $category['name']; ?></h1>

        <p><?= $category['short_description']; ?></p>

    </div>

</div>

<!-- Events -->

<div class="container py-5">

    <h2 class="mb-4">Our Events</h2>

    <div class="row">

        <?php while($event=mysqli_fetch_assoc($events)){ ?>

        <div class="col-lg-4 mb-4">

            <div class="card shadow-sm h-100">

                <img
                    src="../uploads/events/<?= $event['cover_image']; ?>"
                    class="card-img-top"
                    style="height:250px;object-fit:cover;">

                <div class="card-body">

                    <h5><?= $event['title']; ?></h5>

                    <p>

                        <?= substr($event['description'],0,120); ?>...

                    </p>

                    <p>

                        <strong>Location:</strong>

                        <?= $event['location']; ?>

                    </p>

                    <a
                    href="event.php?id=<?= $event['id']; ?>"
                    class="btn btn-primary">

                        View Event

                    </a>

                </div>

            </div>

        </div>

        <?php } ?>

    </div>

</div>

<?php include "../includes/footer.php"; ?>

</body>

</html>