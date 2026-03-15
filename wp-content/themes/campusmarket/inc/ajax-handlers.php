<?php

/**
 * AJAX Handlers
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * ─── FILTER LISTINGS (BROWSE PAGE) ─────────────────────
 */
function cm_ajax_filter_listings()
{
    check_ajax_referer('cm_nonce', 'nonce');

    $category  = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
    $condition = isset($_POST['condition']) ? sanitize_text_field(wp_unslash($_POST['condition'])) : '';
    $type      = isset($_POST['listing_type']) ? sanitize_text_field(wp_unslash($_POST['listing_type'])) : '';
    $sort      = isset($_POST['sort']) ? sanitize_text_field(wp_unslash($_POST['sort'])) : 'date';
    $paged     = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $search    = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

    $args = array(
        'post_type'      => 'cm_listing',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => '_cm_approval_status',
                'value' => 'approved',
            ),
        ),
    );

    if (! empty($search)) {
        $args['s'] = $search;
    }

    // Category filter
    if ($category > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'listing_category',
                'field'    => 'term_id',
                'terms'    => $category,
            ),
        );
    }

    // Price filters
    if ($min_price > 0) {
        $args['meta_query'][] = array(
            'key'     => '_cm_price',
            'value'   => $min_price,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }
    if ($max_price > 0) {
        $args['meta_query'][] = array(
            'key'     => '_cm_price',
            'value'   => $max_price,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
    }

    // Condition filter
    if (! empty($condition)) {
        $args['meta_query'][] = array(
            'key'   => '_cm_condition',
            'value' => $condition,
        );
    }

    // Type filter
    if (! empty($type)) {
        $args['meta_query'][] = array(
            'key'   => '_cm_listing_type',
            'value' => $type,
        );
    }

    // Sorting
    switch ($sort) {
        case 'price_low':
            $args['meta_key'] = '_cm_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
        case 'price_high':
            $args['meta_key'] = '_cm_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'oldest':
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
            break;
        default: // newest
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/listing-card');
        }
    } else {
        echo '<div class="cm-empty-state"><div class="cm-empty-state__icon">📭</div>';
        echo '<h3>' . esc_html__('No listings found', 'campusmarket') . '</h3>';
        echo '<p>' . esc_html__('Try adjusting your filters or search terms.', 'campusmarket') . '</p></div>';
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success(array(
        'html'       => $html,
        'found'      => $query->found_posts,
        'max_pages'  => $query->max_num_pages,
        'current'    => $paged,
    ));
}
add_action('wp_ajax_cm_filter_listings', 'cm_ajax_filter_listings');
add_action('wp_ajax_nopriv_cm_filter_listings', 'cm_ajax_filter_listings');

/**
 * ─── SUBMIT LISTING (FRONTEND FORM) ───────────────────
 */
function cm_ajax_submit_listing()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $listing_id  = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
    $title       = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
    $description = wp_kses_post(wp_unslash($_POST['description'] ?? ''));
    $price       = floatval($_POST['price'] ?? 0);
    $price_type  = sanitize_text_field(wp_unslash($_POST['price_type'] ?? 'per_day'));
    $condition   = sanitize_text_field(wp_unslash($_POST['condition'] ?? 'good'));
    $location    = sanitize_text_field(wp_unslash($_POST['location'] ?? ''));
    $type        = sanitize_text_field(wp_unslash($_POST['listing_type'] ?? 'item'));
    $intent      = sanitize_text_field(wp_unslash($_POST['listing_intent'] ?? 'sale'));
    $category    = intval($_POST['category'] ?? 0);
    $avail_start = sanitize_text_field(wp_unslash($_POST['availability_start'] ?? ''));
    $avail_end   = sanitize_text_field(wp_unslash($_POST['availability_end'] ?? ''));
    $item_status = sanitize_text_field(wp_unslash($_POST['item_status'] ?? 'active'));

    if (empty($title) || empty($description)) {
        wp_send_json_error(array('message' => __('Title and description are required.', 'campusmarket')));
    }

    if ($listing_id) {
        // Update existing listing
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'cm_listing') {
            wp_send_json_error(array('message' => __('Listing not found.', 'campusmarket')));
        }
        if ((int)$listing->post_author !== get_current_user_id() && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'campusmarket')));
        }

        $post_id = wp_update_post(array(
            'ID'           => $listing_id,
            'post_title'   => $title,
            'post_content' => $description,
        ));
        $success_msg = __('Listing updated successfully!', 'campusmarket');
    } else {
        // Create new listing
        $post_id = wp_insert_post(array(
            'post_type'    => 'cm_listing',
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ));
        $success_msg = __('Listing submitted successfully! It will be visible after admin approval.', 'campusmarket');
        update_post_meta($post_id, '_cm_approval_status', 'pending');
    }

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => $post_id->get_error_message()));
    }

    // Set meta
    update_post_meta($post_id, '_cm_price', $price);
    update_post_meta($post_id, '_cm_price_type', $price_type);
    update_post_meta($post_id, '_cm_condition', $condition);
    update_post_meta($post_id, '_cm_location', $location);
    update_post_meta($post_id, '_cm_listing_type', $type);
    update_post_meta($post_id, '_cm_listing_intent', $intent);
    update_post_meta($post_id, '_cm_availability_start', $avail_start);
    update_post_meta($post_id, '_cm_availability_end', $avail_end);
    update_post_meta($post_id, '_cm_item_status', $item_status);

    // Set category
    if ($category > 0) {
        wp_set_object_terms($post_id, $category, 'listing_category');
    }

    // Handle image upload
    if (! empty($_FILES['listing_image']) && ! empty($_FILES['listing_image']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('listing_image', $post_id);
        if (! is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    wp_send_json_success(array(
        'message' => $success_msg,
        'post_id' => $post_id,
    ));
}
add_action('wp_ajax_cm_submit_listing', 'cm_ajax_submit_listing');

/**
 * ─── BOOK ITEM ─────────────────────────────────────────
 */
function cm_ajax_book_item()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $listing_id = intval($_POST['listing_id'] ?? 0);
    $item_status = get_post_meta($listing_id, '_cm_item_status', true) ?: 'active';
    
    if ($item_status !== 'active') {
        $msg = $item_status === 'sold' ? __('This item has been sold.', 'campusmarket') : __('This item is currently rented.', 'campusmarket');
        wp_send_json_error(array('message' => $msg));
    }

    $result = cm_create_booking(array(
        'listing_id' => $listing_id,
        'renter_id'  => get_current_user_id(),
        'start_date' => sanitize_text_field(wp_unslash($_POST['start_date'] ?? '')),
        'end_date'   => sanitize_text_field(wp_unslash($_POST['end_date'] ?? '')),
    ));

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array(
        'message'    => __('Booking request sent! The owner will confirm your request.', 'campusmarket'),
        'booking_id' => $result,
    ));
}
add_action('wp_ajax_cm_book_item', 'cm_ajax_book_item');

