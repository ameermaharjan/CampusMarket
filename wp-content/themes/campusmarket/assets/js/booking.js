/**
 * CampusMarket — Booking JS
 */
(function ($) {
    'use strict';

    const bookingForm = document.getElementById('cm-submit-booking-form');
    if (!bookingForm) return;

    const startDate = document.getElementById('cm-start-date');
    const endDate = document.getElementById('cm-end-date');
    const totalEl = document.getElementById('cm-booking-total');
    const totalPrice = document.getElementById('cm-total-price');
    const priceInput = bookingForm.querySelector('input[name="price"]');
    const priceTypeInput = bookingForm.querySelector('input[name="price_type"]');

    function calculateTotal() {
        if (!startDate.value || !endDate.value) { totalEl.style.display = 'none'; return; }

        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const price = parseFloat(priceInput.value) || 0;
        const type = priceTypeInput.value;

        if (end <= start) { totalEl.style.display = 'none'; return; }

        let total = price;
        const diffMs = end - start;
        const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        if (type === 'per_day') {
            total = Math.max(1, diffDays) * price;
        } else if (type === 'per_hour') {
            total = Math.max(1, diffDays * 24) * price;
        }

        totalEl.style.display = 'flex';
        totalPrice.textContent = 'Rs. ' + total.toFixed(2);
    }

    startDate.addEventListener('change', function () {
        endDate.min = this.value;
        calculateTotal();
    });
    endDate.addEventListener('change', calculateTotal);

    bookingForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const msgEl = document.getElementById('cm-booking-message');
        btn.disabled = true;
        btn.textContent = 'Booking...';

        $.post(cmData.ajaxUrl, {
            action: 'cm_book_item',
            nonce: cmData.nonce,
            listing_id: this.querySelector('[name="listing_id"]').value,
            start_date: startDate.value,
            end_date: endDate.value
        }, function (response) {
            if (response.success) {
                msgEl.className = 'cm-form-message cm-form-message--success';
                msgEl.textContent = response.data.message;
                if (window.cmScrollTo) window.cmScrollTo(msgEl);
            } else {
                msgEl.className = 'cm-form-message cm-form-message--error';
                msgEl.textContent = response.data.message;
                if (window.cmScrollTo) window.cmScrollTo(msgEl);
            }
            btn.disabled = false;
            btn.textContent = '📅 Request Booking';
        });
    });
})(jQuery);
