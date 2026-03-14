<?php
/**
 * Listing Category Taxonomy Archive Redirect
 * 
 * Redirects category archives to the main browse page with appropriate filters
 * to maintain a consistent premium UI experience.
 *
 * @package CampusMarket
 */

$term = get_queried_object();

if ($term && !is_wp_error($term)) {
    $browse_url = get_post_type_archive_link('cm_listing');
    $redirect_url = add_query_arg('category', $term->term_id, $browse_url);
    
    wp_redirect($redirect_url);
    exit;
}

// Fallback to home if something goes wrong
wp_redirect(home_url());
exit;