/**
 * ─── UPDATE BOOKING STATUS ─────────────────────────────
 */
function cm_ajax_update_booking()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $booking_id = intval($_POST['booking_id'] ?? 0);
    $new_status = sanitize_text_field(wp_unslash($_POST['status'] ?? ''));

    // Verify the current user is the owner or admin
    $owner_id = get_post_meta($booking_id, '_cm_owner_id', true);
    $renter_id = get_post_meta($booking_id, '_cm_renter_id', true);

    if ((int) $owner_id !== get_current_user_id() && (int) $renter_id !== get_current_user_id() && ! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'campusmarket')));
    }

    $result = cm_update_booking_status($booking_id, $new_status);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    $listing_id = get_post_meta($booking_id, '_cm_listing_id', true);
    $listing_title = get_the_title($listing_id);

    // Automation: If rental is confirmed, mark listing as 'rented'
    if ($new_status === 'confirmed') {
        $intent = get_post_meta($listing_id, '_cm_listing_intent', true);
        if ($intent === 'rent') {
            $end_date = get_post_meta($booking_id, '_cm_end_date', true);
            update_post_meta($listing_id, '_cm_item_status', 'rented');
            update_post_meta($listing_id, '_cm_rented_until', $end_date);
        } else {
            // For sales, mark as sold
            update_post_meta($listing_id, '_cm_item_status', 'sold');
        }
    }

    // Send notification to renter about status change
    $status_text = $new_status === 'confirmed' ? __('approved', 'campusmarket') : $new_status;
    $intent = get_post_meta($listing_id, '_cm_listing_intent', true);
    $type_label = ($intent === 'rent') ? __('booking request', 'campusmarket') : __('buy request', 'campusmarket');
    
    $message = sprintf(__('Your %s for "%s" has been %s.', 'campusmarket'), $type_label, $listing_title, $status_text);
    $link = home_url('/dashboard/?tab=bookings');
    cm_add_notification($renter_id, 'booking_update', $message, $link);

    wp_send_json_success(array('message' => __('Booking status updated.', 'campusmarket')));
}
add_action('wp_ajax_cm_update_booking', 'cm_ajax_update_booking');

