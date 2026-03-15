/**
 * CampusMarket — Dashboard JS
 */
(function ($) {
    'use strict';

    // ─── Delete Listing ─────────────────────────────────
    $(document).on('click', '.cm-delete-listing', function () {
        if (!confirm('Are you sure you want to delete this listing?')) return;

        var btn = $(this);
        var listingId = btn.data('listing-id');

        $.post(cmData.ajaxUrl, {
            action: 'cm_delete_listing',
            nonce: cmData.nonce,
            listing_id: listingId
        }, function (response) {
            if (response.success) {
                $('#listing-row-' + listingId).fadeOut(300, function () { $(this).remove(); });
            } else {
                alert(response.data.message);
            }
        });
    });

    // ─── Booking Actions ────────────────────────────────
    $(document).on('click', '.cm-booking-action', function () {
        var btn = $(this);
        var bookingId = btn.data('booking-id');
        var action = btn.data('action');

        btn.prop('disabled', true).text('...');

        var ajaxAction = 'cm_update_booking';
        if (action === 'notify_returned') ajaxAction = 'cm_notify_returned';
        if (action === 'confirm_return') ajaxAction = 'cm_confirm_return';
        if (action === 'reject_return') ajaxAction = 'cm_reject_return';

        $.post(cmData.ajaxUrl, {
            action: ajaxAction,
            nonce: cmData.nonce,
            booking_id: bookingId,
            status: action
        }, function (response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
                btn.prop('disabled', false);
            }
        });
    });

    // ─── Admin: Approve/Reject Listing ──────────────────
    $(document).on('click', '.cm-admin-approve', function () {
        var btn = $(this);
        var listingId = btn.data('listing-id');
        var approvalAction = btn.data('action');

        btn.prop('disabled', true);

        $.post(cmData.ajaxUrl, {
            action: 'cm_approve_listing',
            nonce: cmData.nonce,
            listing_id: listingId,
            approval_action: approvalAction
        }, function (response) {
            if (response.success) {
                $('#admin-listing-' + listingId).fadeOut(300, function () { $(this).remove(); });
            } else {
                alert(response.data.message);
                btn.prop('disabled', false);
            }
        });
    });

    // ─── Admin: Verify User ─────────────────────────────
    $(document).on('click', '.cm-admin-verify', function () {
        var btn = $(this);
        var userId = btn.data('user-id');
        var verify = btn.data('verify');
        var remarks = '';

        if (verify == '0') {
            remarks = prompt('Please enter remarks/reason for rejection:');
            if (remarks === null) {
                return; // Admin cancelled
            }
            if (remarks.trim() === '') {
                alert('Rejection remarks are mandatory.');
                return;
            }
        }

        btn.prop('disabled', true);

        $.post(cmData.ajaxUrl, {
            action: 'cm_verify_user',
            nonce: cmData.nonce,
            user_id: userId,
            verify: verify,
            remarks: remarks
        }, function (response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
                btn.prop('disabled', false);
            }
        });
    });

})(jQuery);
