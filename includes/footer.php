<?php
/* =========================================================
   SEVARTHA FOUNDATION FOOTER
========================================================= */

/*
 * The project uses /ngo-website/ as its application root.
 * Keeping the base path in one place prevents footer links
 * from breaking when the footer is included from subfolders.
 */
$siteBase = '/ngo-website/';


/* =========================================================
   OUR WORK CATEGORIES
========================================================= */

/*
 * Navbar already loads the database connection before the
 * footer is included. The fallback below keeps the footer
 * usable if it is ever included on its own.
 */
if (!isset($conn)) {
    require_once __DIR__ . '/../config/db.php';
}

$footerCategories = mysqli_query(
    $conn,
    "
        SELECT id, name, slug
        FROM categories
        ORDER BY id ASC
    "
);

?>

<!-- =========================================================
     FLOATING WHATSAPP BUTTON
========================================================= -->

<a
    href="https://wa.me/919867983524"
    class="floating-whatsapp"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Chat with Sevartha Foundation on WhatsApp"
    title="Chat with us on WhatsApp"
>
    <i class="fab fa-whatsapp"></i>
</a>

<!-- =========================================================
     SEVARTHA FOUNDATION FOOTER
========================================================= -->

<footer class="site-footer">

    <div class="footer-container">

        <!-- =================================================
             BRAND / ABOUT
        ================================================== -->

        <div class="footer-col footer-brand">

            <a
                href="<?= $siteBase; ?>index.php"
                class="footer-logo">
                Sevartha Foundation
            </a>

            <p class="footer-tagline">
                Empowering Lives,<br>
                Transforming Communities.
            </p>

            <p class="footer-description">
                Sevartha Foundation is committed to uplifting
                underserved communities through education,
                healthcare, food support, elderly care and
                disaster relief.
            </p>

            <a
                href="<?= $siteBase; ?>donate/donate.php"
                class="footer-donate-btn">
                Donate Now
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>


        <!-- =================================================
             ABOUT US
        ================================================== -->

        <div class="footer-col">

            <h4>About Us</h4>

            <ul>

                <li>
                    <a
                        href="<?= $siteBase; ?>about-us/mission-vision-focus.php">
                        Mission, Vision &amp; Focus
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>about-us/founders.php">
                        Our Founders
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>about-us/our-teams.php">
                        Our Team
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>about-us/accountability-transparency.php">
                        Financial Transparency
                    </a>
                </li>

            </ul>

        </div>


        <!-- =================================================
             OUR WORK
        ================================================== -->

        <div class="footer-col">

            <h4>Our Work</h4>

            <ul>

                <?php if (
                    $footerCategories &&
                    mysqli_num_rows($footerCategories) > 0
                ) { ?>

                    <?php while (
                        $footerCategory =
                        mysqli_fetch_assoc($footerCategories)
                    ) { ?>

                        <li>
                            <a
                                href="<?= $siteBase; ?>our-work/category.php?slug=<?= urlencode($footerCategory['slug']); ?>">
                                <?= htmlspecialchars(
                                    $footerCategory['name']
                                ); ?>
                            </a>
                        </li>

                    <?php } ?>

                <?php } else { ?>

                    <li>
                        <a
                            href="<?= $siteBase; ?>about-us/mission-vision-focus.php">
                            Our Focus Areas
                        </a>
                    </li>

                <?php } ?>

                <li>
                    <a href="<?= $siteBase; ?>annual_event.php">
                        Annual Events
                    </a>
                </li>

            </ul>

        </div>


        <!-- =================================================
             GET INVOLVED
        ================================================== -->

        <div class="footer-col">

            <h4>Get Involved</h4>

            <ul>

                <li>
                    <a href="<?= $siteBase; ?>donate/donate.php">
                        Donate
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>connect-with-us/volunteer.php">
                        Volunteer With Us
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>connect-with-us/contact.php">
                        Contact Us
                    </a>
                </li>

                <li>
                    <a
                        href="<?= $siteBase; ?>connect-with-us/location.php">
                        Our Location
                    </a>
                </li>

            </ul>

        </div>


        <!-- =================================================
             CONNECT WITH US
        ================================================== -->

        <div class="footer-col footer-connect">

            <h4>Connect With Us</h4>

            <div class="footer-social">

                <!-- Facebook -->
                <a
                    href="https://www.facebook.com/sevarthafoundation"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Facebook"
                    class="footer-facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>


                <!-- Instagram -->
                <a
                    href="https://www.instagram.com/sevarthafoundation/reels"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Instagram"
                    class="footer-instagram">
                    <i class="fab fa-instagram"></i>
                </a>


                <!-- WhatsApp -->
                <a
                    href="https://wa.me/919867983524"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="WhatsApp"
                    class="footer-whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </a>

            </div>

            <div class="footer-contact-info">

                <p>
                    <i class="fas fa-location-dot"></i>

                    <a
                        href="<?= $siteBase; ?>connect-with-us/location.php">
                        <span>
                            Plot No. 72, Office No. 101–102,<br>
                            Opposite FAB Gym, Shri Krishna Nagar,<br>
                            Near National Park,<br>
                            Borivali (East), Mumbai – 400066
                        </span>
                    </a>
                </p>

                <p>
                    <i class="fas fa-envelope"></i>

                    <a
                        href="https://mail.google.com/mail/?view=cm&fs=1&to=sevarthafoundation7@gmail.com&su=Contact%20Sevartha%20Foundation&body=Hello%20Sevartha%20Foundation%2C%0A%0AI%20would%20like%20to%20get%20in%20touch%20with%20you%20regarding%3A%0A%0A"
                        target="_blank"
                        rel="noopener noreferrer">
                        sevarthafoundation7@gmail.com
                    </a>
                </p>
                <p>
                    <i class="fas fa-phone"></i>

                    <a href="tel:+919867983524">
                        +91 98679 83524
                    </a>
                </p>

            </div>

        </div>

    </div>


    <!-- =====================================================
         FOOTER BOTTOM
    ====================================================== -->

    <div class="footer-bottom">

        <div class="footer-bottom-left">

            <span>
                © <?= date("Y"); ?> Sevartha Foundation.
                All Rights Reserved.
            </span>

        </div>


        <div class="footer-bottom-links">

            <a href="#">
                Privacy Policy
            </a>

            <span>•</span>

            <a href="#">
                Terms & Conditions
            </a>

        </div>

    </div>

