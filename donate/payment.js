/* =========================================================
   SEVARTHA FOUNDATION
   PAYMENT & CONFIRMATION INTERACTIONS
   Instant UPI copy, QR fallback, mobile share & validation
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    /* ---------------------------------------------------------
       1. ONE-CLICK UPI COPY
    --------------------------------------------------------- */
    const copyButton = document.getElementById("copyUpi");
    const upiIdElement = document.getElementById("upiId");
    const copyMessage = document.getElementById("copyMessage");

    if (copyButton && upiIdElement) {
        copyButton.addEventListener("click", async () => {
            const upiId = upiIdElement.textContent.trim();

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(upiId);
                } else {
                    // Fallback for non-https or older browsers
                    const textArea = document.createElement("textarea");
                    textArea.value = upiId;
                    textArea.style.position = "fixed";
                    textArea.style.opacity = "0";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    document.execCommand("copy");
                    document.body.removeChild(textArea);
                }

                // Visual feedback
                copyButton.classList.add("copied");
                const originalHtml = copyButton.innerHTML;
                copyButton.innerHTML = '<i class="fa-solid fa-check"></i> <span>Copied!</span>';

                if (copyMessage) {
                    copyMessage.textContent = "UPI ID copied to clipboard.";
                    copyMessage.classList.add("show");
                }

                setTimeout(() => {
                    copyButton.classList.remove("copied");
                    copyButton.innerHTML = originalHtml;
                    if (copyMessage) {
                        copyMessage.classList.remove("show");
                    }
                }, 2500);

            } catch (err) {
                if (copyMessage) {
                    copyMessage.textContent = "Please select and copy the UPI ID manually.";
                    copyMessage.classList.add("show");
                }
            }
        });
    }

    /* ---------------------------------------------------------
       2. QR IMAGE LOAD / FALLBACK
    --------------------------------------------------------- */
    const qrImg = document.getElementById("checkoutQr");
    const qrFallback = document.getElementById("checkoutQrFallback");

    if (qrImg && qrFallback) {
        const handleQrError = () => {
            qrImg.style.display = "none";
            qrFallback.hidden = false;
        };

        qrImg.addEventListener("error", handleQrError);
        if (qrImg.complete && qrImg.naturalWidth === 0) {
            handleQrError();
        }
    }

    /* ---------------------------------------------------------
       3. TRANSACTION ID FORMATTING & SUBMISSION
    --------------------------------------------------------- */
    const txInput = document.getElementById("transaction_id");
    const paymentForm = document.querySelector(".checkout-reference form");

    if (txInput) {
        txInput.addEventListener("input", function () {
            // Trim leading/trailing spaces and keep input clean
            this.value = this.value.replace(/\s+/g, "").toUpperCase();
        });
    }

    if (paymentForm) {
        paymentForm.addEventListener("submit", function (e) {
            const submitBtn = paymentForm.querySelector(".checkout-submit");
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = "0.75";
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Submitting verification...</span>';
            }
        });
    }

    /* ---------------------------------------------------------
       4. SHARE IMPACT (CONFIRMATION SCREEN)
    --------------------------------------------------------- */
    const shareBtn = document.getElementById("shareImpactBtn");
    if (shareBtn) {
        shareBtn.addEventListener("click", async () => {
            const shareTitle = "Supporting Sevartha Foundation";
            const shareText = "I just supported Sevartha Foundation in their mission to provide education, healthcare, and dignity to vulnerable communities. Join me in making a difference!";
            const shareUrl = window.location.origin + "/NGO-Website/donate/donate.php";

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: shareTitle,
                        text: shareText,
                        url: shareUrl
                    });
                } catch (err) {
                    // Share dialog dismissed
                }
            } else {
                // Fallback to WhatsApp Web/App
                const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + " " + shareUrl)}`;
                window.open(whatsappUrl, "_blank", "noopener,noreferrer");
            }
        });
    }
});
