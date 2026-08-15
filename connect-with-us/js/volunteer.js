document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("volunteerForm");
    const formMessage = document.getElementById("formMessage");


    if (!form) {
        return;
    }


    form.addEventListener("submit", function (event) {

        event.preventDefault();


        /*
        --------------------------------------------------
        BASIC FORM VALIDATION
        --------------------------------------------------
        */

        const interests = form.querySelectorAll(
            'input[name="interest[]"]:checked'
        );

        const availability = form.querySelector(
            'input[name="availability"]:checked'
        );

        const previousExperience = form.querySelector(
            'input[name="previous_experience"]:checked'
        );


        /*
        --------------------------------------------------
        CLEAR PREVIOUS MESSAGE
        --------------------------------------------------
        */

        formMessage.className = "form-message";
        formMessage.textContent = "";


        /*
        --------------------------------------------------
        CHECK INTEREST
        --------------------------------------------------
        */

        if (interests.length === 0) {

            showError(
                "Please select at least one area where you would like to volunteer."
            );

            return;
        }


        /*
        --------------------------------------------------
        CHECK AVAILABILITY
        --------------------------------------------------
        */

        if (!availability) {

            showError(
                "Please select your availability."
            );

            return;
        }


        /*
        --------------------------------------------------
        CHECK PREVIOUS EXPERIENCE
        --------------------------------------------------
        */

        if (!previousExperience) {

            showError(
                "Please let us know whether you have volunteered before."
            );

            return;
        }


        /*
        --------------------------------------------------
        SUCCESS MESSAGE
        --------------------------------------------------
        */

        formMessage.className =
            "form-message success";

        formMessage.innerHTML = `
            <i class="fa-solid fa-circle-check"></i>
            <span>
                Thank you for your interest in volunteering
                with Sevartha Foundation. Your application
                is ready to be submitted.
            </span>
        `;


        /*
        --------------------------------------------------
        TEMPORARY BUTTON CHANGE
        --------------------------------------------------
        */

        const button = form.querySelector(
            ".volunteer-submit"
        );

        const originalButtonText =
            button.innerHTML;


        button.innerHTML = `
            <i class="fa-solid fa-check"></i>
            APPLICATION READY
        `;


        button.disabled = true;


        /*
        --------------------------------------------------
        TEMPORARY FRONTEND DEMO
        --------------------------------------------------
        */

        setTimeout(function () {

            button.innerHTML =
                originalButtonText;

            button.disabled = false;

        }, 4000);

    });



    /*
    ------------------------------------------------------
    ERROR FUNCTION
    ------------------------------------------------------
    */

    function showError(message) {

        formMessage.className =
            "form-message error";

        formMessage.innerHTML = `
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>${message}</span>
        `;


        formMessage.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });

    }

});