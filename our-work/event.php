<?php

include "../config/db.php";

if(!isset($_GET['id']))
{
    die("Event not found.");
}

$id = $_GET['id'];

/* Event Details */

$sql = "SELECT * FROM events WHERE id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Event not found.");
}

$event = mysqli_fetch_assoc($result);

/* Gallery Images */

$sql2 = "SELECT * FROM event_images WHERE event_id=?";

$stmt2 = mysqli_prepare($conn,$sql2);

mysqli_stmt_bind_param($stmt2,"i",$id);

mysqli_stmt_execute($stmt2);

$gallery = mysqli_stmt_get_result($stmt2);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title><?= $event['title']; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/style.css">

<style>

.hero{

    height:450px;
    overflow:hidden;

}

.hero img{

    width:100%;
    height:100%;
    object-fit:cover;

}

.gallery img{

    width:100%;
    height:250px;
    object-fit:cover;
    border-radius:12px;
    cursor:pointer;
    transition:.3s;

}

.gallery img:hover{

    transform:scale(1.03);

}

.info-box{

    background:#f8f9fa;
    padding:20px;
    border-radius:12px;

}

</style>

</head>

<body>

<?php include "../includes/navbar.php"; ?>

<!-- Hero -->

<div class="hero">

<img src="../uploads/events/<?= $event['cover_image']; ?>">

</div>

<div class="container py-5">

<h1>

<?= $event['title']; ?>

</h1>

<div class="row mt-4">

<div class="col-lg-8">

<h3>

About This Event

</h3>

<p>

<?= nl2br($event['description']); ?>

</p>

</div>

<div class="col-lg-4">

<div class="info-box">

<p>

📍 <strong>Location:</strong>

<?= $event['location']; ?>

</p>

<p>

📅 <strong>Date:</strong>

<?= $event['event_date']; ?>

</p>

<?php if(!empty($event['supported_by'])){ ?>

<p>

🤝 <strong>Supported By:</strong>

<?= $event['supported_by']; ?>

</p>

<?php } ?>

<?php if(!empty($event['beneficiaries'])){ ?>

<p>

👥 <strong>Beneficiaries:</strong>

<?= $event['beneficiaries']; ?>

</p>

<?php } ?>

</div>

</div>

</div>

<hr class="my-5">

<h2 class="mb-4">

Gallery

</h2>

<div class="row gallery">

<?php while($image=mysqli_fetch_assoc($gallery)){ ?>

<div class="col-lg-4 mb-4">

<img
src="../uploads/events/<?= $image['image_path']; ?>"
class="img-fluid">

</div>

<?php } ?>

</div>

<a href="javascript:history.back()" class="btn btn-primary mt-3">

← Back

</a>

</div>

<?php include "../includes/footer.php"; ?>

</body>

</html>