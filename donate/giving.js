/* =========================================================
   SEVARTHA FOUNDATION
   DONATION INTERACTION & LOGIC
   Seamless UI feedback, currency formatting & cause syncing
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
    const motionReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Elements
    const amountInput = document.getElementById("donationAmount");
    const amountButtons = document.querySelectorAll(".donation-amount-option");
    const previewAmount = document.getElementById("givingAmountPreview");
    const previewPurpose = document.getElementById("givingPurposePreview");
    const purposeSelect = document.getElementById("donationPurpose");
    const causeCards = document.querySelectorAll(".donation-area[data-cause]");
    const donationForm = document.getElementById("donationDetailsForm");

    // Currency Formatter (Indian Rupee style: e.g., 2,500)
    const inrFormatter = new Intl.NumberFormat("en-IN", {
        maximumFractionDigits: 0
    });

    /* ---------------------------------------------------------
       1. LIVE AMOUNT PREVIEW & SYNC
    --------------------------------------------------------- */
    const updateAmountPreview = () => {
        if (!amountInput || !previewAmount) return;

        const val = Number(amountInput.value);
        if (val >= 1 && val <= 10000000) {
            previewAmount.textContent = "₹" + inrFormatter.format(val);
        } else {
            previewAmount.textContent = "Your choice";
        }

        if (!motionReduced) {
            previewAmount.classList.remove("amount-updated");
            void previewAmount.offsetWidth; // Trigger reflow
            previewAmount.classList.add("amount-updated");
        }
    };

    if (amountInput) {
        amountInput.addEventListener("input", () => {
            // Deselect preset buttons when typing a custom value
            amountButtons.forEach(btn => {
                btn.classList.remove("selected");
                btn.setAttribute("aria-pressed", "false");
            });
            updateAmountPreview();
        });
    }

    amountButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            amountButtons.forEach(b => {
                b.classList.remove("selected");
                b.setAttribute("aria-pressed", "false");
            });

            this.classList.add("selected");
            this.setAttribute("aria-pressed", "true");

            if (amountInput) {
                amountInput.value = this.dataset.amount;
                updateAmountPreview();
            }
        });
    });

    /* ---------------------------------------------------------
       2. CAUSE CARDS & DROPDOWN TWO-WAY SYNC
    --------------------------------------------------------- */
    const updateCauseSelection = (selectedCause) => {
        causeCards.forEach(card => {
            if (card.dataset.cause === selectedCause) {
                card.classList.add("selected");
                card.setAttribute("aria-selected", "true");
            } else {
                card.classList.remove("selected");
                card.setAttribute("aria-selected", "false");
            }
        });

        if (previewPurpose) {
            previewPurpose.textContent = selectedCause || "General support";
        }
    };

    causeCards.forEach(card => {
        const handleCardSelect = () => {
            const cause = card.dataset.cause;
            if (purposeSelect) {
                purposeSelect.value = cause;
                updateCauseSelection(cause);
            }
        };

        card.addEventListener("click", handleCardSelect);
        card.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                handleCardSelect();
            }
        });
    });

    if (purposeSelect) {
        purposeSelect.addEventListener("change", () => {
            updateCauseSelection(purposeSelect.value);
        });

        // Initialize with default or pre-selected purpose
        if (purposeSelect.value) {
            updateCauseSelection(purposeSelect.value);
        }
    }

    // Initialize amount preview on load
    updateAmountPreview();

    /* ---------------------------------------------------------
       3. FORM VALIDATION & ENHANCEMENT
    --------------------------------------------------------- */
    if (donationForm && amountInput) {
        donationForm.addEventListener("submit", (e) => {
            const amount = Number(amountInput.value);
            if (!amount || amount < 1 || amount > 10000000) {
                e.preventDefault();
                amountInput.focus();
                amountInput.classList.add("is-invalid");
                return;
            }

            // Button loading animation
            const submitBtn = donationForm.querySelector(".donation-continue");
            if (submitBtn) {
                submitBtn.classList.add("loading");
                const span = submitBtn.querySelector("span");
                if (span) {
                    span.textContent = "Processing details...";
                }
            }
        });
    }

    /* ---------------------------------------------------------
       4. INTERSECTION OBSERVER ENTRANCES
    --------------------------------------------------------- */
    if ("IntersectionObserver" in window && !motionReduced) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("giving-in-view");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll(".donation-area, .donation-bottom-inner, .donation-trust-chip").forEach(el => {
            observer.observe(el);
        });
    }
});
