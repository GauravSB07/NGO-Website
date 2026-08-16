<?php

/* =========================================================
   DATABASE CONNECTION
========================================================= */

if (!isset($conn)) {
    include __DIR__ . '/../config/db.php';
}


/* =========================================================
   GET CATEGORIES FOR OUR WORK
========================================================= */

$categoriesQuery = "
    SELECT id, name, slug
    FROM categories
    ORDER BY id ASC
";

$categories = mysqli_query($conn, $categoriesQuery);

if (!$categories) {
    die("Category query failed: " . mysqli_error($conn));
}

?>

<nav class="navbar">

    <div class="container-fluid">

        <!-- =================================================
             BRAND
        ================================================== -->

        <a
            class="ngo-brand"
            href="/ngo-website/index.php">
            <span class="brand-title">Sevartha</span>
            <span class="brand-subtitle">Foundation</span>
        </a>


        <!-- =================================================
             MOBILE TOGGLE
        ================================================== -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- =================================================
             NAVIGATION
        ================================================== -->

        <div
            class="collapse navbar-collapse justify-content-end"
            id="navbarNav">

            <ul class="navbar-nav">

                <!-- HOME -->
                <li class="nav-item">
                    <a class="nav-link" href="/NGO-Website/index.php">
                        <i class="fa-solid fa-house"></i>
                        Home
                    </a>
                </li>

                <!-- =================================================
                     ABOUT US
                ================================================== -->

                <li class="nav-item nav-dropdown about-nav">

                    <a
                        href="#"
                        class="nav-link nav-dropdown-link">
                        About Us
                    </a>

                    <div class="nav-dropdown-menu about-menu">

                        <div class="dropdown-header">
                            ABOUT SEVARTHA FOUNDATION
                        </div>

                        <div class="dropdown-list">

                            <a
                                href="/ngo-website/about-us/mission-vision-focus.php"
                                class="dropdown-item-custom">
                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-bullseye"></i>
                                </span>

                                <span class="dropdown-content">
                                    <strong>Mission, Vision & Focus</strong>
                                    <small>What drives our work</small>
                                </span>
                            </a>


                            <a
                                href="/ngo-website/about-us/founders.php"
                                class="dropdown-item-custom">
                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-users"></i>
                                </span>

                                <span class="dropdown-content">
                                    <strong>Our Founders</strong>
                                    <small>The people behind Sevartha</small>
                                </span>
                            </a>


                            <a
                                href="/ngo-website/about-us/our-teams.php"
                                class="dropdown-item-custom">
                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-people-group"></i>
                                </span>

                                <span class="dropdown-content">
                                    <strong>Our Team</strong>
                                    <small>Meet our dedicated team</small>
                                </span>
                            </a>


                            <a
                                href="/ngo-website/about-us/accountability-transparency.php"
                                class="dropdown-item-custom">
                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-chart-line"></i>
                                </span>

                                <span class="dropdown-content">
                                    <strong>Financial Transparency</strong>
                                    <small>Our commitment to accountability</small>
                                </span>
                            </a>

                        </div>

                    </div>

                </li>


                <!-- =========================================================
     OUR WORK
