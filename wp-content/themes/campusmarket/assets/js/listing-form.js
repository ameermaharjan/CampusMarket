/**
 * CampusMarket — Listing Form JS
 */
(function ($) {
    'use strict';

    // ─── Image Preview ──────────────────────────────────
    const imageInput = document.getElementById('cm-listing-image');
    const preview = document.getElementById('cm-image-preview');
    const uploadContent = document.getElementById('cm-upload-content');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (uploadContent) uploadContent.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ─── Intent Toggle (Rent/Sale) ──────────────────────
    const intentRadios = document.querySelectorAll('input[name="listing_intent"]');
    const priceTypeSelect = document.getElementById('cm-listing-price-type');
    const typeRadios = document.querySelectorAll('input[name="listing_type"]');
    const conditionGroup = document.getElementById('cm-condition-group');

    function updatePriceOptions(intent) {
        if (!priceTypeSelect) return;
        const options = priceTypeSelect.options;
        let firstMatch = null;

        for (let i = 0; i < options.length; i++) {
            const optIntent = options[i].getAttribute('data-intent');
            if (optIntent === intent) {
                options[i].style.display = 'block';
                if (!firstMatch) firstMatch = options[i].value;
            } else {
                options[i].style.display = 'none';
            }
        }
        if (firstMatch) priceTypeSelect.value = firstMatch;
    }

    intentRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            updatePriceOptions(this.value);
        });
    });

    // ─── Toggle Condition for Services ──────────────────
    typeRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (conditionGroup) {
                conditionGroup.style.display = this.value === 'service' ? 'none' : 'block';
            }
        });
    });

    // Initialize intent
    const initialIntent = document.querySelector('input[name="listing_intent"]:checked');
    if (initialIntent) updatePriceOptions(initialIntent.value);

    // ─── Form Submission ────────────────────────────────
    const listingForm = document.getElementById('cm-listing-form');
    if (listingForm) {
        listingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('cm-listing-submit');
            const msgEl = document.getElementById('cm-listing-message');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = new FormData(this);
            formData.append('action', 'cm_submit_listing');
            formData.append('nonce', cmData.nonce);

            $.ajax({
                url: cmData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        msgEl.className = 'cm-form-message cm-form-message--success';
                        msgEl.textContent = response.data.message;
                        listingForm.reset();
                        if (preview) { preview.style.display = 'none'; }
                        if (uploadContent) { uploadContent.style.display = 'block'; }
                    } else {
                        msgEl.className = 'cm-form-message cm-form-message--error';
                        msgEl.textContent = response.data.message;
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Listing for Review';
                },
                error: function () {
                    msgEl.className = 'cm-form-message cm-form-message--error';
                    msgEl.textContent = 'An error occurred. Please try again.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Listing for Review';
                }
            });
        });
    }
})(jQuery);
