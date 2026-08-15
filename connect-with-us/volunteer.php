<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Volunteer With Us | Sevartha Foundation</title>


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Main Website CSS -->
    <link
        rel="stylesheet"
        href="../css/style.css">


    <!-- Navbar CSS -->
    <link
        rel="stylesheet"
        href="../css/navbar.css">


    <!-- Footer CSS -->
    <link
        rel="stylesheet"
        href="../css/footer.css">


    <!-- Volunteer Page CSS -->
    <link
        rel="stylesheet"
        href="css/volunteer.css">


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>


<body>


    <?php include '../includes/navbar.php'; ?>


    <!-- =========================================================
     HERO
========================================================= -->

    <section class="volunteer-hero">

        <div class="volunteer-container">

            <div class="volunteer-eyebrow">

                <span></span>

                SEVARTHA FOUNDATION

            </div>


            <h1 class="volunteer-title">

                Volunteer
                <span>With Us</span>

            </h1>


            <p class="volunteer-intro">

                Be a part of the change and help us make a
                meaningful difference in the lives of others.

            </p>

        </div>

    </section>



    <!-- =========================================================
     INTRODUCTION
========================================================= -->

    <section class="volunteer-intro-section">

        <div class="volunteer-container">

            <div class="volunteer-intro-content">

                <div class="volunteer-label">

                    <span></span>

                    JOIN THE MOVEMENT

                </div>


                <h2>

                    Your Time Can
                    <span>Make a Difference</span>

                </h2>


                <p>

                    Volunteering with Sevartha Foundation gives you
                    an opportunity to contribute your time, skills
                    and energy towards creating a positive impact
                    in the community.

                </p>


                <p>

                    Whether you want to support education, healthcare,
                    food distribution, elderly care or disaster relief,
                    there is always a way for you to contribute.

                </p>

            </div>

        </div>

    </section>



    <!-- =========================================================
     VOLUNTEER FORM
