<?php

session_start();


/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id'])) {

    header("Location: dashboard.php");
    exit();

}


include "../config/db.php";


$error = "";


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Get Admin
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT *
            FROM admin
            WHERE username = ?
            LIMIT 1
        ";


        $stmt = mysqli_prepare(
            $conn,
            $sql
        );


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );


        mysqli_stmt_execute($stmt);


        $result = mysqli_stmt_get_result($stmt);


        /*
        |--------------------------------------------------------------------------
        | Check Admin
        |--------------------------------------------------------------------------
        */

        if (mysqli_num_rows($result) === 1) {

            $admin = mysqli_fetch_assoc($result);


            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            if (
                password_verify(
                    $password,
                    $admin['password']
                )
            ) {


                /*
                |--------------------------------------------------------------------------
                | Regenerate Session ID
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);


                $_SESSION['admin_id'] =
                    $admin['id'];


                $_SESSION['admin_name'] =
                    $admin['full_name'];


                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: dashboard.php"
                );

                exit();


            } else {

                $error =
                    "Invalid username or password.";

            }


        } else {

            $error =
                "Invalid username or password.";

        }


        mysqli_stmt_close($stmt);

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

    <title>
        Admin Login | Sevartha Foundation
    </title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <!-- =====================================================
         LOGIN CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../css/admin/login.css"
    >
    <link
    rel="stylesheet"
    href="../css/scroll-content.css"
>
</head>

<body class="admin-login-page">


<div class="container-fluid min-vh-100">

    <div class="row min-vh-100">


        <!-- =================================================
             LOGIN IMAGE
        ================================================== -->

        <div class="col-lg-6 d-none d-lg-block p-0">

            <div class="login-image">

                <img
                    src="../static_image.php?name=loginpage.png"
                    alt="Sevartha Foundation"
                >

            </div>

        </div>


        <!-- =================================================
             LOGIN
        ================================================== -->

        <div
            class="col-lg-6 d-flex align-items-center justify-content-center"
        >

            <div class="login-card">


                <!-- Heading -->

                <h2 class="text-center">

                    Welcome Back

                </h2>


                <p class="login-subtitle">

                    Sign in to your admin account

                </p>


                <!-- Error -->

                <?php if ($error !== "") { ?>

                    <div class="login-error">

                        <i
                            class="fa-solid fa-circle-exclamation"
                        ></i>

                        <?= htmlspecialchars($error); ?>

                    </div>

                <?php } ?>


                <!-- Login Form -->

                <form
                    method="POST"
                    action=""
                >


                    <!-- Username -->

                    <div class="login-form-group">

                        <label for="username">

                            Username

                        </label>

                        <div class="input-wrapper">

                            <i
                                class="fa-solid fa-user"
                            ></i>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                placeholder="Enter your username"
                                autocomplete="username"
                                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Password -->

                    <div class="login-form-group">

                        <label for="password">

                            Password

                        </label>

                        <div class="input-wrapper">

                            <i
                                class="fa-solid fa-lock"
                            ></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                    </div>


                    <!-- Login Button -->

                    <button
                        type="submit"
                        name="login"
                        class="login-button"
                    >

                        <i
                            class="fa-solid fa-right-to-bracket"
                        ></i>

                        Login

                    </button>

                </form>


                <!-- Footer Text -->

                <div class="login-footer">

                    Sevartha Foundation
                    <span>•</span>
                    Admin Panel

                </div>


            </div>

        </div>


    </div>

</div>

<script src="../js/scroll-content.js"></script>
</body>

</html>