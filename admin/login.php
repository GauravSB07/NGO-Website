<?php

session_start();
include '../config/db.php';

if(isset($_POST['login']))
{

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admin WHERE username=?";

    $stmt = mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param($stmt,"s",$username);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)==1)
    {

        $admin = mysqli_fetch_assoc($result);

        if($password == $admin['password'])
        {

            $_SESSION['admin_id']=$admin['id'];
            $_SESSION['admin_name']=$admin['full_name'];

            header("Location: dashboard.php");
            exit();

        }
        else
        {
            $error="Incorrect Password!";
        }

    }
    else
    {
        $error="Username not found!";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/navbar.css">


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


</head>

<body class="login-page">

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-0">

<div class="row g-0 vh-100">

    <!-- Image -->

    <div class="col-lg-7">

        <div class="login-image">

            <img src="../images/login.jpg" class="img-fluid">

        </div>

    </div>

    <!-- Login -->

    <div class="col-lg-5 d-flex align-items-center justify-content-center">

        <div class="login-card">

            <h2 class="text-center mb-4">

                Welcome Back

            </h2>

            <?php if(isset($error)){ ?>

            <div class="alert alert-danger">

                <?= $error; ?>

            </div>

            <?php } ?>

            <form method="POST" action="">

                <div class="mb-3">

                    <input
                    type="text"
                    class="form-control"
                    name="username"
                    placeholder="Username">

                </div>

                <div class="mb-3">

                    <input
                    type="password"
                    class="form-control"
                    name="password"
                    placeholder="Password">

                </div>

                <button type="submit"
                        name="login"
                        class="btn btn-primary w-100">

                    Login

                </button>

                <div class="text-center mt-3">

                    <a href="#">

                        Forgot Password?

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</div>

</body>

</html>