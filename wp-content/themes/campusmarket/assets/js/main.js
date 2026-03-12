/**
 * CampusMarket — Main JavaScript
 */
(function ($) {
    'use strict';

    // ─── Mobile Menu Toggle ─────────────────────────────
    const hamburger = document.getElementById('cm-hamburger');
    const mainNav = document.getElementById('cm-main-nav');

    if (hamburger && mainNav) {
        hamburger.addEventListener('click', function () {
            mainNav.classList.toggle('is-open');
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
        });
    }

    // ─── User Dropdown ──────────────────────────────────
    const userToggle = document.getElementById('cm-user-toggle');
    const userDropdown = document.getElementById('cm-user-dropdown');

    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.classList.toggle('is-open');
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
        });

        document.addEventListener('click', function () {
            userDropdown.classList.remove('is-open');
            if (userToggle) userToggle.setAttribute('aria-expanded', 'false');
        });
    }

    // ─── Star Rating Input ──────────────────────────────
    const starInput = document.getElementById('cm-star-input');
    if (starInput) {
        const stars = starInput.querySelectorAll('.cm-star-input__star');
        const ratingInput = document.getElementById('cm-rating-input');

        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                stars.forEach(function (s, i) {
                    s.textContent = i < rating ? '★' : '☆';
                    s.classList.toggle('active', i < rating);
                });
            });

            star.addEventListener('mouseenter', function () {
                const rating = this.getAttribute('data-rating');
                stars.forEach(function (s, i) {
                    s.textContent = i < rating ? '★' : '☆';
                });
            });
        });

        starInput.addEventListener('mouseleave', function () {
            const current = ratingInput.value;
            stars.forEach(function (s, i) {
                s.textContent = i < current ? '★' : '☆';
                s.classList.toggle('active', i < current);
            });
        });

        // Initialize
        stars.forEach(function (s, i) {
            s.textContent = i < 5 ? '★' : '☆';
            s.classList.add('active');
        });
    }

    // ─── Review Form Submit ─────────────────────────────
    const reviewForm = document.getElementById('cm-submit-review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'cm_submit_review');
            formData.append('nonce', cmData.nonce);

            $.ajax({
                url: cmData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    const msg = document.getElementById('cm-review-message');
                    if (response.success) {
                        msg.className = 'cm-form-message cm-form-message--success';
                        msg.textContent = response.data.message;
                        reviewForm.reset();
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        msg.className = 'cm-form-message cm-form-message--error';
                        msg.textContent = response.data.message;
                    }
                }
            });
        });
    }

    // ─── Smooth Scroll Utility ──────────────────────────
    window.cmScrollTo = function (target, offset = 100) {
        const element = typeof target === 'string' ? document.querySelector(target) : target;
        if (!element) return;

        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    };

    // ─── Toast Notification ─────────────────────────────
    window.cmToast = function (message, type) {
        const toast = document.createElement('div');
        toast.className = 'cm-toast cm-toast--' + (type || 'info');
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function () { toast.classList.add('cm-toast--show'); }, 10);
        setTimeout(function () {
            toast.classList.remove('cm-toast--show');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    };

})(jQuery);
