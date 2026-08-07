<?php

include __DIR__ . "/../config/db.php";

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");

?>

<nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">

<div class="container-fluid px-5">

    <!-- Logo -->

    <a class="navbar-brand fw-bold d-flex align-items-center"
       href="/ngo-website/index.php">

        <img src="/ngo-website/images/logo.png"
             height="70"
             class="me-2">

        <p class="mb-0">
            Sevartha Foundation
        </p>

    </a>

    <!-- Mobile Button -->

    <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">

        <span class="navbar-toggler-icon"></span>

    </button>

    <!-- Navbar -->

    <div class="collapse navbar-collapse justify-content-end"
         id="navbarNav">

        <ul class="navbar-nav align-items-center">

            <!-- ABOUT -->

            <li class="nav-item dropdown position-static">

                <a class="nav-link fw-semibold px-4 dropdown-toggle"
                   href="#"
                   data-bs-toggle="dropdown">

                    ABOUT US

                </a>

                <div class="dropdown-menu mega-menu p-4 border-0 shadow">

                    <div class="row">

                        <div class="col-md-6">

                            <a href="#" class="dropdown-item">

                                Home

                            </a>

                            <a href="#" class="dropdown-item">

                                People

                            </a>

                            <a href="#" class="dropdown-item">

                                Governance

                            </a>

                            <a href="#" class="dropdown-item">

                                Annual Reports

                            </a>

                        </div>

                        <div class="col-md-6">

                            <a href="#" class="dropdown-item">

                                Overview

                            </a>

                            <a href="#" class="dropdown-item">

                                Partners

                            </a>

                            <a href="#" class="dropdown-item">

                                Media Mentions

                            </a>

                        </div>

                    </div>

                </div>

            </li>

            <!-- OUR WORK -->

            <li class="nav-item dropdown position-static">

                <a class="nav-link fw-semibold px-4 dropdown-toggle"
                   href="#"
                   data-bs-toggle="dropdown">

                    OUR WORK

                </a>

                <div class="dropdown-menu mega-menu p-4 border-0 shadow">

                    <div class="row">

                        <?php

                        $count = 0;

                        while($navCategory = mysqli_fetch_assoc($categories))
                        {

                            if($count % 3 == 0)
                            {
                                echo '<div class="col-md-4">';
                            }

                        ?>

                        <a
                        href="/ngo-website/our-work/category.php?slug=<?= $navCategory['slug']; ?>"
                        class="dropdown-item">

                            <?= htmlspecialchars($navCategory['name']); ?>

                        </a>

                        <?php

                            $count++;

                            if($count % 3 == 0)
                            {
                                echo '</div>';
                            }

                        }

                        if($count % 3 != 0)
                        {
                            echo '</div>';
                        }

                        ?>

                    </div>

                </div>

            </li>

            <!-- GET INVOLVED -->

            <li class="nav-item">

                <a class="nav-link fw-semibold px-4"
                   href="#">

                    GET INVOLVED

                </a>

            </li>

            <!-- LOGIN -->

            <li class="nav-item ms-5">

                <a href="/ngo-website/admin/login.php"
                   class="btn btn-primary rounded-pill px-4">

                    LOGIN

                </a>

            </li>

            <!-- DONATE -->

            <li class="nav-item ms-2">

                <a href="#"
                   class="btn btn-outline-dark rounded-pill px-4">

                    DONATE

                </a>

            </li>

        </ul>

    </div>

</div>

</nav>