========================================================= -->

                <li class="nav-item nav-dropdown">

                    <a class="nav-link dropdown-toggle" href="#">
                        Our Work
                    </a>

                    <div class="nav-dropdown-menu work-menu">

                        <div class="dropdown-header">
                            OUR WORK
                        </div>

                        <div class="dropdown-list">


                            <!-- EDUCATION -->
                            <a href="../our-work/category.php?slug=education-for-all"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>Education For All</strong>

                                    <small>
                                        Explore our work and initiatives
                                    </small>

                                </span>

                            </a>


                            <!-- HUNGER AND POVERTY -->
                            <a href="../our-work/category.php?slug=hunger-and-poverty"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-bowl-food"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>Hunger and Poverty</strong>

                                    <small>
                                        Explore our work and initiatives
                                    </small>

                                </span>

                            </a>


                            <!-- HEALTHCARE -->
                            <a href="../our-work/category.php?slug=healthcare-and-medical-relief"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-heart-pulse"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>Healthcare and Medical Relief</strong>

                                    <small>
                                        Explore our work and initiatives
                                    </small>

                                </span>

                            </a>


                            <!-- ELDERLY -->
                            <a href="../our-work/category.php?slug=dignity-for-the-elderly"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-person-cane"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>Dignity for The Elderly</strong>

                                    <small>
                                        Explore our work and initiatives
                                    </small>

                                </span>

                            </a>


                            <!-- AWARENESS -->
                            <a href="../our-work/category.php?slug=awareness-programmes"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>Awareness Programmes</strong>

                                    <small>
                                        Explore our work and initiatives
                                    </small>

                                </span>

                            </a>

                        </div>

                    </div>

                </li>

                <!-- =================================================
                     ANNUAL EVENTS
                ================================================== -->

                <li class="nav-item nav-dropdown">

                    <a
                        href="#"
                        class="nav-link nav-dropdown-link">
                        Annual Events
                    </a>

                    <div class="nav-dropdown-menu annual-event-menu">

                        <div class="dropdown-header">
                            ANNUAL EVENTS
                        </div>

                        <div class="dropdown-list">

                            <a
                                href="/ngo-website/annual_event.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Annual Events
                                    </strong>

                                    <small>
                                        Explore our annual events and activities
                                    </small>

                                </span>

                            </a>

                        </div>

                    </div>

                </li>


                <!-- =================================================
                     TESTIMONIALS
                ================================================== -->

                <li class="nav-item nav-dropdown">

                    <a
                        href="#"
                        class="nav-link nav-dropdown-link">
                        Testimonials
                    </a>

                    <div class="nav-dropdown-menu testimonials-menu">

                        <div class="dropdown-header">
                            VOICES OF SEVARTHA
                        </div>

                        <div class="dropdown-list">


                            <a
                                href="/ngo-website/testimonials/institutional.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-building-columns"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Institutional Appreciation
                                    </strong>

                                    <small>
                                        Recognition from institutions and organisations
                                    </small>

                                </span>

                            </a>


                            <a
                                href="/ngo-website/testimonials/community.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-people-group"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Community & Partner Voices
                                    </strong>

                                    <small>
                                        Experiences from our communities and partners
                                    </small>

                                </span>

                            </a>


                            <a
                                href="/ngo-website/testimonials/news.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-newspaper"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        In the News
                                    </strong>

                                    <small>
                                        Media coverage and public recognition
                                    </small>

                                </span>

                            </a>

                        </div>

                    </div>

                </li>


                <!-- =================================================
                     CONNECT WITH US
                ================================================== -->

                <li class="nav-item nav-dropdown">

                    <a
                        href="#"
                        class="nav-link nav-dropdown-link">
                        Connect With Us
                    </a>

                    <div class="nav-dropdown-menu connect-menu">

                        <div class="dropdown-header">
                            CONNECT WITH US
                        </div>

                        <div class="dropdown-list">


                            <a
                                href="/ngo-website/connect-with-us/contact.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Contact Us
                                    </strong>

                                    <small>
                                        Get in touch with Sevartha Foundation
                                    </small>

                                </span>

                            </a>


                            <a
                                href="/ngo-website/connect-with-us/volunteer.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-hand-holding-heart"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Volunteer With Us
                                    </strong>

                                    <small>
                                        Become a part of our mission
                                    </small>

                                </span>

                            </a>


                            <a
                                href="/ngo-website/connect-with-us/location.php"
                                class="dropdown-item-custom">

                                <span class="dropdown-icon">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>

                                <span class="dropdown-content">

                                    <strong>
                                        Our Locations
                                    </strong>

                                    <small>
                                        Find our offices and locations
                                    </small>

                                </span>

                            </a>

                        </div>

                    </div>

                </li>


                <!-- =================================================
                     LOGIN
                ================================================== -->

                <li class="nav-item login-item">

                    <a
                        href="/ngo-website/admin/login.php"
                        class="nav-link login-link">
                        Login
                    </a>

                </li>


                <!-- =================================================
                     DONATE
                ================================================== -->

                <li class="nav-item donate-item">

                    <a
                        href="/ngo-website/donate/donate.php"
                        class="donate-button">
                        Donate
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>