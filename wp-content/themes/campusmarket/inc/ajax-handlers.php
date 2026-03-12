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

    $title       = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
    $description = wp_kses_post(wp_unslash($_POST['description'] ?? ''));
    $price       = floatval($_POST['price'] ?? 0);
    $price_type  = sanitize_text_field(wp_unslash($_POST['price_type'] ?? 'per_day'));
    $condition   = sanitize_text_field(wp_unslash($_POST['condition'] ?? 'good'));
    $location    = sanitize_text_field(wp_unslash($_POST['location'] ?? ''));
    $type        = sanitize_text_field(wp_unslash($_POST['listing_type'] ?? 'item'));
    $category    = intval($_POST['category'] ?? 0);
    $avail_start = sanitize_text_field(wp_unslash($_POST['availability_start'] ?? ''));
    $avail_end   = sanitize_text_field(wp_unslash($_POST['availability_end'] ?? ''));

    if (empty($title) || empty($description)) {
        wp_send_json_error(array('message' => __('Title and description are required.', 'campusmarket')));
    }

    $post_id = wp_insert_post(array(
        'post_type'    => 'cm_listing',
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id(),
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => $post_id->get_error_message()));
    }

    // Set meta
    update_post_meta($post_id, '_cm_price', $price);
    update_post_meta($post_id, '_cm_price_type', $price_type);
    update_post_meta($post_id, '_cm_condition', $condition);
    update_post_meta($post_id, '_cm_location', $location);
    update_post_meta($post_id, '_cm_listing_type', $type);
    update_post_meta($post_id, '_cm_availability_start', $avail_start);
    update_post_meta($post_id, '_cm_availability_end', $avail_end);
    update_post_meta($post_id, '_cm_approval_status', 'pending');

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
        'message' => __('Listing submitted successfully! It will be visible after admin approval.', 'campusmarket'),
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

    $result = cm_create_booking(array(
        'listing_id' => intval($_POST['listing_id'] ?? 0),
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

    wp_send_json_success(array('message' => __('Booking status updated.', 'campusmarket')));
}
add_action('wp_ajax_cm_update_booking', 'cm_ajax_update_booking');

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

    update_user_meta($user_id, '_cm_verified', $verify);

    $label = '1' === $verify ? __('verified', 'campusmarket') : __('unverified', 'campusmarket');
    wp_send_json_success(array('message' => sprintf(__('User %s.', 'campusmarket'), $label)));
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
