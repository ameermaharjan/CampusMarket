<?php

/**
 * Template Helper Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Get formatted price display
 */
function cm_get_price_display($post_id = null)
{
    if (null === $post_id) {
        $post_id = get_the_ID();
    }

    $price      = get_post_meta($post_id, '_cm_price', true);
    $price_type = get_post_meta($post_id, '_cm_price_type', true);

    if (empty($price)) {
        return '<span class="cm-price cm-price--free">Free</span>';
    }

    $label = '';
    switch ($price_type) {
        case 'per_day':
            $label = '/day';
            break;
        case 'per_hour':
            $label = '/hr';
            break;
        case 'fixed':
            $label = '';
            break;
        default:
            $label = '/day';
    }

    return '<span class="cm-price">Rs. ' . esc_html(number_format((float) $price, 2)) . '<small>' . esc_html($label) . '</small></span>';
}

/**
 * Get star rating HTML
 */
function cm_get_star_html($rating, $max = 5)
{
    $rating = max(0, min($max, (float) $rating));
    $full   = floor($rating);
    $half   = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty  = $max - $full - $half;

    $html = '<span class="cm-stars" aria-label="' . esc_attr($rating . ' out of ' . $max . ' stars') . '">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<span class="cm-star cm-star--full">★</span>';
    }
    if ($half) {
        $html .= '<span class="cm-star cm-star--half">★</span>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<span class="cm-star cm-star--empty">☆</span>';
    }
    $html .= '</span>';

    return $html;
}

/**
 * Get status badge HTML
 */
function cm_get_status_badge($status)
{
    $badges = array(
        'pending'   => array('label' => 'Pending',   'class' => 'cm-badge--warning'),
        'approved'  => array('label' => 'Approved',  'class' => 'cm-badge--success'),
        'rejected'  => array('label' => 'Rejected',  'class' => 'cm-badge--danger'),
        'confirmed' => array('label' => 'Confirmed', 'class' => 'cm-badge--success'),
        'completed' => array('label' => 'Completed', 'class' => 'cm-badge--primary'),
        'cancelled' => array('label' => 'Cancelled', 'class' => 'cm-badge--danger'),
    );

    $badge = isset($badges[$status]) ? $badges[$status] : array('label' => ucfirst($status), 'class' => 'cm-badge--default');

    return '<span class="cm-badge ' . esc_attr($badge['class']) . '">' . esc_html($badge['label']) . '</span>';
}

/**
 * Get human-readable time ago
 */
function cm_time_ago($timestamp)
{
    $diff = time() - strtotime($timestamp);

    if ($diff < 60) {
        return __('Just now', 'campusmarket');
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return sprintf(_n('%d minute ago', '%d minutes ago', $mins, 'campusmarket'), $mins);
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'campusmarket'), $hours);
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return sprintf(_n('%d day ago', '%d days ago', $days, 'campusmarket'), $days);
    } else {
        return date_i18n(get_option('date_format'), strtotime($timestamp));
    }
}

/**
 * Check if current user is the listing owner
 */
function cm_is_listing_owner($post_id = null)
{
    if (null === $post_id) {
        $post_id = get_the_ID();
    }

    if (! is_user_logged_in()) {
        return false;
    }

    return (int) get_post_field('post_author', $post_id) === get_current_user_id();
}

/**
 * Get condition display label
 */
function cm_get_condition_label($condition)
{
    $labels = array(
        'new'      => __('New', 'campusmarket'),
        'like_new' => __('Like New', 'campusmarket'),
        'good'     => __('Good', 'campusmarket'),
        'fair'     => __('Fair', 'campusmarket'),
    );

    return isset($labels[$condition]) ? $labels[$condition] : ucfirst($condition);
}

/**
 * Get listing type label
 */
function cm_get_listing_type_label($type)
{
    $labels = array(
        'item'    => __('📦 Item', 'campusmarket'),
        'service' => __('🛠️ Service', 'campusmarket'),
    );

    return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
}

/**
 * Count user listings
 */
function cm_count_user_listings($user_id = null)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    $count = new WP_Query(array(
        'post_type'      => 'cm_listing',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ));

    return $count->post_count;
}

/**
 * Count user bookings
 */
function cm_count_user_bookings($user_id = null)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    $count = new WP_Query(array(
        'post_type'      => 'cm_booking',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => array(
            array(
                'key'   => '_cm_renter_id',
                'value' => $user_id,
            ),
        ),
    ));

    return $count->post_count;
}
