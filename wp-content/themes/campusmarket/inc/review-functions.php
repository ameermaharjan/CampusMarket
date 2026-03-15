<?php

/**
 * Review Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Submit a review
 */
function cm_submit_review($data)
{
    $listing_id  = intval($data['listing_id']);
    $reviewer_id = intval($data['reviewer_id']);
    $rating      = intval($data['rating']);
    $comment     = wp_kses_post($data['comment']);

    // Validate rating
    $rating = max(1, min(5, $rating));

    // Check if user already reviewed this listing
    $existing = new WP_Query(array(
        'post_type'      => 'cm_review',
        'posts_per_page' => 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_reviewed_listing_id',
                'value' => $listing_id,
            ),
            array(
                'key'   => '_cm_reviewer_id',
                'value' => $reviewer_id,
            ),
        ),
    ));

    if ($existing->have_posts()) {
        return new WP_Error('already_reviewed', __('You have already reviewed this listing.', 'campusmarket'));
    }

    // Cannot review own listing
    $listing = get_post($listing_id);
    if ($listing && (int) $listing->post_author === $reviewer_id) {
        return new WP_Error('own_listing', __('You cannot review your own listing.', 'campusmarket'));
    }

    $review_id = wp_insert_post(array(
        'post_type'    => 'cm_review',
        'post_title'   => sprintf('Review for %s by %s', get_the_title($listing_id), get_userdata($reviewer_id)->display_name),
        'post_content' => $comment,
        'post_status'  => 'publish',
        'post_author'  => $reviewer_id,
    ));

    if (is_wp_error($review_id)) {
        return $review_id;
    }

    update_post_meta($review_id, '_cm_reviewed_listing_id', $listing_id);
    update_post_meta($review_id, '_cm_reviewer_id', $reviewer_id);
    update_post_meta($review_id, '_cm_rating', $rating);

    return $review_id;
}

/**
 * Get reviews for a listing
 */
function cm_get_listing_reviews($listing_id)
{
    return new WP_Query(array(
        'post_type'      => 'cm_review',
        'posts_per_page' => -1,
        'fields'         => 'ids', // Optimization: Only get IDs
        'no_found_rows'  => true,  // Optimization: Don't calculate pagination
        'meta_query'     => array(
            array(
                'key'   => '_cm_reviewed_listing_id',
                'value' => (int) $listing_id,
            ),
        ),
        'orderby' => 'date',
        'order'   => 'DESC',
    ));
}

/**
 * Get average rating for a listing
 */
function cm_get_average_rating($listing_id)
{
    // Use a lightweight query to get ratings directly
    global $wpdb;
    
    $results = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_value FROM $wpdb->postmeta 
         WHERE meta_key = '_cm_rating' 
         AND post_id IN (
            SELECT post_id FROM $wpdb->postmeta 
            WHERE meta_key = '_cm_reviewed_listing_id' 
            AND meta_value = %d
         )",
        $listing_id
    ));

    if (empty($results)) {
        return 0;
    }

    $total = array_sum(array_map('intval', $results));
    $count = count($results);

    return $count > 0 ? round($total / $count, 1) : 0;
}

/**
 * Get review count for a listing
 */
function cm_get_review_count($listing_id)
{
    $reviews = cm_get_listing_reviews($listing_id);
    return $reviews->found_posts;
}
