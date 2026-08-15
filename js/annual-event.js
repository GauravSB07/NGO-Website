/* =========================================================
   SEVARTHA FOUNDATION
   ANNUAL EVENT GALLERY
========================================================= */


document.addEventListener("DOMContentLoaded", function () {


    /* =====================================================
       ELEMENTS
    ====================================================== */

    const galleryItems =
        document.querySelectorAll(".gallery-item");

    const featuredButton =
        document.querySelector(".featured-image-button");

    const loadMoreBtn =
        document.getElementById("loadMoreBtn");

    const galleryCount =
        document.getElementById("galleryCount");

    const lightbox =
        document.getElementById("galleryLightbox");

    const lightboxImage =
        document.getElementById("lightboxImage");

    const lightboxName =
        document.getElementById("lightboxName");

    const lightboxCounter =
        document.getElementById("lightboxCounter");

    const lightboxClose =
        document.getElementById("lightboxClose");

    const lightboxPrev =
        document.getElementById("lightboxPrev");

    const lightboxNext =
        document.getElementById("lightboxNext");

    const lightboxLoading =
        document.querySelector(".lightbox-loading");


    /* =====================================================
       DATA
    ====================================================== */

    const images =
        Array.isArray(window.annualEventImages)
            ? window.annualEventImages
            : [];


    let currentIndex = 0;

    let visibleImages =
        Math.min(16, images.length);

    const loadAmount = 16;


    /* =====================================================
       UPDATE GALLERY COUNT
    ====================================================== */

    function updateGalleryCount() {

        if (!galleryCount) {
            return;
        }


        const strongTags =
            galleryCount.querySelectorAll("strong");


        if (strongTags.length >= 2) {

            strongTags[0].textContent =
                visibleImages;

            strongTags[1].textContent =
                images.length;

        }

    }


    /* =====================================================
       SHOW NEXT SET OF IMAGES
    ====================================================== */

    function showMoreImages() {

        const nextLimit =
            Math.min(
                visibleImages + loadAmount,
                images.length
            );


        for (
            let i = visibleImages;
            i < nextLimit;
            i++
        ) {

            const item =
                document.querySelector(
                    `.gallery-item[data-index="${i}"]`
                );


            if (item) {

                item.classList.remove(
                    "gallery-item-hidden"
                );

            }

        }


        visibleImages =
            nextLimit;


        updateGalleryCount();


        if (
            visibleImages >= images.length
        ) {

            if (loadMoreBtn) {

                loadMoreBtn.style.display =
                    "none";

            }

        }

    }


    /* =====================================================
       LOAD MORE BUTTON
    ====================================================== */

    if (loadMoreBtn) {

        loadMoreBtn.addEventListener(
            "click",
            showMoreImages
        );

    }


    /* =====================================================
       OPEN LIGHTBOX
    ====================================================== */

    function openLightbox(index) {

        if (
            images.length === 0 ||
            !images[index]
        ) {

            return;

        }


        currentIndex = index;


        lightbox.classList.add(
            "active"
        );


        lightbox.setAttribute(
            "aria-hidden",
            "false"
        );


        document.body.classList.add(
            "lightbox-open"
        );


        loadLightboxImage(
            currentIndex
        );

    }


    /* =====================================================
       LOAD LIGHTBOX IMAGE
    ====================================================== */

    function loadLightboxImage(index) {

        const image =
            images[index];


        if (!image) {
            return;
        }


        lightboxLoading.classList.add(
            "active"
        );


        lightboxImage.style.opacity =
            "0";


        const imageUrl =
            `static_image.php?id=${image.id}`;


        const preload =
            new Image();


        preload.onload = function () {

            lightboxImage.src =
                imageUrl;

            lightboxImage.alt =
                image.name;


            lightboxName.textContent =
                image.name;


            lightboxCounter.textContent =
                `${index + 1} / ${images.length}`;


            lightboxLoading.classList.remove(
                "active"
            );


            lightboxImage.style.opacity =
                "1";

        };


        preload.onerror = function () {

            lightboxLoading.classList.remove(
                "active"
            );

            lightboxImage.style.opacity =
                "1";

        };


        preload.src =
            imageUrl;

    }


    /* =====================================================
       CLOSE LIGHTBOX
    ====================================================== */

    function closeLightbox() {

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


        lightboxImage.src = "";

    }


    /* =====================================================
       NEXT IMAGE
    ====================================================== */

    function showNext() {

        if (images.length === 0) {
            return;
        }


        currentIndex =
            (currentIndex + 1)
            % images.length;


        loadLightboxImage(
            currentIndex
        );

    }


    /* =====================================================
       PREVIOUS IMAGE
    ====================================================== */

    function showPrevious() {

        if (images.length === 0) {
            return;
        }


        currentIndex =
            (currentIndex - 1 + images.length)
            % images.length;


        loadLightboxImage(
            currentIndex
        );

    }


    /* =====================================================
       GALLERY CLICK
    ====================================================== */

    galleryItems.forEach(function (item) {

        item.addEventListener(
            "click",
            function () {

                const index =
                    parseInt(
                        this.dataset.index,
                        10
                    );


                openLightbox(index);

            }
        );

    });


    /* =====================================================
       FEATURED IMAGE CLICK
    ====================================================== */

    if (featuredButton) {

        featuredButton.addEventListener(
            "click",
            function () {

                openLightbox(0);

            }
        );

    }


    /* =====================================================
       BUTTON EVENTS
    ====================================================== */

    if (lightboxClose) {

        lightboxClose.addEventListener(
            "click",
            closeLightbox
        );

    }


    if (lightboxNext) {

        lightboxNext.addEventListener(
            "click",
            showNext
        );

    }


    if (lightboxPrev) {

        lightboxPrev.addEventListener(
            "click",
            showPrevious
        );

    }


    /* =====================================================
       BACKDROP CLICK
    ====================================================== */

    const backdrop =
        document.querySelector(
            ".lightbox-backdrop"
        );


    if (backdrop) {

        backdrop.addEventListener(
            "click",
            closeLightbox
        );

    }


    /* =====================================================
       KEYBOARD CONTROLS
    ====================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                !lightbox.classList.contains(
                    "active"
                )
            ) {

                return;

            }


            if (
                event.key === "Escape"
            ) {

                closeLightbox();

            }


            else if (
                event.key === "ArrowRight"
            ) {

                showNext();

            }


            else if (
                event.key === "ArrowLeft"
            ) {

                showPrevious();

            }

        }
    );


    /* =====================================================
       INITIAL COUNT
    ====================================================== */

    updateGalleryCount();

});