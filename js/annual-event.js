document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const lightbox = document.getElementById("galleryLightbox");
    const lightboxImage = document.getElementById("lightboxImage");
    const lightboxName = document.getElementById("lightboxName");
    const lightboxCounter = document.getElementById("lightboxCounter");

    const lightboxClose = document.getElementById("lightboxClose");
    const lightboxPrev = document.getElementById("lightboxPrev");
    const lightboxNext = document.getElementById("lightboxNext");

    const featuredButton =
        document.querySelector(".featured-image-button");

    const galleryItems =
        document.querySelectorAll(".gallery-item");

    const loadMoreBtn =
        document.getElementById("loadMoreBtn");

    const galleryCount =
        document.getElementById("galleryCount");


    /*
    |--------------------------------------------------------------------------
    | CHECK IMAGE DATA
    |--------------------------------------------------------------------------
    */

    if (
        typeof annualEventImages === "undefined" ||
        !Array.isArray(annualEventImages)
    ) {
        console.error(
            "Annual event image data was not found."
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT IMAGE
    |--------------------------------------------------------------------------
    */

    let currentIndex = 0;


    /*
    |--------------------------------------------------------------------------
    | OPEN LIGHTBOX
    |--------------------------------------------------------------------------
    */

    function openLightbox(index) {

        if (
            annualEventImages.length === 0
        ) {
            return;
        }


        /*
        | Make sure index stays valid
        */

        if (index < 0) {
            index =
                annualEventImages.length - 1;
        }

        if (
            index >= annualEventImages.length
        ) {
            index = 0;
        }


        currentIndex = index;


        const image =
            annualEventImages[currentIndex];


        /*
        | Show loading state
        */

        if (lightbox) {

            lightbox.classList.add("active");

            lightbox.setAttribute(
                "aria-hidden",
                "false"
            );

        }


        document.body.classList.add(
            "lightbox-open"
        );


        /*
        | Load image
        */

        if (lightboxImage) {

            lightboxImage.classList.add(
                "loading"
            );


            lightboxImage.src =
                "static_image.php?id="
                + encodeURIComponent(image.id);


            lightboxImage.alt =
                image.name || "Annual Event Photograph";


            lightboxImage.onload =
                function () {

                    lightboxImage.classList.remove(
                        "loading"
                    );

                };


            lightboxImage.onerror =
                function () {

                    lightboxImage.classList.remove(
                        "loading"
                    );

                    console.error(
                        "Unable to load image:",
                        image.id
                    );

                };

        }


        /*
        | Image name
        */

        if (lightboxName) {

            lightboxName.textContent =
                image.name || "";

        }


        /*
        | Counter
        */

        if (lightboxCounter) {

            lightboxCounter.textContent =
                (currentIndex + 1)
                + " / "
                + annualEventImages.length;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CLOSE LIGHTBOX
    |--------------------------------------------------------------------------
    */

    function closeLightbox() {

        if (!lightbox) {
            return;
        }


        lightbox.classList.remove(
            "active"
        );


        lightbox.setAttribute(
            "aria-hidden",
            "true"
        );


        document.body.classList.remove(
            "lightbox-open"
        );


        /*
        | Clear image after closing
        */

        if (lightboxImage) {

            setTimeout(
                function () {

                    lightboxImage.src = "";

                },
                200
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | NEXT IMAGE
    |--------------------------------------------------------------------------
    */

    function showNextImage() {

        openLightbox(
            currentIndex + 1
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVIOUS IMAGE
    |--------------------------------------------------------------------------
    */

    function showPreviousImage() {

        openLightbox(
            currentIndex - 1
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FEATURED "VIEW GALLERY" BUTTON
    |--------------------------------------------------------------------------
    */

    if (featuredButton) {

        featuredButton.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                const index =
                    parseInt(
                        this.dataset.lightboxIndex,
                        10
                    );


                openLightbox(
                    Number.isNaN(index)
                        ? 0
                        : index
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | GALLERY IMAGE BUTTONS
    |--------------------------------------------------------------------------
    */

    galleryItems.forEach(
        function (item) {

            item.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();


                    const index =
                        parseInt(
                            this.dataset.index,
                            10
                        );


                    if (
                        Number.isNaN(index)
                    ) {
                        return;
                    }


                    openLightbox(index);

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLOSE BUTTON
    |--------------------------------------------------------------------------
    */

    if (lightboxClose) {

        lightboxClose.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                closeLightbox();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVIOUS BUTTON
    |--------------------------------------------------------------------------
    */

    if (lightboxPrev) {

        lightboxPrev.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                showPreviousImage();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | NEXT BUTTON
    |--------------------------------------------------------------------------
    */

    if (lightboxNext) {

        lightboxNext.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                showNextImage();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLICK BACKDROP TO CLOSE
    |--------------------------------------------------------------------------
    */

    const lightboxBackdrop =
        document.querySelector(
            ".lightbox-backdrop"
        );


    if (lightboxBackdrop) {

        lightboxBackdrop.addEventListener(
            "click",
            function () {

                closeLightbox();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | KEYBOARD CONTROLS
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                !lightbox ||
                !lightbox.classList.contains("active")
            ) {
                return;
            }


            /*
            | ESC
            */

            if (
                event.key === "Escape"
            ) {

                closeLightbox();

            }


            /*
            | RIGHT ARROW
            */

            else if (
                event.key === "ArrowRight"
            ) {

                showNextImage();

            }


            /*
            | LEFT ARROW
            */

            else if (
                event.key === "ArrowLeft"
            ) {

                showPreviousImage();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | LOAD MORE PHOTOS
    |--------------------------------------------------------------------------
    */

    if (loadMoreBtn) {

        let visibleImages = 16;

        const increment =
            16;


        loadMoreBtn.addEventListener(
            "click",
            function () {

                const hiddenItems =
                    document.querySelectorAll(
                        ".gallery-item-hidden"
                    );


                let revealed = 0;


                hiddenItems.forEach(
                    function (item) {

                        if (
                            revealed < increment
                        ) {

                            item.classList.remove(
                                "gallery-item-hidden"
                            );

                            revealed++;

                        }

                    }
                );


                visibleImages += revealed;


                /*
                | Update counter
                */

                if (galleryCount) {

                    galleryCount.innerHTML =
                        "Showing "
                        + "<strong>"
                        + Math.min(
                            visibleImages,
                            annualEventImages.length
                        )
                        + "</strong>"
                        + " of "
                        + "<strong>"
                        + annualEventImages.length
                        + "</strong>"
                        + " photos";

                }


                /*
                | Hide button when all images
                | are visible
                */

                if (
                    visibleImages >=
                    annualEventImages.length
                ) {

                    loadMoreBtn.style.display =
                        "none";

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TOUCH SWIPE
    |--------------------------------------------------------------------------
    */

    let touchStartX = 0;
    let touchEndX = 0;


    if (lightbox) {

        lightbox.addEventListener(
            "touchstart",
            function (event) {

                if (
                    event.touches.length > 0
                ) {

                    touchStartX =
                        event.touches[0].clientX;

                }

            },
            {
                passive: true
            }
        );


        lightbox.addEventListener(
            "touchend",
            function (event) {

                if (
                    event.changedTouches.length > 0
                ) {

                    touchEndX =
                        event.changedTouches[0].clientX;

                }


                const difference =
                    touchStartX - touchEndX;


                /*
                | Swipe left
                */

                if (
                    difference > 50
                ) {

                    showNextImage();

                }


                /*
                | Swipe right
                */

                else if (
                    difference < -50
                ) {

                    showPreviousImage();

                }

            },
            {
                passive: true
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PRELOAD IMAGES
    |--------------------------------------------------------------------------
    */

    annualEventImages.forEach(
        function (image) {

            const preload =
                new Image();


            preload.src =
                "static_image.php?id="
                + encodeURIComponent(image.id);

        }
    );


    console.log(
        "Annual Event Gallery loaded:",
        annualEventImages.length,
        "images"
    );

});