<?php

/**
 * Booking Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Create a new booking
 */
function cm_create_booking($data)
{
    $listing_id = intval($data['listing_id']);
    $renter_id  = intval($data['renter_id']);
    $start_date = sanitize_text_field($data['start_date']);
    $end_date   = sanitize_text_field($data['end_date']);

    // Get listing info
    $listing     = get_post($listing_id);
    $owner_id    = $listing ? (int) $listing->post_author : 0;
    $price       = (float) get_post_meta($listing_id, '_cm_price', true);
    $price_type  = get_post_meta($listing_id, '_cm_price_type', true);

    // Calculate total price
    $total_price = cm_calculate_total_price($price, $price_type, $start_date, $end_date);

    // Check availability
    if (! cm_check_availability($listing_id, $start_date, $end_date)) {
        return new WP_Error('unavailable', __('This item is not available for the selected dates.', 'campusmarket'));
    }

    // Cannot book own listing
    if ($renter_id === $owner_id) {
        return new WP_Error('own_listing', __('You cannot book your own listing.', 'campusmarket'));
    }

    $booking_id = wp_insert_post(array(
        'post_type'   => 'cm_booking',
        'post_title'  => sprintf('Booking: %s by %s', get_the_title($listing_id), get_userdata($renter_id)->display_name),
        'post_status' => 'publish',
        'post_author' => $renter_id,
    ));

    if (is_wp_error($booking_id)) {
        return $booking_id;
    }

    update_post_meta($booking_id, '_cm_listing_id', $listing_id);
    update_post_meta($booking_id, '_cm_renter_id', $renter_id);
    update_post_meta($booking_id, '_cm_owner_id', $owner_id);
    update_post_meta($booking_id, '_cm_start_date', $start_date);
    update_post_meta($booking_id, '_cm_end_date', $end_date);
    update_post_meta($booking_id, '_cm_status', 'pending');
    update_post_meta($booking_id, '_cm_total_price', $total_price);

    // Send notification to owner
    $renter_name = get_userdata($renter_id)->display_name;
    $intent = get_post_meta($listing_id, '_cm_listing_intent', true);
    if ($intent === 'rent') {
        $message = sprintf(__('%s requested to rent your listing "%s".', 'campusmarket'), $renter_name, get_the_title($listing_id));
    } else {
        $message = sprintf(__('%s sent a buy request for your listing "%s".', 'campusmarket'), $renter_name, get_the_title($listing_id));
    }
    $link = home_url('/dashboard/?tab=listings');
    cm_add_notification($owner_id, 'booking_request', $message, $link);

    return $booking_id;
}

/**
 * Update booking status
 */
function cm_update_booking_status($booking_id, $new_status)
{
    $valid = array('pending', 'confirmed', 'completed', 'cancelled');
    if (! in_array($new_status, $valid, true)) {
        return new WP_Error('invalid_status', __('Invalid booking status.', 'campusmarket'));
    }

    update_post_meta($booking_id, '_cm_status', $new_status);
    return true;
}

/**
 * Get bookings for a user (as renter)
 */
function cm_get_user_bookings($user_id = null, $status = '')
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    $meta_query = array(
        array(
            'key'   => '_cm_renter_id',
            'value' => $user_id,
        ),
    );

    if (! empty($status)) {
        $meta_query[] = array(
            'key'   => '_cm_status',
            'value' => $status,
        );
    }

    return new WP_Query(array(
        'post_type'      => 'cm_booking',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

/**
 * Get rental requests for a listing owner
 */
function cm_get_rental_requests($owner_id = null, $status = '')
{
    if (null === $owner_id) {
        $owner_id = get_current_user_id();
    }

    $meta_query = array(
        array(
            'key'   => '_cm_owner_id',
            'value' => $owner_id,
        ),
    );

    if (! empty($status)) {
        $meta_query[] = array(
            'key'   => '_cm_status',
            'value' => $status,
        );
    }

    return new WP_Query(array(
        'post_type'      => 'cm_booking',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

/**
 * Check if listing is available for given dates
 */
function cm_check_availability($listing_id, $start_date, $end_date)
{
    $existing = new WP_Query(array(
        'post_type'      => 'cm_booking',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_listing_id',
                'value' => $listing_id,
            ),
            array(
                'key'     => '_cm_status',
                'value'   => array('pending', 'confirmed'),
                'compare' => 'IN',
            ),
            array(
                'relation' => 'AND',
                array(
                    'key'     => '_cm_start_date',
                    'value'   => $end_date,
                    'compare' => '<=',
                    'type'    => 'DATETIME',
                ),
                array(
                    'key'     => '_cm_end_date',
                    'value'   => $start_date,
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ),
            ),
        ),
    ));

    return 0 === $existing->found_posts;
}

/**
 * Calculate total price
 */
function cm_calculate_total_price($price, $price_type, $start_date, $end_date)
{
    if ('fixed' === $price_type) {
        return $price;
    }

    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);
    $diff  = $start->diff($end);

    if ('per_hour' === $price_type) {
        $hours = ($diff->days * 24) + $diff->h;
        return max(1, $hours) * $price;
    }

    // per_day
    $days = $diff->days;
    return max(1, $days) * $price;
}
