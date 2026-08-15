document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("contactForm");
    const messageBox = document.getElementById("contactFormMessage");


    if (!form) {
        return;
    }


    form.addEventListener("submit", function (event) {

        event.preventDefault();


        const name = document
            .getElementById("name")
            .value
            .trim();

        const email = document
            .getElementById("email")
            .value
            .trim();

        const phone = document
            .getElementById("phone")
            .value
            .trim();

        const message = document
            .getElementById("message")
            .value
            .trim();


        clearMessage();


        /* -----------------------------------------
           NAME VALIDATION
        ----------------------------------------- */

        if (name.length < 2) {

            showError(
                "Please enter your name."
            );

            document.getElementById("name").focus();

            return;
        }


        /* -----------------------------------------
           EMAIL VALIDATION
        ----------------------------------------- */

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


        if (!emailPattern.test(email)) {

            showError(
                "Please enter a valid email address."
            );

            document.getElementById("email").focus();

            return;
        }


        /* -----------------------------------------
           PHONE VALIDATION
        ----------------------------------------- */

        const cleanPhone =
            phone.replace(/[\s\-()]/g, "");


        const phonePattern =
            /^(?:\+91|91)?[6-9]\d{9}$/;


        if (!phonePattern.test(cleanPhone)) {

            showError(
                "Please enter a valid Indian mobile number."
            );

            document.getElementById("phone").focus();

            return;
        }


        /* -----------------------------------------
           MESSAGE VALIDATION
        ----------------------------------------- */

        if (message.length < 10) {

            showError(
                "Please enter a message of at least 10 characters."
            );

            document.getElementById("message").focus();

            return;
        }


        /* -----------------------------------------
           SUCCESS
        ----------------------------------------- */

        messageBox.className =
            "contact-form-message success";

        messageBox.innerHTML = `
            <i class="fa-solid fa-circle-check"></i>
            <span>
                Thank you for contacting Sevartha Foundation.
                Your message is ready to be submitted.
            </span>
        `;


        /* -----------------------------------------
           TEMPORARY BUTTON STATE
        ----------------------------------------- */

        const button =
            form.querySelector(".contact-submit");


        if (button) {

            const originalText =
                button.innerHTML;


            button.innerHTML = `
                <i class="fa-solid fa-check"></i>
                MESSAGE READY
            `;


            button.disabled = true;


            setTimeout(function () {

                button.innerHTML =
                    originalText;

                button.disabled = false;

            }, 4000);

        }

    });


    /* ---------------------------------------------
       SHOW ERROR
    --------------------------------------------- */

    function showError(text) {

        messageBox.className =
            "contact-form-message error";

        messageBox.innerHTML = `
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>${text}</span>
        `;

        messageBox.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });

    }


    /* ---------------------------------------------
       CLEAR MESSAGE
    --------------------------------------------- */

    function clearMessage() {

        messageBox.className =
            "contact-form-message";

        messageBox.innerHTML = "";

    }

});