/**
 * ─── RENTER: NOTIFY RETURNED ───────────────────────────
 */
function cm_ajax_notify_returned() {
    check_ajax_referer('cm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $booking_id = intval($_POST['booking_id']);
    $renter_id = get_post_meta($booking_id, '_cm_renter_id', true);
    $owner_id = get_post_meta($booking_id, '_cm_owner_id', true);

    if ((int)$renter_id !== get_current_user_id()) {
        wp_send_json_error(array('message' => 'Unauthorized.'));
    }

    update_post_meta($booking_id, '_cm_return_notified', '1');
    
    $listing_id = get_post_meta($booking_id, '_cm_listing_id', true);
    $message = sprintf(__('Student %s has notified that the item "%s" has been returned. Please confirm the return.', 'campusmarket'), get_userdata($renter_id)->display_name, get_the_title($listing_id));
    cm_add_notification($owner_id, 'booking_update', $message, home_url('/dashboard/?tab=listings'));

    wp_send_json_success(array('message' => __('Owner notified! Waiting for confirmation.', 'campusmarket')));
}
add_action('wp_ajax_cm_notify_returned', 'cm_ajax_notify_returned');

/**
 * ─── OWNER: CONFIRM RETURN ─────────────────────────────
 */
function cm_ajax_confirm_return() {
    check_ajax_referer('cm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $booking_id = intval($_POST['booking_id']);
    $owner_id = get_post_meta($booking_id, '_cm_owner_id', true);
    $renter_id = get_post_meta($booking_id, '_cm_renter_id', true);

    if ((int)$owner_id !== get_current_user_id()) {
        wp_send_json_error(array('message' => 'Unauthorized.'));
    }

    // Complete the booking
    cm_update_booking_status($booking_id, 'completed');
    update_post_meta($booking_id, '_cm_return_confirmed', '1');

    // Reset listing to active
    $listing_id = get_post_meta($booking_id, '_cm_listing_id', true);
    update_post_meta($listing_id, '_cm_item_status', 'active');
    delete_post_meta($listing_id, '_cm_rented_until');

    // Notify renter
    $message = sprintf(__('Owner confirmed the return for "%s". Thank you!', 'campusmarket'), get_the_title($listing_id));
    cm_add_notification($renter_id, 'booking_update', $message, home_url('/dashboard/?tab=bookings'));

    wp_send_json_success(array('message' => __('Return confirmed! Listing is now active again.', 'campusmarket')));
}
add_action('wp_ajax_cm_confirm_return', 'cm_ajax_confirm_return');

/**
 * ─── OWNER: REJECT RETURN (NOT RECEIVED) ────────────────
 */
function cm_ajax_reject_return() {
    check_ajax_referer('cm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $booking_id = intval($_POST['booking_id']);
    $owner_id = get_post_meta($booking_id, '_cm_owner_id', true);
    $renter_id = get_post_meta($booking_id, '_cm_renter_id', true);

    if ((int)$owner_id !== get_current_user_id()) {
        wp_send_json_error(array('message' => 'Unauthorized.'));
    }

    // Reset return status
    delete_post_meta($booking_id, '_cm_return_notified');
    
    // Notify renter
    $listing_id = get_post_meta($booking_id, '_cm_listing_id', true);
    $message = sprintf(__('Owner of "%s" notified that they have NOT received the item yet. Please ensure the item is returned and notify again.', 'campusmarket'), get_the_title($listing_id));
    cm_add_notification($renter_id, 'booking_update', $message, home_url('/dashboard/?tab=bookings'));

    wp_send_json_success(array('message' => __('Renter notified that the item was not received.', 'campusmarket')));
}
add_action('wp_ajax_cm_reject_return', 'cm_ajax_reject_return');

/**
 * ─── SEND MESSAGE ──────────────────────────────────────
 */
function cm_ajax_send_message()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $result = cm_send_message(array(
        'sender_id'       => get_current_user_id(),
        'receiver_id'     => intval($_POST['receiver_id'] ?? 0),
        'message'         => wp_kses_post(wp_unslash($_POST['message'] ?? '')),
        'conversation_id' => sanitize_text_field(wp_unslash($_POST['conversation_id'] ?? '')),
    ));

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    // Notify the receiver
    $sender = get_userdata(get_current_user_id());
    $raw_message = strip_tags(wp_unslash($_POST['message']));
    $first_char = mb_substr(trim($raw_message), 0, 1);
    $message_preview = $first_char . '...';
    $notification_message = sprintf(__('New message from %s: "%s"', 'campusmarket'), $sender->display_name, $message_preview);
    $link = home_url('/chat/?with=' . get_current_user_id());
    cm_add_notification(intval($_POST['receiver_id']), 'new_message', $notification_message, $link);

    wp_send_json_success(array(
        'message'    => __('Message sent.', 'campusmarket'),
        'message_id' => $result,
    ));
}
add_action('wp_ajax_cm_send_message', 'cm_ajax_send_message');

/**
 * ─── FETCH MESSAGES (POLLING) ──────────────────────────
 */
function cm_ajax_fetch_messages()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $conversation_id = sanitize_text_field(wp_unslash($_POST['conversation_id'] ?? ''));
    $after_id        = intval($_POST['after_id'] ?? 0);
    $current_user_id = get_current_user_id();

    // Mark messages as read
    cm_mark_as_read($conversation_id, $current_user_id);

    $messages = cm_get_conversation($conversation_id, $after_id);
    $data     = array();

    if ($messages->have_posts()) {
        while ($messages->have_posts()) {
            $messages->the_post();
            $sender_id = (int) get_post_meta(get_the_ID(), '_cm_sender_id', true);
            $data[] = array(
                'id'        => get_the_ID(),
                'content'   => get_the_content(),
                'sender_id' => $sender_id,
                'is_mine'   => $sender_id === $current_user_id,
                'sender'    => get_userdata($sender_id)->display_name,
                'avatar'    => get_avatar_url($sender_id, array('size' => 36)),
                'date'      => get_the_date('M j, g:i a'),
                'timestamp' => get_the_time('U'),
            );
        }
    }
    wp_reset_postdata();

    wp_send_json_success(array('messages' => $data));
}
add_action('wp_ajax_cm_fetch_messages', 'cm_ajax_fetch_messages');

/**
 * ─── SUBMIT REVIEW ─────────────────────────────────────
 */
function cm_ajax_submit_review()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $result = cm_submit_review(array(
        'listing_id'  => intval($_POST['listing_id'] ?? 0),
        'reviewer_id' => get_current_user_id(),
        'rating'      => intval($_POST['rating'] ?? 5),
        'comment'     => wp_kses_post(wp_unslash($_POST['comment'] ?? '')),
    ));

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array('message' => __('Review submitted successfully!', 'campusmarket')));
}
add_action('wp_ajax_cm_submit_review', 'cm_ajax_submit_review');

