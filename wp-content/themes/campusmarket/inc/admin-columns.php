<?php

/**
 * Custom Admin Columns for CPTs
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * ─── LISTING ADMIN COLUMNS ─────────────────────────────
 */
function cm_listing_columns($columns)
{
    $new_columns = array();
    $new_columns['cb']              = $columns['cb'];
    $new_columns['title']           = $columns['title'];
    $new_columns['cm_price']        = __('Price', 'campusmarket');
    $new_columns['cm_type']         = __('Type', 'campusmarket');
    $new_columns['cm_status']       = __('Approval', 'campusmarket');
    $new_columns['cm_owner']        = __('Owner', 'campusmarket');
    $new_columns['taxonomy-listing_category'] = __('Category', 'campusmarket');
    $new_columns['date']            = $columns['date'];
    return $new_columns;
}
add_filter('manage_cm_listing_posts_columns', 'cm_listing_columns');

function cm_listing_column_content($column, $post_id)
{
    switch ($column) {
        case 'cm_price':
            $price = get_post_meta($post_id, '_cm_price', true);
            $price_type = get_post_meta($post_id, '_cm_price_type', true);
            if ($price) {
                echo 'Rs. ' . esc_html($price);
                if ($price_type && 'fixed' !== $price_type) {
                    echo ' / ' . esc_html(str_replace('per_', '', $price_type));
                }
            } else {
                echo '—';
            }
            break;

        case 'cm_type':
            $type = get_post_meta($post_id, '_cm_listing_type', true);
            echo esc_html(ucfirst($type ?: 'item'));
            break;

        case 'cm_status':
            $status = get_post_meta($post_id, '_cm_approval_status', true);
            $status = $status ?: 'pending';
            $badges = array(
                'pending'  => '<span style="color:#D97706;font-weight:600;">⏳ Pending</span>',
                'approved' => '<span style="color:#059669;font-weight:600;">✅ Approved</span>',
                'rejected' => '<span style="color:#DC2626;font-weight:600;">❌ Rejected</span>',
            );
            echo isset($badges[$status]) ? $badges[$status] : esc_html($status);
            break;

        case 'cm_owner':
            $author_id = get_post_field('post_author', $post_id);
            $user = get_userdata($author_id);
            if ($user) {
                echo esc_html($user->display_name);
                if (cm_is_user_verified($author_id)) {
                    echo ' ✔️';
                }
            }
            break;
    }
}
add_action('manage_cm_listing_posts_custom_column', 'cm_listing_column_content', 10, 2);

/**
 * ─── BOOKING ADMIN COLUMNS ─────────────────────────────
 */
function cm_booking_columns($columns)
{
    $new_columns = array();
    $new_columns['cb']           = $columns['cb'];
    $new_columns['title']        = $columns['title'];
    $new_columns['cm_listing']   = __('Listing', 'campusmarket');
    $new_columns['cm_renter']    = __('Renter', 'campusmarket');
    $new_columns['cm_dates']     = __('Dates', 'campusmarket');
    $new_columns['cm_b_status']  = __('Status', 'campusmarket');
    $new_columns['cm_b_price']   = __('Total', 'campusmarket');
    $new_columns['date']         = $columns['date'];
    return $new_columns;
}
add_filter('manage_cm_booking_posts_columns', 'cm_booking_columns');

function cm_booking_column_content($column, $post_id)
{
    switch ($column) {
        case 'cm_listing':
            $listing_id = get_post_meta($post_id, '_cm_listing_id', true);
            echo $listing_id ? esc_html(get_the_title($listing_id)) : '—';
            break;

        case 'cm_renter':
            $renter_id = get_post_meta($post_id, '_cm_renter_id', true);
            $user = $renter_id ? get_userdata($renter_id) : null;
            echo $user ? esc_html($user->display_name) : '—';
            break;

        case 'cm_dates':
            $start = get_post_meta($post_id, '_cm_start_date', true);
            $end   = get_post_meta($post_id, '_cm_end_date', true);
            echo esc_html($start) . ' → ' . esc_html($end);
            break;

        case 'cm_b_status':
            $status = get_post_meta($post_id, '_cm_status', true);
            echo esc_html(ucfirst($status ?: 'pending'));
            break;

        case 'cm_b_price':
            $price = get_post_meta($post_id, '_cm_total_price', true);
            echo $price ? 'Rs. ' . esc_html($price) : '—';
            break;
    }
}
add_action('manage_cm_booking_posts_custom_column', 'cm_booking_column_content', 10, 2);

/**
 * Make columns sortable
 */
function cm_listing_sortable_columns($columns)
{
    $columns['cm_price']  = '_cm_price';
    $columns['cm_status'] = '_cm_approval_status';
    return $columns;
}
add_filter('manage_edit-cm_listing_sortable_columns', 'cm_listing_sortable_columns');
