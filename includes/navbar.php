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
            href="/ngo-website/index.php"
        >
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
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- =================================================
             NAVIGATION
        ================================================== -->

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">

            <ul class="navbar-nav">

                <!-- =================================================
                     ABOUT US
                ================================================== -->

                <li class="nav-item nav-dropdown about-nav">
                    <a href="#" class="nav-link nav-dropdown-link">About Us</a>
                    <div class="nav-dropdown-menu about-menu">
                        <div class="dropdown-header">ABOUT SEVARTHA FOUNDATION</div>
                        <div class="dropdown-list">
                            <a href="/ngo-website/about-us/mission-vision-focus.php" class="dropdown-item-custom">
                                <span class="dropdown-icon"><i class="fa-solid fa-bullseye"></i></span>
                                <span class="dropdown-content">
                                    <strong>Mission, Vision & Focus</strong>
                                    <small>What drives our work</small>
                                </span>
                            </a>
                            <a href="/ngo-website/about-us/founders.php" class="dropdown-item-custom">
                                <span class="dropdown-icon"><i class="fa-solid fa-users"></i></span>
                                <span class="dropdown-content">
                                    <strong>Our Founders</strong>
                                    <small>The people behind Sevartha</small>
                                </span>
                            </a>
                            <a href="/ngo-website/about-us/our-teams.php" class="dropdown-item-custom">
                                <span class="dropdown-icon"><i class="fa-solid fa-people-group"></i></span>
                                <span class="dropdown-content">
                                    <strong>Our Team</strong>
                                    <small>Meet our dedicated team</small>
                                </span>
                            </a>
                            <a href="/ngo-website/about-us/accountability-transparency.php" class="dropdown-item-custom">
                                <span class="dropdown-icon"><i class="fa-solid fa-chart-line"></i></span>
                                <span class="dropdown-content">
                                    <strong>Financial Transparency</strong>
                                    <small>Our commitment to accountability</small>
                                </span>
                            </a>
                            <a href="/ngo-website/connect-with-us/contact.php" class="dropdown-item-custom">
                                <span class="dropdown-icon"><i class="fa-solid fa-envelope"></i></span>
                                <span class="dropdown-content">
                                    <strong>Contact Us</strong>
                                    <small>Get in touch with us</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </li>

                <!-- =================================================
                     OUR WORK
                ================================================== -->

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-link">Our Work</a>
                    <div class="nav-dropdown-menu work-menu">
                        <div class="work-grid">
                            <?php while ($navCategory = mysqli_fetch_assoc($categories)) { ?>
                                <a href="/ngo-website/our-work/category.php?slug=<?= urlencode($navCategory['slug']); ?>" class="work-item">
                                    <?= htmlspecialchars($navCategory['name']); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </li>

                <!-- =================================================
                     IMPACT STORIES
                ================================================== -->

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-link">Impact Stories</a>
                    <div class="nav-dropdown-menu">
                        <a href="impact-stories.php" class="simple-dropdown-item">All Impact Stories</a>
                        <a href="success-stories.php" class="simple-dropdown-item">Success Stories</a>
                        <a href="community-stories.php" class="simple-dropdown-item">Community Stories</a>
                    </div>
                </li>

                <!-- =================================================
                     TESTIMONIALS
                ================================================== -->

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-link">Testimonials</a>
                    <div class="nav-dropdown-menu">
                        <a href="testimonials.php" class="simple-dropdown-item">All Testimonials</a>
                        <a href="beneficiary-testimonials.php" class="simple-dropdown-item">Beneficiaries</a>
                        <a href="volunteer-testimonials.php" class="simple-dropdown-item">Volunteers</a>
                    </div>
                </li>

                <!-- =================================================
                     NEWS
                ================================================== -->

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-link">News</a>
                    <div class="nav-dropdown-menu">
                        <a href="news.php" class="simple-dropdown-item">Latest News</a>
                        <a href="media.php" class="simple-dropdown-item">Media Coverage</a>
                        <a href="press-releases.php" class="simple-dropdown-item">Press Releases</a>
                    </div>
                </li>

                <!-- =================================================
                     CONTACT US
                ================================================== -->

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-link">Connect With Us</a>
                    <div class="nav-dropdown-menu">
                        <a href="/ngo-website/connect-with-us/contact.php" class="simple-dropdown-item">Contact Us</a>
                        <a href="/ngo-website/connect-with-us/volunteer.php" class="simple-dropdown-item">Volunteer With Us</a>
                        <a href="/ngo-website/connect-with-us/location.php" class="simple-dropdown-item">Our Locations</a>
                    </div>
                </li>

                <!-- =================================================
                     LOGIN
                ================================================== -->

                <li class="nav-item login-item">
                    <a href="/ngo-website/admin/login.php" class="nav-link login-link">Login</a>
                </li>

                <!-- =================================================
                     DONATE
                ================================================== -->

                <li class="nav-item donate-item">
                    <a href="donate/donate.php" class="donate-button">
                        Donate <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>