========================================================= -->

    <section class="volunteer-form-section">

        <div class="volunteer-container">


            <div class="volunteer-form-wrapper">


                <!-- FORM INTRO -->

                <div class="volunteer-form-header">

                    <div class="volunteer-label">

                        <span></span>

                        VOLUNTEER REGISTRATION

                    </div>


                    <h2>

                        Become a
                        <span>Volunteer</span>

                    </h2>


                    <p>

                        Fill in the details below and take the
                        first step towards becoming a part of
                        Sevartha Foundation.

                    </p>

                </div>



                <!-- FORM -->

                <form
                    class="volunteer-form"
                    id="volunteerForm"
                    action=""
                    method="POST">


                    <!-- =========================================
                     PERSONAL DETAILS
                ========================================== -->

                    <div class="form-section-title">

                        <i class="fa-solid fa-user"></i>

                        Personal Details

                    </div>


                    <div class="form-row">


                        <!-- FULL NAME -->

                        <div class="form-group">

                            <label for="full_name">

                                Full Name
                                <span>*</span>

                            </label>

                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                placeholder="Enter your full name"
                                required>

                        </div>


                        <!-- CITY -->

                        <div class="form-group">

                            <label for="city">

                                City

                            </label>

                            <input
                                type="text"
                                id="city"
                                name="city"
                                placeholder="Enter your city">

                        </div>

                    </div>



                    <div class="form-row">


                        <!-- EMAIL -->

                        <div class="form-group">

                            <label for="email">

                                Email Address
                                <span>*</span>

                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                required>

                        </div>


                        <!-- PHONE -->

                        <div class="form-group">

                            <label for="phone">

                                Phone Number
                                <span>*</span>

                            </label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                placeholder="Enter your phone number"
                                required>

                        </div>

                    </div>



                    <!-- =========================================
                     INTEREST AREAS
                ========================================== -->

                    <div class="form-section-title">

                        <i class="fa-solid fa-hand-holding-heart"></i>

                        How Would You Like to Help?

                    </div>


                    <p class="form-help-text">

                        Select one or more areas where you would
                        like to contribute.

                    </p>


                    <div class="interest-grid">


                        <label class="interest-option">

                            <input
                                type="checkbox"
                                name="interest[]"
                                value="Education">

                            <span class="custom-checkbox">

                                <i class="fa-solid fa-check"></i>

                            </span>


                            <span class="interest-content">

                                <i class="fa-solid fa-book-open"></i>

                                <span>

                                    <strong>
                                        Education
                                    </strong>

                                    <small>
                                        Supporting children and learning
                                    </small>

                                </span>

                            </span>

                        </label>



                        <label class="interest-option">

                            <input
                                type="checkbox"
                                name="interest[]"
                                value="Food and Poverty Relief">

                            <span class="custom-checkbox">

                                <i class="fa-solid fa-check"></i>

                            </span>


                            <span class="interest-content">

                                <i class="fa-solid fa-bowl-food"></i>

                                <span>

                                    <strong>
                                        Food & Poverty Relief
                                    </strong>

                                    <small>
                                        Helping vulnerable communities
                                    </small>

                                </span>

                            </span>

                        </label>



                        <label class="interest-option">

                            <input
                                type="checkbox"
                                name="interest[]"
                                value="Healthcare">

                            <span class="custom-checkbox">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            <span class="interest-content">

                                <i class="fa-solid fa-heart-pulse"></i>

                                <span>

                                    <strong>
                                        Healthcare
                                    </strong>

                                    <small>
                                        Supporting health initiatives
                                    </small>

                                </span>

                            </span>

                        </label>


                        <label class="interest-option">

                            <input
                                type="checkbox"
                                name="interest[]"
                                value="Elderly Care">

                            <span class="custom-checkbox">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            <span class="interest-content">

                                <i class="fa-solid fa-person-cane"></i>

                                <span>

                                    <strong>
                                        Elderly Care
                                    </strong>

                                    <small>
                                        Supporting senior citizens
                                    </small>

                                </span>

                            </span>

                        </label>


                        <label class="interest-option">

                            <input
                                type="checkbox"
                                name="interest[]"
                                value="Disaster Relief">

                            <span class="custom-checkbox">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            <span class="interest-content">

                                <i class="fa-solid fa-house-medical"></i>

                                <span>

                                    <strong>
                                        Disaster Relief
                                    </strong>

                                    <small>
                                        Helping during emergencies
                                    </small>

                                </span>

                            </span>

                        </label>
                    </div>



                    <!-- =========================================
                     AVAILABILITY
                ========================================== -->

                    <div class="form-section-title">

                        <i class="fa-regular fa-calendar"></i>

                        Your Availability

                    </div>


                    <div class="radio-group">


                        <label class="radio-option">

                            <input
                                type="radio"
                                name="availability"
                                value="Weekdays">

                            <span class="custom-radio"></span>

                            Weekdays

                        </label>


                        <label class="radio-option">

                            <input
                                type="radio"
                                name="availability"
                                value="Weekends">

                            <span class="custom-radio"></span>

                            Weekends

                        </label>


                        <label class="radio-option">

                            <input
                                type="radio"
                                name="availability"
                                value="Both">

                            <span class="custom-radio"></span>

                            Both

                        </label>

                    </div>



                    <!-- =========================================
                     EXPERIENCE
                ========================================== -->

                    <div class="form-section-title">

                        <i class="fa-solid fa-people-group"></i>

                        Previous Volunteering Experience

                    </div>


                    <div class="radio-group">


                        <label class="radio-option">

                            <input
                                type="radio"
                                name="previous_experience"
                                value="Yes">

                            <span class="custom-radio"></span>

                            Yes

                        </label>


                        <label class="radio-option">

                            <input
                                type="radio"
                                name="previous_experience"
                                value="No">

                            <span class="custom-radio"></span>

                            No

                        </label>

                    </div>



                    <!-- =========================================
                     MESSAGE
                ========================================== -->

                    <div class="form-section-title">

                        <i class="fa-regular fa-message"></i>

                        Tell Us About Yourself

                    </div>


                    <div class="form-group">

                        <label for="message">

                            Message

                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            placeholder="Tell us a little about yourself, your skills, or why you would like to volunteer with us..."></textarea>

                    </div>



                    <!-- =========================================
                     SUBMIT
                ========================================== -->
                    <div
                        id="formMessage"
                        class="form-message"
                        aria-live="polite"></div>
                    <div class="form-submit">

                        <button
                            type="submit"
                            class="volunteer-submit">

                            SUBMIT APPLICATION

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>


                        <p>

                            <i class="fa-solid fa-lock"></i>

                            Your information will be kept
                            confidential.

                        </p>

                    </div>


                </form>

            </div>

        </div>

    </section>



    <?php include '../includes/footer.php'; ?>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <script src="js/volunteer.js"></script>


</body>

</html>