/**
 * ─── ADMIN: APPROVE / REJECT LISTING ───────────────────
 */
function cm_ajax_approve_listing()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'campusmarket')));
    }

    $listing_id = intval($_POST['listing_id'] ?? 0);
    $action     = sanitize_text_field(wp_unslash($_POST['approval_action'] ?? ''));

    if (! in_array($action, array('approved', 'rejected'), true)) {
        wp_send_json_error(array('message' => __('Invalid action.', 'campusmarket')));
    }

    update_post_meta($listing_id, '_cm_approval_status', $action);

    // Notify author
    $listing = get_post($listing_id);
    if ($listing) {
        $author_id = $listing->post_author;
        $status_text = $action === 'approved' ? __('approved', 'campusmarket') : __('rejected', 'campusmarket');
        $message = sprintf(__('Your listing "%s" has been %s by an admin.', 'campusmarket'), get_the_title($listing_id), $status_text);
        $link = home_url('/dashboard/?tab=listings');
        cm_add_notification($author_id, 'listing_approval', $message, $link);
    }

    wp_send_json_success(array(
        'message' => sprintf(__('Listing %s.', 'campusmarket'), $action),
    ));
}
add_action('wp_ajax_cm_approve_listing', 'cm_ajax_approve_listing');

/**
 * ─── ADMIN: VERIFY USER ────────────────────────────────
 */
