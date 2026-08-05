<?php

session_start();

if(!isset($_SESSION['admin_id']))
{
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>

<html>

<head>

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h1>

Welcome,

<?php echo $_SESSION['admin_name']; ?>

</h1>

<a href="logout.php" class="btn btn-danger">

Logout

</a>

</div>

</body>

</html>