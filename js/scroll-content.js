/* =========================================================
   SEVARTHA FOUNDATION
   SCROLL CONTENT REVEAL
   INDEPENDENT FROM CARD STACK ANIMATIONS
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const animatedElements = document.querySelectorAll(
        ".scroll-content, " +
        ".scroll-heading, " +
        ".scroll-paragraph, " +
        ".scroll-icon, " +
        ".scroll-quote"
    );


    if (!animatedElements.length) {
        return;
    }


    const observer = new IntersectionObserver(

        function (entries, observer) {

            entries.forEach(function (entry) {

                if (!entry.isIntersecting) {
                    return;
                }


                entry.target.classList.add("scroll-visible");


                observer.unobserve(entry.target);

            });

        },

        {
            threshold: 0.15,

            rootMargin: "0px 0px -60px 0px"
        }

    );


    animatedElements.forEach(function (element) {

        observer.observe(element);

    });

});
/* =========================================================
   CONTACT PAGE - EMAIL & PHONE CARD ANIMATION
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const contactCards = document.querySelectorAll(
        ".contact-card-left, .contact-card-right"
    );

    if (!contactCards.length) return;

    const contactCardObserver = new IntersectionObserver(
        function (entries, observer) {

            entries.forEach(function (entry) {

                if (!entry.isIntersecting) return;

                entry.target.classList.add("contact-card-visible");

                observer.unobserve(entry.target);
            });

        },
        {
            threshold: 0.2,
            rootMargin: "0px 0px -60px 0px"
        }
    );

    contactCards.forEach(function (card) {
        contactCardObserver.observe(card);
    });

});