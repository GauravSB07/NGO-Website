/* =========================================================
   SEVARTHA FOUNDATION
   GLOBAL WEBSITE ANIMATION SYSTEM
   ---------------------------------------------------------
   • Hero animations preserved
   • Global scroll reveal
   • TRUE STACK → SCATTER CARD ANIMATION
   • Scroll progress
   • Back to top
   • Hero mouse parallax
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       1. SELECTORS
    ====================================================== */

    const cardGroupSelector =
        ".story-band__grid, " +
        ".focus-grid, " +
        ".founders-grid, " +
        ".team-grid, " +
        ".documents-grid, " +
        ".testimonial-document-grid, " +
        ".interest-grid, " +
        ".address-grid";

    const heroSelector =
        ".hero-section, " +
        ".about-page-hero, " +
        ".founders-hero, " +
        ".team-hero, " +
        ".transparency-hero, " +
        ".contact-hero, " +
        ".location-hero, " +
        ".volunteer-hero, " +
        ".testimonial-page-hero, " +
        ".category-hero, " +
        ".annual-hero";


    /* =====================================================
       2. EXISTING MANUAL REVEAL SYSTEM
    ====================================================== */

    const revealElements = document.querySelectorAll(
        ".reveal, .reveal-left, .reveal-right, .reveal-scale"
    );

    if ("IntersectionObserver" in window) {

        const revealObserver = new IntersectionObserver(
            function (entries, observer) {

                entries.forEach(function (entry) {

                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add("active");

                    observer.unobserve(entry.target);
                });

            },
            {
                threshold: 0.12,
                rootMargin: "0px 0px -50px 0px"
            }
        );

        revealElements.forEach(function (element) {
            revealObserver.observe(element);
        });

    } else {

        revealElements.forEach(function (element) {
            element.classList.add("active");
        });

    }


    /* =====================================================
       3. GLOBAL SCROLL CONTENT PREPARATION
       
       This automatically gives normal sections:
       • heading reveal
       • paragraph reveal
       • image reveal
       • button reveal

       Card groups are deliberately excluded because
       they have their own STACK → SCATTER system.
    ====================================================== */

    const normalSections = document.querySelectorAll(
        "main section:not(.hero-section)"
    );


    function isInsideCardGroup(element) {

        return !!element.closest(cardGroupSelector);

    }


    function prepareSection(section) {

        if (section.matches(heroSelector)) {
            return;
        }

        section.classList.add("scroll-section-pending");


        /* -----------------------------------------------
           Existing fade animations

           We DO NOT remove them.

           We pause them until their section is reached.
        ------------------------------------------------ */

        const existingAnimatedElements =
            section.querySelectorAll(
                ".fade-in-up, .fade-in-left, .fade-in-right"
            );

        existingAnimatedElements.forEach(function (element) {

            element.classList.add(
                "scroll-controlled-animation"
            );

        });


        /* -----------------------------------------------
           Headings
        ------------------------------------------------ */

        const headings =
            section.querySelectorAll(
                "h1, h2, h3, .section-title, .section-subtitle"
            );

        headings.forEach(function (element) {

            if (isInsideCardGroup(element)) {
                return;
            }

            if (
                element.classList.contains("reveal") ||
                element.classList.contains("reveal-left") ||
                element.classList.contains("reveal-right") ||
                element.classList.contains("reveal-scale")
            ) {
                return;
            }

            element.classList.add("global-scroll-text");

            element.style.setProperty(
                "--scroll-delay",
                "0s"
            );

        });


        /* -----------------------------------------------
           Paragraphs
        ------------------------------------------------ */

        const paragraphs =
            section.querySelectorAll("p");

        paragraphs.forEach(function (element) {

            if (isInsideCardGroup(element)) {
                return;
            }

            if (
                element.classList.contains("reveal") ||
                element.classList.contains("reveal-left") ||
                element.classList.contains("reveal-right") ||
                element.classList.contains("reveal-scale")
            ) {
                return;
            }

            element.classList.add("global-scroll-text");

            element.style.setProperty(
                "--scroll-delay",
                "0.14s"
            );

        });


        /* -----------------------------------------------
           Images

           Don't touch hero images.
        ------------------------------------------------ */

        const images =
            section.querySelectorAll("img");

        images.forEach(function (image) {

            if (
                isInsideCardGroup(image) ||
                image.closest(heroSelector)
            ) {
                return;
            }

            if (
                image.classList.contains("reveal") ||
                image.classList.contains("reveal-left") ||
                image.classList.contains("reveal-right") ||
                image.classList.contains("reveal-scale")
            ) {
                return;
            }

            image.classList.add("global-scroll-image");

            image.style.setProperty(
                "--scroll-delay",
                "0.18s"
            );

        });


        /* -----------------------------------------------
           Buttons / CTA links
        ------------------------------------------------ */

        const buttons =
            section.querySelectorAll(
                "button, .btn, a.btn, input[type='submit'], input[type='button']"
            );

        buttons.forEach(function (button) {

            if (isInsideCardGroup(button)) {
                return;
            }

            if (
                button.classList.contains("reveal") ||
                button.classList.contains("reveal-left") ||
                button.classList.contains("reveal-right") ||
                button.classList.contains("reveal-scale")
            ) {
                return;
            }

            button.classList.add("global-scroll-button");

            button.style.setProperty(
                "--scroll-delay",
                "0.28s"
            );

        });

    }


    normalSections.forEach(function (section) {
        prepareSection(section);
    });


    /* =====================================================
       4. GLOBAL SECTION REVEAL

       IMPORTANT:
       Nothing below the hero is automatically shown just
       because the page loaded.

       The section becomes active when the user reaches it.
    ====================================================== */

    function revealSection(section) {

        if (
            !section ||
            section.classList.contains("scroll-section-active")
        ) {
            return;
        }

        section.classList.remove(
            "scroll-section-pending"
        );

        section.classList.add(
            "scroll-section-active"
        );

    }


    if ("IntersectionObserver" in window) {

        const sectionObserver =
            new IntersectionObserver(
                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }

                        /*
                         * If the page has loaded at the very top,
                         * wait for actual scrolling.
                         */
                        if (window.scrollY <= 5) {
                            return;
                        }

                        revealSection(entry.target);

                        observer.unobserve(entry.target);

                    });

                },
                {
                    threshold: 0.08,
                    rootMargin: "0px 0px -60px 0px"
                }
            );


        normalSections.forEach(function (section) {
            sectionObserver.observe(section);
        });


        /*
         * First real scroll.
         *
         * This handles sections which were already partially
         * visible when the page initially loaded.
         */

        let firstScrollHandled = false;

        function handleFirstScroll() {

            if (firstScrollHandled) {
                return;
            }

            if (window.scrollY <= 5) {
                return;
            }

            firstScrollHandled = true;


            normalSections.forEach(function (section) {

                const rect =
                    section.getBoundingClientRect();

                const visible =
                    rect.top <
                    window.innerHeight * 0.88 &&
                    rect.bottom > 0;

                if (visible) {
                    revealSection(section);
                }

            });


            window.removeEventListener(
                "scroll",
                handleFirstScroll
            );

        }

        window.addEventListener(
            "scroll",
            handleFirstScroll,
            {
                passive: true
            }
        );

    } else {

        /*
         * Old-browser fallback
         */

        normalSections.forEach(function (section) {
            revealSection(section);
        });

    }


    /* =====================================================
       5. TRUE STACK → SCATTER CARD ANIMATION

       THIS IS THE IMPORTANT PART.

       Cards:
       1. Calculate their REAL grid positions.
       2. Move into one stacked deck.
       3. Show the deck.
       4. Scatter each card to its ORIGINAL position.
       5. Restore the normal CSS grid.

       DO NOT REMOVE THIS SYSTEM.
    ====================================================== */

    const cardGroups =
        document.querySelectorAll(cardGroupSelector);


    cardGroups.forEach(function (group) {

        const cards =
            Array.from(group.children);


        if (!cards.length) {
            return;
        }


        /*
         * Don't run twice.
         */

        if (group.dataset.stackAnimationInitialized) {
            return;
        }

        group.dataset.stackAnimationInitialized = "true";


        /*
         * IMPORTANT:
         *
         * The original cards may already have fade-in
         * classes in the HTML.
         *
         * The stack animation controls them instead.
         */

        cards.forEach(function (card) {

            card.classList.remove(
                "fade-in-up",
                "fade-in-left",
                "fade-in-right",
                "fade-in",
                "reveal",
                "reveal-left",
                "reveal-right",
                "reveal-scale"
            );

            card.style.animation = "none";

        });


        /*
         * Wait for the browser to calculate the original
         * grid positions.
         */

        requestAnimationFrame(function () {

            requestAnimationFrame(function () {

                const groupRect =
                    group.getBoundingClientRect();


                /*
                 * Save the ORIGINAL grid positions.
                 */

                const originalPositions =
                    cards.map(function (card) {

                        const rect =
                            card.getBoundingClientRect();

                        return {

                            x:
                                rect.left -
                                groupRect.left,

                            y:
                                rect.top -
                                groupRect.top,

                            width:
                                rect.width,

                            height:
                                rect.height

                        };

                    });


                /*
                 * Preserve group height while cards
                 * temporarily become absolute.
                 */

                const originalHeight =
                    groupRect.height;


                group.style.minHeight =
                    originalHeight + "px";


                group.classList.add(
                    "stack-cards"
                );


                /* -----------------------------------------
                   PUT ALL CARDS INTO ONE DECK
                ----------------------------------------- */

                cards.forEach(function (card, index) {

                    const position =
                        originalPositions[index];


                    /*
                     * Center each card in the group.
                     */

                    const stackX =
                        (groupRect.width -
                            position.width) / 2;

                    const stackY =
                        (groupRect.height -
                            position.height) / 2;


                    /*
                     * Slight rotation gives the deck
                     * a natural stacked appearance.
                     */

                    const rotations = [
                        -3,
                        2,
                        -2,
                        3,
                        -1.5,
                        2.5
                    ];


                    const rotation =
                        rotations[
                            index %
                            rotations.length
                        ];


                    card.style.position =
                        "absolute";

                    card.style.left =
                        "0px";

                    card.style.top =
                        "0px";

                    card.style.width =
                        position.width + "px";

                    card.style.height =
                        position.height + "px";

                    card.style.margin =
                        "0";

                    card.style.zIndex =
                        cards.length - index;


                    /*
                     * Hidden initially.
                     */

                    card.style.opacity =
                        "0";


                    card.style.transform =
                        "translate3d(" +
                        stackX +
                        "px, " +
                        stackY +
                        "px, 0) " +
                        "rotate(" +
                        rotation +
                        "deg) " +
                        "scale(.94)";


                    card.style.transition =
                        "none";

                });


                /* -----------------------------------------
                   STACK OBSERVER
                ----------------------------------------- */

                function startStackAnimation() {

                    if (
                        group.classList.contains(
                            "stack-animation-complete"
                        )
                    ) {
                        return;
                    }


                    group.classList.add(
                        "deck-ready"
                    );


                    /*
                     * STEP 1
                     *
                     * Show the complete stacked deck.
                     */

                    cards.forEach(function (card) {

                        card.style.opacity =
                            "1";

                    });


                    /*
                     * Small pause so the user sees
                     * the deck before it spreads.
                     */

                    setTimeout(function () {


                        /*
                         * STEP 2
                         *
                         * Move every card to the exact
                         * position it originally occupied.
                         */

                        cards.forEach(
                            function (card, index) {

                                const position =
                                    originalPositions[
                                        index
                                    ];


                                const delay =
                                    index * 0.16;


                                card.style.transition =
                                    "transform 1.15s " +
                                    "cubic-bezier(" +
                                    "0.16, 1, 0.3, 1) " +
                                    delay +
                                    "s, " +
                                    "opacity .5s ease " +
                                    delay +
                                    "s";


                                card.style.transform =
                                    "translate3d(" +
                                    position.x +
                                    "px, " +
                                    position.y +
                                    "px, 0) " +
                                    "rotate(0deg) " +
                                    "scale(1)";

                            }
                        );


                        group.classList.add(
                            "cards-visible"
                        );


                        /*
                         * STEP 3
                         *
                         * Wait for the last card to
                         * reach its position.
                         */

                        const totalTime =
                            1150 +
                            (
                                (cards.length - 1) *
                                160
                            ) +
                            200;


                        setTimeout(function () {


                            /*
                             * Restore normal grid CSS.
                             */

                            cards.forEach(
                                function (card) {

                                    card.style.position =
                                        "";

                                    card.style.left =
                                        "";

                                    card.style.top =
                                        "";

                                    card.style.width =
                                        "";

                                    card.style.height =
                                        "";

                                    card.style.margin =
                                        "";

                                    card.style.zIndex =
                                        "";

                                    card.style.opacity =
                                        "";

                                    card.style.transform =
                                        "";

                                    card.style.transition =
                                        "";

                                    card.style.animation =
                                        "";

                                }
                            );


                            group.style.minHeight =
                                "";


                            group.classList.add(
                                "stack-animation-complete"
                            );

                        }, totalTime);


                    }, 450);

                }


                /* -----------------------------------------
                   OBSERVE CARD GROUP
                ----------------------------------------- */

                if ("IntersectionObserver" in window) {

                    const stackObserver =
                        new IntersectionObserver(
                            function (
                                entries,
                                observer
                            ) {

                                entries.forEach(
                                    function (entry) {

                                        if (
                                            !entry.isIntersecting
                                        ) {
                                            return;
                                        }


                                        /*
                                         * Start only after
                                         * actual scrolling.
                                         */

                                        if (
                                            window.scrollY <= 5
                                        ) {
                                            return;
                                        }


                                        startStackAnimation();


                                        observer.unobserve(
                                            entry.target
                                        );

                                    }
                                );

                            },
                            {
                                threshold: 0.16,
                                rootMargin:
                                    "0px 0px -70px 0px"
                            }
                        );


                    stackObserver.observe(group);


                    /*
                     * If the card group was already partly
                     * visible during the first scroll, start it.
                     */

                    let stackScrollHandled =
                        false;


                    function checkStackOnScroll() {

                        if (stackScrollHandled) {
                            return;
                        }

                        if (window.scrollY <= 5) {
                            return;
                        }


                        const rect =
                            group.getBoundingClientRect();


                        const visible =
                            rect.top <
                            window.innerHeight * 0.88 &&
                            rect.bottom > 0;


                        if (visible) {

                            stackScrollHandled =
                                true;

                            startStackAnimation();

                            window.removeEventListener(
                                "scroll",
                                checkStackOnScroll
                            );

                        }

                    }


                    window.addEventListener(
                        "scroll",
                        checkStackOnScroll,
                        {
                            passive: true
                        }
                    );


                } else {

                    /*
                     * Fallback
                     */

                    cards.forEach(function (card) {

                        card.style.position =
                            "";

                        card.style.left =
                            "";

                        card.style.top =
                            "";

                        card.style.width =
                            "";

                        card.style.height =
                            "";

                        card.style.opacity =
                            "1";

                        card.style.transform =
                            "";

                        card.style.transition =
                            "";

                    });


                    group.style.minHeight =
                        "";

                }

            });

        });

    });


    /* =====================================================
       6. SECTIONS WITHOUT <section>

       Some pages can contain a main container directly.
       Give those pages global animation too.
    ====================================================== */

    const mainContainers =
        document.querySelectorAll(
            "main > .container, " +
            "main > .container-fluid"
        );


    mainContainers.forEach(function (container) {

        if (
            container.querySelector(
                "section:not(.hero-section)"
            )
        ) {
            return;
        }


        if (
            container.closest(heroSelector)
        ) {
            return;
        }


        container.classList.add(
            "scroll-section-pending"
        );


        const elements =
            container.querySelectorAll(
                "h1, h2, h3, p, img, button, .btn"
            );


        elements.forEach(function (element) {

            if (isInsideCardGroup(element)) {
                return;
            }


            if (
                element.matches("h1, h2, h3")
            ) {

                element.classList.add(
                    "global-scroll-text"
                );

            } else if (
                element.matches("img")
            ) {

                element.classList.add(
                    "global-scroll-image"
                );

            } else if (
                element.matches("button, .btn")
            ) {

                element.classList.add(
                    "global-scroll-button"
                );

            } else {

                element.classList.add(
                    "global-scroll-text"
                );

            }

        });


        if ("IntersectionObserver" in window) {

            const mainObserver =
                new IntersectionObserver(
                    function (entries, observer) {

                        entries.forEach(
                            function (entry) {

                                if (
                                    !entry.isIntersecting
                                ) {
                                    return;
                                }


                                if (
                                    window.scrollY <= 5
                                ) {
                                    return;
                                }


                                entry.target.classList.remove(
                                    "scroll-section-pending"
                                );

                                entry.target.classList.add(
                                    "scroll-section-active"
                                );


                                observer.unobserve(
                                    entry.target
                                );

                            }
                        );

                    },
                    {
                        threshold: 0.08,
                        rootMargin:
                            "0px 0px -60px 0px"
                    }
                );


            mainObserver.observe(container);

        }

    });


    /* =====================================================
       7. SCROLL PROGRESS BAR
    ====================================================== */

    const progressBar =
        document.createElement("div");

    progressBar.className =
        "scroll-progress";

    document.body.appendChild(
        progressBar
    );


    function updateScrollProgress() {

        const scrollTop =
            window.scrollY ||
            document.documentElement.scrollTop;


        const documentHeight =
            document.documentElement.scrollHeight -
            document.documentElement.clientHeight;


        if (documentHeight <= 0) {

            progressBar.style.width =
                "0%";

            return;
        }


        const progress =
            (scrollTop /
                documentHeight) *
            100;


        progressBar.style.width =
            progress + "%";

    }


    window.addEventListener(
        "scroll",
        updateScrollProgress,
        {
            passive: true
        }
    );


    updateScrollProgress();


    /* =====================================================
       8. BACK TO TOP
    ====================================================== */

    const backToTop =
        document.createElement("button");


    backToTop.className =
        "back-to-top";


    backToTop.type =
        "button";


    backToTop.setAttribute(
        "aria-label",
        "Back to top"
    );


    backToTop.innerHTML =
        '<i class="fa-solid fa-arrow-up"></i>';


    document.body.appendChild(
        backToTop
    );


    function updateBackToTop() {

        if (window.scrollY > 500) {

            backToTop.classList.add(
                "show"
            );

        } else {

            backToTop.classList.remove(
                "show"
            );

        }

    }


    window.addEventListener(
        "scroll",
        updateBackToTop,
        {
            passive: true
        }
    );


    backToTop.addEventListener(
        "click",
        function () {

            window.scrollTo({

                top: 0,

                behavior: "smooth"

            });

        }
    );


    updateBackToTop();


    /* =====================================================
       9. HERO COLLAGE MOUSE PARALLAX

       Hero is intentionally kept separate from the
       global scroll animation.
    ====================================================== */

    const heroCollage =
        document.querySelector(
            ".hero-collage-bg"
        );


    if (
        heroCollage &&
        window.matchMedia(
            "(pointer: fine)"
        ).matches
    ) {

        let targetX = 0;
        let targetY = 0;

        let currentX = 0;
        let currentY = 0;


        window.addEventListener(
            "mousemove",
            function (event) {

                targetX =
                    (
                        event.clientX /
                        window.innerWidth -
                        0.5
                    ) * 10;


                targetY =
                    (
                        event.clientY /
                        window.innerHeight -
                        0.5
                    ) * 10;

            },
            {
                passive: true
            }
        );


        function animateHeroParallax() {

            currentX +=
                (targetX - currentX) *
                0.06;


            currentY +=
                (targetY - currentY) *
                0.06;


            heroCollage.style.transform =
                "translate3d(" +
                currentX +
                "px, " +
                currentY +
                "px, 0)";


            requestAnimationFrame(
                animateHeroParallax
            );

        }


        animateHeroParallax();

    }


    /* =====================================================
       10. REDUCED MOTION

       Accessibility is handled primarily by CSS.
    ====================================================== */

});
/* =========================================================
   GLOBAL CONTENT SCROLL REVEAL
   ---------------------------------------------------------
   IMPORTANT:
   • Does NOT touch hero sections
   • Does NOT touch stack cards
   • Does NOT change card positioning
   • Does NOT change hero animation
   • Hides normal page content until scrolling
   • Heading appears before paragraph
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* -----------------------------------------------------
       HERO SELECTORS
    ----------------------------------------------------- */

    const heroSelector =
        ".hero-section, " +
        ".about-page-hero, " +
        ".founders-hero, " +
        ".team-hero, " +
        ".transparency-hero, " +
        ".contact-hero, " +
        ".location-hero, " +
        ".volunteer-hero, " +
        ".testimonial-page-hero, " +
        ".category-hero, " +
        ".annual-hero";


    /* -----------------------------------------------------
       CARD SELECTORS

       These are COMPLETELY excluded.
       Their existing stack animation remains untouched.
    ----------------------------------------------------- */

    const cardSelector =
        ".story-band__grid, " +
        ".focus-grid, " +
        ".founders-grid, " +
        ".team-grid, " +
        ".documents-grid, " +
        ".testimonial-document-grid, " +
        ".interest-grid, " +
        ".address-grid";


    /* -----------------------------------------------------
       FIND ALL NORMAL PAGE CONTENT
    ----------------------------------------------------- */

    const elements =
        document.querySelectorAll(
            "main h1, " +
            "main h2, " +
            "main h3, " +
            "main h4, " +
            "main p, " +
            "main img, " +
            "main button, " +
            "main .btn, " +
            "main a.btn, " +
            "main li"
        );


    const revealElements = [];


    /* -----------------------------------------------------
       PREPARE ELEMENTS
    ----------------------------------------------------- */

    elements.forEach(function (element) {

        /*
         * NEVER touch hero content.
         */

        if (element.closest(heroSelector)) {
            return;
        }


        /*
         * NEVER touch cards.
         */

        if (element.closest(cardSelector)) {
            return;
        }


        /*
         * Don't interfere with the existing manual reveal
         * system. Those elements already have their own
         * animation.
         */

        if (
            element.classList.contains("reveal") ||
            element.classList.contains("reveal-left") ||
            element.classList.contains("reveal-right") ||
            element.classList.contains("reveal-scale")
        ) {
            return;
        }


        /*
         * Don't animate tiny navigation/list elements
         * unnecessarily.
         */

        if (
            element.closest("nav") ||
            element.closest(".navbar") ||
            element.closest(".site-footer")
        ) {
            return;
        }


        element.classList.add(
            "global-content-reveal"
        );


        /* -------------------------------------------------
           ANIMATION ORDER

           Heading
           ↓
           Paragraph
           ↓
           Image
           ↓
           Button
        ------------------------------------------------- */

        if (
            element.matches(
                "h1, h2, h3, h4"
            )
        ) {

            element.classList.add(
                "global-reveal-heading"
            );

            element.style.setProperty(
                "--content-delay",
                "0s"
            );

        }

        else if (
            element.matches("p")
        ) {

            element.classList.add(
                "global-reveal-paragraph"
            );

            element.style.setProperty(
                "--content-delay",
                "0.18s"
            );

        }

        else if (
            element.matches("img")
        ) {

            element.classList.add(
                "global-reveal-image"
            );

            element.style.setProperty(
                "--content-delay",
                "0.28s"
            );

        }

        else if (
            element.matches(
                "button, .btn, a.btn"
            )
        ) {

            element.classList.add(
                "global-reveal-button"
            );

            element.style.setProperty(
                "--content-delay",
                "0.38s"
            );

        }

        else {

            element.classList.add(
                "global-reveal-other"
            );

            element.style.setProperty(
                "--content-delay",
                "0.12s"
            );

        }


        revealElements.push(element);

    });


    /* -----------------------------------------------------
       IMPORTANT:
       EVERYTHING IS HIDDEN BEFORE USER SCROLLS.
    ----------------------------------------------------- */

    let hasUserScrolled =
        window.scrollY > 5;


    /* -----------------------------------------------------
       REVEAL FUNCTION
    ----------------------------------------------------- */

    function revealElement(element) {

        if (
            element.classList.contains(
                "global-content-visible"
            )
        ) {
            return;
        }


        element.classList.add(
            "global-content-visible"
        );

    }


    /* -----------------------------------------------------
       INTERSECTION OBSERVER
    ----------------------------------------------------- */

    if ("IntersectionObserver" in window) {

        const contentObserver =
            new IntersectionObserver(
                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }


                        /*
                         * Do NOT reveal anything merely because
                         * it was visible when the page loaded.
                         */

                        if (!hasUserScrolled) {
                            return;
                        }


                        revealElement(
                            entry.target
                        );


                        observer.unobserve(
                            entry.target
                        );

                    });

                },
                {
                    threshold: 0.08,

                    rootMargin:
                        "0px 0px -70px 0px"
                }
            );


        revealElements.forEach(function (element) {

            contentObserver.observe(
                element
            );

        });


        /* -------------------------------------------------
           FIRST REAL SCROLL

           If elements were already inside the viewport when
           the page loaded, this makes sure they reveal only
           after the user actually starts scrolling.
        ------------------------------------------------- */

        let firstScrollDone = false;


        function handleFirstScroll() {

            if (firstScrollDone) {
                return;
            }


            if (window.scrollY <= 5) {
                return;
            }


            firstScrollDone = true;

            hasUserScrolled = true;


            revealElements.forEach(
                function (element) {

                    if (
                        element.classList.contains(
                            "global-content-visible"
                        )
                    ) {
                        return;
                    }


                    const rect =
                        element.getBoundingClientRect();


                    const visible =
                        rect.top <
                        window.innerHeight * 0.88 &&
                        rect.bottom > 0;


                    if (visible) {

                        revealElement(
                            element
                        );

                    }

                }
            );


            window.removeEventListener(
                "scroll",
                handleFirstScroll
            );

        }


        window.addEventListener(
            "scroll",
            handleFirstScroll,
            {
                passive: true
            }
        );


    } else {

        /*
         * Fallback for browsers without
         * IntersectionObserver.
         */

        revealElements.forEach(
            function (element) {

                revealElement(
                    element
                );

            }
        );

    }

});
/* =========================================================
   SEVARTHA FOUNDATION
   GLOBAL SCROLL CONTENT ANIMATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /*
     * IMPORTANT:
     * Do not touch hero sections or stack-card groups.
     */

    const excludedSelectors = [
        ".hero-section",
        ".about-page-hero",
        ".founders-hero",
        ".team-hero",
        ".transparency-hero",
        ".contact-hero",
        ".location-hero",
        ".volunteer-hero",
        ".testimonial-page-hero",
        ".category-hero",
        ".annual-hero",
        ".stack-cards",
        ".story-band__grid",
        ".focus-grid",
        ".founders-grid",
        ".team-grid",
        ".documents-grid",
        ".testimonial-document-grid",
        ".interest-grid",
        ".address-grid"
    ];


    function isExcluded(element) {

        return excludedSelectors.some(function (selector) {

            return element.closest(selector) !== null;

        });

    }


    /*
     * ---------------------------------------------------------
     * FIND NORMAL PAGE CONTENT
     * ---------------------------------------------------------
     */

    const contentElements = document.querySelectorAll(
        "main h1, " +
        "main h2, " +
        "main h3, " +
        "main h4, " +
        "main h5, " +
        "main h6, " +
        "main p, " +
        "main img, " +
        "main button, " +
        "main .btn, " +
        "main a.btn, " +
        "main li, " +
        "main figure, " +
        "main blockquote"
    );


    /*
     * ---------------------------------------------------------
     * PREPARE ELEMENTS
     * ---------------------------------------------------------
     */

    const animationElements = [];


    contentElements.forEach(function (element) {

        if (isExcluded(element)) {
            return;
        }


        /*
         * Ignore empty elements
         */

        if (
            element.tagName !== "IMG" &&
            element.textContent.trim() === ""
        ) {
            return;
        }


        /*
         * Do not animate navigation links
         */

        if (element.closest("nav")) {
            return;
        }


        /*
         * Do not animate footer elements here
         */

        if (element.closest("footer")) {
            return;
        }


        /*
         * Remove old manual reveal classes
         * from NORMAL content only.
         *
         * Hero and cards were already excluded above.
         */

        element.classList.remove(
            "reveal",
            "reveal-left",
            "reveal-right",
            "reveal-scale",
            "fade-in",
            "fade-in-up",
            "fade-in-left",
            "fade-in-right"
        );


        /*
         * Heading
         */

        if (/^H[1-6]$/.test(element.tagName)) {

            element.classList.add("site-scroll-heading");

        }


        /*
         * Paragraph
         */

        else if (element.tagName === "P") {

            element.classList.add("site-scroll-paragraph");

        }


        /*
         * Image
         */

        else if (element.tagName === "IMG") {

            element.classList.add("site-scroll-image");

        }


        /*
         * Button
         */

        else if (
            element.tagName === "BUTTON" ||
            element.matches(".btn, a.btn")
        ) {

            element.classList.add("site-scroll-button");

        }


        /*
         * Everything else
         */

        else {

            element.classList.add("site-scroll-other");

        }


        animationElements.push(element);

    });


    /*
     * ---------------------------------------------------------
     * USER SCROLL STATE
     *
     * Nothing gets revealed automatically when page loads.
     * ---------------------------------------------------------
     */

    let userHasScrolled = false;


    /*
     * ---------------------------------------------------------
     * REVEAL ELEMENT
     * ---------------------------------------------------------
     */

    function revealElement(element) {

        if (!element) {
            return;
        }

        element.classList.add("site-scroll-visible");

    }


    /*
     * ---------------------------------------------------------
     * CHECK WHETHER ELEMENT IS CURRENTLY VISIBLE
     * ---------------------------------------------------------
     */

    function isElementInViewport(element) {

        const rect = element.getBoundingClientRect();

        return (
            rect.top < window.innerHeight * 0.88 &&
            rect.bottom > window.innerHeight * 0.05
        );

    }


    /*
     * ---------------------------------------------------------
     * FIRST SCROLL
     *
     * This is important.
     *
     * IntersectionObserver may already have checked elements
     * while the page was sitting at the top.
     *
     * We therefore explicitly reveal elements when the user
     * actually starts scrolling.
     * ---------------------------------------------------------
     */

    function handleFirstScroll() {

        if (userHasScrolled) {
            return;
        }

        userHasScrolled = true;


        animationElements.forEach(function (element) {

            if (isElementInViewport(element)) {

                revealElement(element);

            }

        });


        /*
         * Footer if currently visible
         */

        const footer = document.querySelector(".site-footer");

        if (
            footer &&
            isElementInViewport(footer)
        ) {

            footer.classList.add("site-footer-visible");

        }

    }


    /*
     * ---------------------------------------------------------
     * SCROLL LISTENER
     * ---------------------------------------------------------
     */

    window.addEventListener(
        "scroll",
        handleFirstScroll,
        {
            passive: true
        }
    );


    /*
     * ---------------------------------------------------------
     * INTERSECTION OBSERVER
     * ---------------------------------------------------------
     */

    if ("IntersectionObserver" in window) {

        const contentObserver =
            new IntersectionObserver(

                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }


                        /*
                         * Never reveal before actual scrolling.
                         */

                        if (!userHasScrolled) {
                            return;
                        }


                        revealElement(entry.target);


                        /*
                         * Once revealed, stop observing.
                         */

                        observer.unobserve(
                            entry.target
                        );

                    });

                },

                {
                    threshold: 0.12,

                    rootMargin:
                        "0px 0px -70px 0px"
                }

            );


        animationElements.forEach(function (element) {

            contentObserver.observe(element);

        });

    }


    /*
     * ---------------------------------------------------------
     * FOOTER OBSERVER
     * ---------------------------------------------------------
     */

    const footer =
        document.querySelector(".site-footer");


    if (
        footer &&
        "IntersectionObserver" in window
    ) {

        const footerObserver =
            new IntersectionObserver(

                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }


                        if (!userHasScrolled) {
                            return;
                        }


                        footer.classList.add(
                            "site-footer-visible"
                        );


                        observer.unobserve(
                            entry.target
                        );

                    });

                },

                {
                    threshold: 0.08,

                    rootMargin:
                        "0px 0px -50px 0px"
                }

            );


        footerObserver.observe(footer);

    }


    /*
     * ---------------------------------------------------------
     * SAFETY CHECK
     *
     * Cards must never be controlled by this animation system.
     * ---------------------------------------------------------
     */

    document
        .querySelectorAll(".stack-cards > *")
        .forEach(function (card) {

            card.classList.remove(
                "site-scroll-animation",
                "site-scroll-heading",
                "site-scroll-paragraph",
                "site-scroll-image",
                "site-scroll-button",
                "site-scroll-other",
                "site-scroll-visible"
            );

        });

});
/* =========================================================
   GLOBAL CONTENT SCROLL ANIMATION
   =========================================================

   IMPORTANT:
   - DO NOT TOUCH HERO
   - DO NOT TOUCH STACKING
   - DO NOT TOUCH CARDS
   - DO NOT TOUCH existing .reveal animations

   Only normal page content is animated.

   Animation order:
   Heading
      ↓
   Paragraph
      ↓
   Image / Content
      ↓
   Button
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const animatedContent = [];

    /*
     * Elements that should NEVER be touched.
     */
    const protectedSelectors = [
        ".hero-section",
        ".about-page-hero",
        ".founders-hero",
        ".team-hero",
        ".transparency-hero",
        ".contact-hero",
        ".location-hero",
        ".volunteer-hero",
        ".testimonial-page-hero",
        ".category-hero",
        ".annual-hero",

        /* Cards / stacking */
        ".stack-cards",
        ".story-band__grid",
        ".focus-grid",
        ".founders-grid",
        ".team-grid",
        ".documents-grid",
        ".testimonial-document-grid",
        ".interest-grid",
        ".address-grid",

        /* Navigation */
        "nav",
        ".navbar",
        ".site-navbar",
        "header",

        /* Footer */
        "footer",
        ".site-footer"
    ];


    /*
     * Content elements to animate.
     *
     * This covers practically all visible content without
     * touching structural containers.
     */
    const contentSelector = [
        "main h1",
        "main h2",
        "main h3",
        "main h4",
        "main h5",
        "main h6",

        "main p",

        "main img",
        "main figure",
        "main blockquote",

        "main li",

        "main button",
        "main .btn",
        "main a",

        "main label",

        "main input",
        "main textarea",
        "main select",

        "main table",
        "main th",
        "main td"
    ].join(",");


    const elements =
        document.querySelectorAll(contentSelector);


    /*
     * Check whether an element belongs to a protected
     * area such as hero, cards, navbar or footer.
     */
    function isProtected(element) {

        return protectedSelectors.some(function (selector) {

            return element.closest(selector) !== null;

        });

    }


    /*
     * Check whether element already uses the existing
     * manual reveal system.
     *
     * We NEVER override these animations.
     */
    function alreadyUsesReveal(element) {

        return (
            element.classList.contains("reveal") ||
            element.classList.contains("reveal-left") ||
            element.classList.contains("reveal-right") ||
            element.classList.contains("reveal-scale") ||
            element.classList.contains("active")
        );

    }


    /*
     * Prepare normal content.
     */
    elements.forEach(function (element) {

        /*
         * Don't touch hero.
         */
        if (isProtected(element)) {
            return;
        }


        /*
         * Don't touch manually animated content.
         */
        if (alreadyUsesReveal(element)) {
            return;
        }


        /*
         * Don't animate hidden/template elements.
         */
        const style =
            window.getComputedStyle(element);

        if (
            style.display === "none" ||
            style.visibility === "hidden"
        ) {
            return;
        }


        /*
         * Don't animate empty elements.
         */
        if (
            element.tagName !== "IMG" &&
            element.textContent.trim() === "" &&
            !element.matches("input, textarea, select")
        ) {
            return;
        }


        /*
         * Add common class.
         */
        element.classList.add(
            "global-scroll-content"
        );


        /*
         * Assign animation type.
         */
        if (
            element.matches(
                "h1,h2,h3,h4,h5,h6"
            )
        ) {

            element.classList.add(
                "global-scroll-heading"
            );

        }

        else if (
            element.matches("p")
        ) {

            element.classList.add(
                "global-scroll-paragraph"
            );

        }

        else if (
            element.matches(
                "img,figure"
            )
        ) {

            element.classList.add(
                "global-scroll-image"
            );

        }

        else if (
            element.matches(
                "button,.btn,a"
            )
        ) {

            element.classList.add(
                "global-scroll-button"
            );

        }

        else {

            element.classList.add(
                "global-scroll-other"
            );

        }


        animatedContent.push(element);

    });


    /*
     * =====================================================
     * INITIAL STATE
     * =====================================================
     *
     * Everything is hidden.
     *
     * IMPORTANT:
     * We DO NOT reveal anything on page load.
     */
    animatedContent.forEach(function (element) {

        element.classList.remove(
            "global-scroll-visible"
        );

    });


    /*
     * =====================================================
     * REDUCED MOTION
     * =====================================================
     */

    const reducedMotion =
        window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;


    if (reducedMotion) {

        animatedContent.forEach(function (element) {

            element.classList.add(
                "global-scroll-visible"
            );

        });

        return;

    }


    /*
     * =====================================================
     * FIRST SCROLL
     * =====================================================
     *
     * Nothing appears until the user actually scrolls.
     */
    let hasScrolled = false;


    function firstScroll() {

        if (hasScrolled) {
            return;
        }

        hasScrolled = true;

        /*
         * Reveal content currently visible on screen.
         */
        animatedContent.forEach(function (element) {

            if (
                element.classList.contains(
                    "global-scroll-visible"
                )
            ) {
                return;
            }


            const rect =
                element.getBoundingClientRect();


            if (
                rect.top <
                window.innerHeight * 0.92 &&
                rect.bottom > 0
            ) {

                element.classList.add(
                    "global-scroll-visible"
                );

            }

        });


        window.removeEventListener(
            "scroll",
            firstScroll
        );

    }


    window.addEventListener(
        "scroll",
        firstScroll,
        {
            passive: true
        }
    );


    /*
     * =====================================================
     * INTERSECTION OBSERVER
     * =====================================================
     *
     * After first scroll, each element animates when it
     * enters the viewport.
     */
    if ("IntersectionObserver" in window) {

        const contentObserver =
            new IntersectionObserver(
                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }


                        /*
                         * Don't reveal before user scrolls.
                         */
                        if (!hasScrolled) {
                            return;
                        }


                        entry.target.classList.add(
                            "global-scroll-visible"
                        );


                        /*
                         * Animate only once.
                         */
                        observer.unobserve(
                            entry.target
                        );

                    });

                },
                {
                    threshold: 0.12,

                    rootMargin:
                        "0px 0px -70px 0px"
                }
            );


        animatedContent.forEach(function (element) {

            contentObserver.observe(
                element
            );

        });

    }

});
/* =========================================================
   OUR IMPACT CONTENT SCROLL ANIMATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const impactSection =
        document.querySelector(".story-band__inner");

    if (!impactSection) return;

    /*
     * If the section is already visible when the page loads,
     * keep it hidden until the user actually scrolls.
     */
    impactSection.classList.remove(
        "impact-content-visible"
    );

    if (
        window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches
    ) {
        impactSection.classList.add(
            "impact-content-visible"
        );
        return;
    }

    let activated = false;

    const activateImpact = () => {

        if (activated) return;

        const rect =
            impactSection.getBoundingClientRect();

        /*
         * Activate when the section comes into view.
         */
        if (
            rect.top < window.innerHeight * 0.85 &&
            rect.bottom > 0
        ) {

            activated = true;

            impactSection.classList.add(
                "impact-content-visible"
            );

            window.removeEventListener(
                "scroll",
                activateImpact
            );
        }
    };

    /*
     * Wait for actual scrolling.
     */
    window.addEventListener(
        "scroll",
        activateImpact,
        { passive: true }
    );

});
/* =========================================================
   SEVARTHA FOUNDATION
   GLOBAL CINEMATIC CONTENT SCROLL ANIMATION

   IMPORTANT:
   - HERO untouched
   - CARD STACK untouched
   - EXISTING .reveal untouched
   - NAVBAR untouched
   - FOOTER untouched

   Animation order:

   EYEBROW
       ↓
   HEADING
       ↓
   SUBHEADING
       ↓
   PARAGRAPH
       ↓
   IMAGE / CONTENT
       ↓
   BUTTON
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const heroSelectors = [
        ".hero-section",
        ".about-page-hero",
        ".founders-hero",
        ".team-hero",
        ".transparency-hero",
        ".contact-hero",
        ".location-hero",
        ".volunteer-hero",
        ".testimonial-page-hero",
        ".category-hero",
        ".annual-hero"
    ];

    const cardSelectors = [
        ".story-band__grid",
        ".focus-grid",
        ".founders-grid",
        ".team-grid",
        ".documents-grid",
        ".testimonial-document-grid",
        ".interest-grid",
        ".address-grid",
        ".stack-cards"
    ];


    /* ---------------------------------------------------------
       CHECK PROTECTED ELEMENT
       --------------------------------------------------------- */

    function isProtected(element) {

        /* Existing reveal animation */
        if (
            element.classList.contains("reveal") ||
            element.classList.contains("reveal-left") ||
            element.classList.contains("reveal-right") ||
            element.classList.contains("reveal-scale")
        ) {
            return true;
        }


        /* Hero */
        for (const selector of heroSelectors) {

            if (
                element.closest(selector)
            ) {
                return true;
            }
        }


        /* Card stacking */
        for (const selector of cardSelectors) {

            if (
                element.closest(selector)
            ) {
                return true;
            }
        }


        /* Navigation */
        if (
            element.closest("nav") ||
            element.closest(".navbar") ||
            element.closest("header")
        ) {
            return true;
        }


        /* Footer */
        if (
            element.closest("footer") ||
            element.closest(".site-footer")
        ) {
            return true;
        }


        return false;
    }


    /* ---------------------------------------------------------
       FIND NORMAL CONTENT
       --------------------------------------------------------- */

    const selector = [
        "main h1",
        "main h2",
        "main h3",
        "main h4",
        "main h5",
        "main h6",

        "main p",

        "main img",
        "main figure",

        "main blockquote",

        "main li",

        "main .eyebrow",
        "main .section-label",
        "main .section-title",
        "main .section-subtitle",

        "main .btn",
        "main .button",
        "main button",

        "main input",
        "main textarea",
        "main select",

        "main table"
    ].join(",");


    const elements =
        document.querySelectorAll(selector);


    /* ---------------------------------------------------------
       ASSIGN ANIMATION TYPE
       --------------------------------------------------------- */

    elements.forEach(function (element) {

        if (isProtected(element)) {
            return;
        }


        /* Don't animate empty elements */

        if (
            element.tagName !== "IMG" &&
            element.textContent.trim() === "" &&
            !element.matches(
                "input, textarea, select"
            )
        ) {
            return;
        }


        element.classList.add(
            "sf-scroll-item"
        );


        /* EYEBROW */

        if (
            element.matches(
                ".eyebrow, .section-label"
            )
        ) {

            element.classList.add(
                "sf-scroll-eyebrow"
            );

            return;
        }


        /* MAIN HEADING */

        if (
            element.matches("h1, h2")
        ) {

            element.classList.add(
                "sf-scroll-heading"
            );

            return;
        }


        /* SUBHEADING */

        if (
            element.matches("h3, h4, h5, h6")
        ) {

            element.classList.add(
                "sf-scroll-subheading"
            );

            return;
        }


        /* PARAGRAPH */

        if (
            element.matches("p")
        ) {

            element.classList.add(
                "sf-scroll-paragraph"
            );

            return;
        }


        /* IMAGE */

        if (
            element.matches(
                "img, figure"
            )
        ) {

            element.classList.add(
                "sf-scroll-image"
            );

            return;
        }


        /* BUTTON */

        if (
            element.matches(
                ".btn, .button, button"
            )
        ) {

            element.classList.add(
                "sf-scroll-button"
            );

            return;
        }


        /* EVERYTHING ELSE */

        element.classList.add(
            "sf-scroll-other"
        );

    });


    /* ---------------------------------------------------------
       REDUCED MOTION
       --------------------------------------------------------- */

    if (
        window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches
    ) {

        elements.forEach(function (element) {

            if (
                element.classList.contains(
                    "sf-scroll-item"
                )
            ) {

                element.classList.add(
                    "sf-scroll-visible"
                );
            }

        });

        return;
    }


    /* ---------------------------------------------------------
       INTERSECTION OBSERVER
       --------------------------------------------------------- */

    if (
        !("IntersectionObserver" in window)
    ) {

        elements.forEach(function (element) {

            if (
                element.classList.contains(
                    "sf-scroll-item"
                )
            ) {

                element.classList.add(
                    "sf-scroll-visible"
                );
            }

        });

        return;
    }


    const observer =
        new IntersectionObserver(
            function (entries, observer) {

                entries.forEach(function (entry) {

                    if (
                        !entry.isIntersecting
                    ) {
                        return;
                    }


                    const element =
                        entry.target;


                    element.classList.add(
                        "sf-scroll-visible"
                    );


                    observer.unobserve(
                        element
                    );

                });

            },
            {
                threshold: 0.12,

                rootMargin:
                    "0px 0px -80px 0px"
            }
        );


    /* ---------------------------------------------------------
       START OBSERVING
       --------------------------------------------------------- */

    elements.forEach(function (element) {

        if (
            !element.classList.contains(
                "sf-scroll-item"
            )
        ) {
            return;
        }


        observer.observe(element);

    });

});