function cm_ajax_verify_user()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'campusmarket')));
    }

    $user_id = intval($_POST['user_id'] ?? 0);
    $verify  = sanitize_text_field(wp_unslash($_POST['verify'] ?? '1'));
    $remarks = isset($_POST['remarks']) ? sanitize_textarea_field(wp_unslash($_POST['remarks'])) : '';

    update_user_meta($user_id, '_cm_verified', $verify);
    
    // Sync the new multi-step status field
    $status = ('1' === $verify) ? 'approved' : 'rejected';
    update_user_meta($user_id, '_cm_verification_status', $status);
    
    // Save remarks if rejected
    if ($status === 'rejected') {
        update_user_meta($user_id, '_cm_verification_remarks', $remarks);
    } else {
        delete_user_meta($user_id, '_cm_verification_remarks');
    }

    // Notify user
    $is_verified = '1' === $verify;
    if ($is_verified) {
        $message = __('Congratulations! Your student ID has been verified. You now have a verified badge.', 'campusmarket');
        cm_add_notification($user_id, 'user_verified', $message, home_url('/dashboard/'));
    } else {
        $msg_append = !empty($remarks) ? ' Reason: ' . $remarks : '';
        $message = __('Your student ID verification was rejected. Please review our guidelines and re-submit your ID.' . $msg_append, 'campusmarket');
        cm_add_notification($user_id, 'user_rejected', $message, home_url('/dashboard/'));
    }

    $label = $is_verified ? __('verified', 'campusmarket') : __('rejected', 'campusmarket');
    wp_send_json_success(array('message' => sprintf(__('User verification %s.', 'campusmarket'), $label)));
}
add_action('wp_ajax_cm_verify_user', 'cm_ajax_verify_user');

/**
 * ─── DELETE LISTING (STUDENT DASHBOARD) ────────────────
 */
function cm_ajax_delete_listing()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $listing_id = intval($_POST['listing_id'] ?? 0);
    $listing    = get_post($listing_id);

    if (! $listing || 'cm_listing' !== $listing->post_type) {
        wp_send_json_error(array('message' => __('Listing not found.', 'campusmarket')));
    }

    if ((int) $listing->post_author !== get_current_user_id() && ! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'campusmarket')));
    }

    wp_trash_post($listing_id);

    wp_send_json_success(array('message' => __('Listing deleted.', 'campusmarket')));
}
add_action('wp_ajax_cm_delete_listing', 'cm_ajax_delete_listing');

/**
 * ─── MARK NOTIFICATION AS READ ─────────────────────────
 */
function cm_ajax_mark_notification_read()
{
    check_ajax_referer('cm_nonce', 'nonce');

    if (! is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'campusmarket')));
    }

    $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
    $mark_all = isset($_POST['mark_all']) ? (bool) $_POST['mark_all'] : false;
    $user_id = get_current_user_id();

    if ($mark_all) {
        $unread_notifications = cm_get_unread_notifications($user_id);
        if ($unread_notifications->have_posts()) {
            while ($unread_notifications->have_posts()) {
                $unread_notifications->the_post();
                cm_mark_notification_read(get_the_ID());
            }
            wp_reset_postdata();
        }
    } else if ($notification_id > 0) {
        // Ensure the notification belongs to this user
        $notification = get_post($notification_id);
        $recipient_id = get_post_meta($notification_id, '_cm_recipient_id', true);
        if ($notification && (int) $recipient_id === $user_id) {
            cm_mark_notification_read($notification_id);
        } else {
            wp_send_json_error(array('message' => __('Permission denied.', 'campusmarket')));
        }
    } else {
        wp_send_json_error(array('message' => __('Invalid request.', 'campusmarket')));
    }

    wp_send_json_success(array('message' => __('Notification(s) marked as read.', 'campusmarket')));
}
add_action('wp_ajax_cm_mark_notification_read', 'cm_ajax_mark_notification_read');
