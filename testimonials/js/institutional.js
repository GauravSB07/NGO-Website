document.addEventListener("DOMContentLoaded", function () {

    const modal =
        document.getElementById("testimonialModal");

    const modalImage =
        document.getElementById("testimonialModalImage");

    const modalTitle =
        document.getElementById("testimonialModalTitle");

    const closeButton =
        document.getElementById("testimonialModalClose");


    const buttons =
        document.querySelectorAll(
            ".document-view-button, .document-link-button"
        );


    buttons.forEach(function (button) {

        button.addEventListener("click", function () {

            const image =
                button.getAttribute("data-image");

            const title =
                button.getAttribute("data-title");


            modalImage.src = image;

            modalImage.alt = title;

            modalTitle.textContent = title;


            modal.classList.add("active");

            document.body.style.overflow = "hidden";

        });

    });


    function closeModal() {

        modal.classList.remove("active");

        modalImage.src = "";

        document.body.style.overflow = "";

    }


    closeButton.addEventListener(
        "click",
        closeModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {

                closeModal();

            }

        }
    );


    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                modal.classList.contains("active")
            ) {

                closeModal();

            }

        }
    );

});