</footer>
<script src="/ngo-website/js/animations.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const whatsappButton =
        document.querySelector(".floating-whatsapp");

    if (!whatsappButton) return;


    /*
     * Find the existing back-to-top button.
     *
     * The selectors below cover the common names
     * used for the site's back-to-top button.
     */

    const backToTop =
        document.querySelector(
            "#backToTop, " +
            ".back-to-top, " +
            ".back-to-top-btn, " +
            ".scroll-to-top, " +
            ".scroll-top"
        );


    /*
     * If the page has no back-to-top button,
     * WhatsApp stays hidden.
     */

    if (!backToTop) return;


    /*
     * Check whether the back-to-top arrow
     * is currently visible.
     */

    function updateWhatsAppVisibility() {

        const arrowStyle =
            window.getComputedStyle(backToTop);

        const arrowVisible =
            arrowStyle.display !== "none" &&
            arrowStyle.visibility !== "hidden" &&
            parseFloat(arrowStyle.opacity) > 0 &&
            backToTop.classList.contains("show");


        if (arrowVisible) {

            whatsappButton.classList.add("show");

        } else {

            whatsappButton.classList.remove("show");

        }

    }


    /*
     * Watch for class/style changes on the
     * back-to-top button.
     */

    const observer =
        new MutationObserver(function () {

            updateWhatsAppVisibility();

        });


    observer.observe(backToTop, {
        attributes: true,
        attributeFilter: [
            "class",
            "style"
        ]
    });


    /*
     * Also check while scrolling.
     */

    window.addEventListener(
        "scroll",
        updateWhatsAppVisibility,
        { passive: true }
    );


    /*
     * Initial state.
     */

    updateWhatsAppVisibility();

});
</script>