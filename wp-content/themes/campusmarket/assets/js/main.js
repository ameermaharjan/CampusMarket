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

    // ─── Notifications ──────────────────────────────────
    const markAllReadBtn = document.getElementById('cm-mark-all-read');
    const notificationItems = document.querySelectorAll('.cm-notification-item');
    const notificationBadge = document.getElementById('cm-notification-badge');

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            
            $.ajax({
                url: cmData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cm_mark_notification_read',
                    nonce: cmData.nonce,
                    mark_all: true
                },
                success: function (response) {
                    if (response.success) {
                        if (notificationBadge) notificationBadge.remove();
                        markAllReadBtn.remove();
                        const list = document.getElementById('cm-notifications-list');
                        if (list) {
                            list.innerHTML = `
                                <div class="px-4 py-8 text-center" id="cm-no-notifications">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">notifications_off</span>
                                    <p class="text-sm text-slate-500 font-medium">No new notifications</p>
                                </div>
                            `;
                        }
                    }
                }
            });
        });
    }

    notificationItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            // We don't prevent default, we want the link to work
            // But we fire an ajax request in the background
            const id = this.getAttribute('data-id');
            if (!id) return;

            $.ajax({
                url: cmData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cm_mark_notification_read',
                    nonce: cmData.nonce,
                    notification_id: id
                }
            });
        });
    });

    // ─── Footer Social Sharing ──────────────────────────
    const shareModal = document.getElementById('cm-share-modal');
    const shareBtn = document.getElementById('cm-footer-share');
    const copyBtn = document.getElementById('cm-copy-url');
    const shareUrlInput = document.getElementById('cm-share-url');

    // Centralized Copy Helper
    window.cmCopyLink = function(textToCopy, buttonEl, successMessage) {
        const fallbackCopy = (text) => {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
        };

        const copyAction = () => {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(textToCopy);
            } else {
                fallbackCopy(textToCopy);
                return Promise.resolve();
            }
        };

        copyAction().then(() => {
            if (buttonEl) {
                const originalText = buttonEl.textContent;
                buttonEl.textContent = 'COPIED!';
                buttonEl.classList.add('bg-emerald-500', 'text-white');
                setTimeout(() => {
                    buttonEl.textContent = originalText;
                    buttonEl.classList.remove('bg-emerald-500', 'text-white');
                }, 2000);
            }

            // Show toast
            const toast = document.createElement('div');
            toast.className = 'cm-share-toast';
            toast.textContent = successMessage || 'Link copied to clipboard!';
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        });
    };

    if (shareBtn && shareModal) {
        shareBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            const shareData = {
                title: document.title,
                text: 'Check out this on CampusMarket!',
                url: window.location.href
            };

            // Use Web Share API if available (best for mobile)
            if (navigator.share) {
                navigator.share(shareData).catch(() => {
                    // Fallback to modal if share fails or cancelled
                    shareModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                });
            } else {
                shareModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
        });

        const closeElements = shareModal.querySelectorAll('.cm-modal-close');
        closeElements.forEach(el => {
            el.addEventListener('click', () => {
                shareModal.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });

        // Platform Sharing
        const platformBtns = shareModal.querySelectorAll('.cm-share-btn');
        platformBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const platform = this.getAttribute('data-platform');
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent('Check out this on CampusMarket: ');
                let shareLink = '';

                if (platform === 'whatsapp') {
                    shareLink = `https://api.whatsapp.com/send?text=${text}${url}`;
                } else if (platform === 'facebook') {
                    shareLink = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                } else if (platform === 'instagram') {
                    window.cmCopyLink(window.location.href, null, 'Link copied! Paste it on your Instagram Story/Bio.');
                    return;
                }

                if (shareLink) {
                    window.open(shareLink, '_blank', 'width=600,height=400');
                }
            });
        });

        // Copy Link Button
        if (copyBtn) {
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.cmCopyLink(window.location.href, copyBtn);
            });
        }
    }

    // ─── Feedback Modal ─────────────────────────────────
    const feedbackModal = document.getElementById('cm-feedback-modal');
    const openFeedbackBtn = document.getElementById('cm-open-feedback');
    const feedbackForm = document.getElementById('cm-feedback-form');
    const starBtns = document.querySelectorAll('.cm-star-btn');
    const ratingInput = document.getElementById('cm-feedback-rating-input');

    if (openFeedbackBtn && feedbackModal) {
        openFeedbackBtn.addEventListener('click', () => {
            feedbackModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });

        const closeElements = feedbackModal.querySelectorAll('.cm-modal-close');
        closeElements.forEach(el => {
            el.addEventListener('click', () => {
                feedbackModal.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });

        // Star Rating Interactivity
        starBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                ratingInput.value = rating;
                
                // Update stars visual
                starBtns.forEach(sb => {
                    const sbRating = parseInt(sb.getAttribute('data-rating'));
                    if (sbRating <= rating) {
                        sb.classList.remove('text-slate-200');
                        sb.classList.add('text-amber-400');
                    } else {
                        sb.classList.remove('text-amber-400');
                        sb.classList.add('text-slate-200');
                    }
                });
            });
        });

        // Form Submission
        if (feedbackForm) {
            feedbackForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> SENDING...';

                const formData = new FormData(this);
                formData.append('action', 'cm_submit_feedback');
                formData.append('nonce', cmData.nonce);

                $.ajax({
                    url: cmData.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.cmToast(response.data.message, 'success');
                            feedbackForm.reset();
                            // Reset stars
                            starBtns.forEach(sb => {
                                sb.classList.remove('text-amber-400');
                                sb.classList.add('text-slate-200');
                            });
                            ratingInput.value = 5;
                            
                            setTimeout(() => {
                                feedbackModal.classList.remove('is-open');
                                document.body.style.overflow = '';
                            }, 500);
                        } else {
                            window.cmToast(response.data.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function() {
                        window.cmToast('Failed to send feedback. Please try again.', 'error');
                    },
                    complete: function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                });
            });
        }
    }

    // ─── User Reporting Modal ────────────────────────────
    const reportModal = document.getElementById('cm-report-modal');
    const reportForm = document.getElementById('cm-report-form');
    
    $(document).on('click', '.cm-open-report-modal', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
        if (reportModal) {
            $('#cm-report-user-id').val(userId);
            $('#cm-report-user-name').text(userName);
            reportModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    });

    if (reportModal) {
        const closeElements = reportModal.querySelectorAll('.cm-modal-close');
        closeElements.forEach(el => {
            el.addEventListener('click', () => {
                reportModal.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });

        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-xl">sync</span> SUBMITTING...';

                const formData = new FormData(this);
                formData.append('action', 'cm_submit_report');
                formData.append('nonce', cmData.nonce);

                $.ajax({
                    url: cmData.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.cmToast(response.data.message, 'success');
                            reportForm.reset();
                            setTimeout(() => {
                                reportModal.classList.remove('is-open');
                                document.body.style.overflow = '';
                            }, 500);
                        } else {
                            window.cmToast(response.data.message || 'Error submitting report', 'error');
                        }
                    },
                    error: function() {
                        window.cmToast('Network error. Please try again.', 'error');
                    },
                    complete: function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                });
            });
        }
    }

})(jQuery);
