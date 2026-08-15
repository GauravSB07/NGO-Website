<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sevartha Foundation</title>


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">


    <!-- Navbar CSS -->
    <link rel="stylesheet" href="css/navbar.css">


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

</head>


<body>


<?php include 'includes/navbar.php'; ?>



<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="hero-section">

    <!-- Background slideshow -->
    <div class="hero-bg">
        <img class="hero-slide active" src="static_image.php?name=homepage_image1.png" alt="Sevartha Foundation">
        <img class="hero-slide" src="static_image.php?name=homepage_image2.png" alt="Sevartha Foundation">
        <img class="hero-slide" src="static_image.php?name=homepage_image3.png" alt="Sevartha Foundation">
        <img class="hero-slide" src="static_image.php?name=homepage_image4.png" alt="Sevartha Foundation">
        <img class="hero-slide" src="static_image.php?name=homepage_image5.png" alt="Sevartha Foundation">
    </div>

    <!-- Dark overlay -->
    <div class="hero-overlay"></div>

    <!-- HERO CONTENT -->
    <div class="hero-wrap">

        <div class="hero-copy fade-in-left">

            <h1 class="hero-title">
                ACTIVELY SEEKING<br>
                OPPORTUNITIES TO<br>
                MAKE A DIFFERENCE.
            </h1>

            <p class="hero-subtitle">
                Education <span>•</span> Healthcare <span>•</span> Hope
            </p>

            <p class="hero-description">
                Creating meaningful opportunities and building stronger
                communities through education, healthcare and compassionate action.
            </p>

            <div class="hero-button-row">
                <a href="contact.php" class="hero-cta">
                    Join The Movement
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="programs.php" class="hero-learn">
                    Explore Programs
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

        </div>

    </div>

</section>

<!-- =========================================================
     STORY / ABOUT SECTION
========================================================= -->

<section class="story-band">

    <div class="story-band__inner fade-in-up">


        <div class="story-intro">

            <h2 class="story-band__title">

                Empowering Lives,
                <span>Transforming Communities</span>

            </h2>


            <p class="story-band__text">

                The Sevartha Foundation is dedicated to transforming lives
                and uplifting underserved communities. Our mission is to
                bridge economic disparities by extending support to those
                in need. From education and medical relief to assisting the
                elderly and impoverished, we work tirelessly to create a
                more equitable society.

            </p>

        </div>



        <!-- STORY CARDS -->

        <div class="story-band__grid">


            <!-- CARD 1 -->

            <article class="story-card fade-in-up">

                <div class="story-card-icon">

                    <i class="fa-solid fa-heart"></i>

                </div>


                <div class="story-card-number">

                    <span
                        class="counter"
                        data-target="5000"
                    >
                        0
                    </span>

                    <span class="story-card-unit">
                        +
                    </span>

                </div>


                <h3>
                    Lives Transformed
                </h3>


                <p>

                    Providing essential support, education, healthcare,
                    and livelihood opportunities to those in need.

                </p>

            </article>



            <!-- CARD 2 -->

            <article class="story-card fade-in-up">

                <div class="story-card-icon">

                    <i class="fa-solid fa-hands-holding-child"></i>

                </div>


                <div class="story-card-number">

                    <span
                        class="counter"
                        data-target="25"
                    >
                        0
                    </span>

                    <span class="story-card-unit">
                        +
                    </span>

                </div>


                <h3>
                    Successful Projects
                </h3>


                <p>

                    Executing diverse initiatives focused on education,
                    healthcare, women empowerment, and disaster relief.

                </p>

            </article>



            <!-- CARD 3 -->

            <article class="story-card fade-in-up">

                <div class="story-card-icon">

                    <i class="fa-solid fa-people-group"></i>

                </div>


                <div class="story-card-number">

                    <span
                        class="counter"
                        data-target="250"
                    >
                        0
                    </span>

                    <span class="story-card-unit">
                        +
                    </span>

                </div>


                <h3>
                    Dedicated Volunteers
                </h3>


                <p>

                    A strong network of passionate individuals committed
                    to making a meaningful difference.

                </p>

            </article>


        </div>



        <!-- LOWER CALL TO ACTION -->

        <div class="story-cta">

            <p>
                Together, we can turn compassion into meaningful change.
            </p>


            <a href="contact.php">

                Get Involved

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


    </div>

</section>



<!-- Footer -->

<?php include 'includes/footer.php'; ?>



<!-- Footer CSS -->

<link rel="stylesheet" href="css/footer.css">



<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>



<!-- =========================================================
     COUNTER ANIMATION
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    const counters =
        document.querySelectorAll(".counter");


    const observer =
        new IntersectionObserver((entries, observer) => {


            entries.forEach(entry => {


                if (!entry.isIntersecting) {
                    return;
                }


                const counter =
                    entry.target;


                const target =
                    parseInt(counter.dataset.target);


                let current = 0;


                const duration = 1800;

                const startTime =
                    performance.now();


                function updateCounter(currentTime) {


                    const elapsed =
                        currentTime - startTime;


                    const progress =
                        Math.min(
                            elapsed / duration,
                            1
                        );


                    const easedProgress =
                        1 -
                        Math.pow(
                            1 - progress,
                            3
                        );


                    current =
                        Math.floor(
                            target *
                            easedProgress
                        );


                    counter.textContent =
                        current.toLocaleString();


                    if (progress < 1) {

                        requestAnimationFrame(
                            updateCounter
                        );

                    } else {

                        counter.textContent =
                            target.toLocaleString();

                    }

                }


                requestAnimationFrame(
                    updateCounter
                );


                observer.unobserve(counter);

            });


        }, {

            threshold: 0.5

        });


    counters.forEach(counter => {

        observer.observe(counter);

    });

});

</script>



<!-- =========================================================
     HERO IMAGE SLIDESHOW
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    const slides =
        document.querySelectorAll(".hero-slide");


    if (slides.length <= 1) {
        return;
    }


    let currentSlide = 0;


    setInterval(function () {


        slides[currentSlide]
            .classList
            .remove("active");


        currentSlide++;


        if (
            currentSlide >=
            slides.length
        ) {

            currentSlide = 0;

        }


        slides[currentSlide]
            .classList
            .add("active");


    }, 2000);

});

</script>



<!-- =========================================================
     CUSTOM JS
========================================================= -->

<script src="js/script.js"></script>


</body>

</html>