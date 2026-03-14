<?php

/**
 * Notification Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Create a new notification for a user
 *
 * @param int $user_id The ID of the user receiving the notification.
 * @param string $type The type of notification (e.g., 'booking_request', 'booking_approved', 'system_alert').
 * @param string $message The notification content.
 * @param string $link Optional link associated with the notification.
 * @return int|WP_Error The post ID on success, or WP_Error on failure.
 */
function cm_add_notification($user_id, $type, $message, $link = '') {
    $notification_id = wp_insert_post(array(
        'post_type'   => 'cm_notification',
        'post_title'  => sprintf('Notification for User %d', $user_id),
        'post_content'=> sanitize_text_field($message),
        'post_status' => 'publish',
        'post_author' => $user_id, // The recipient is the author so they can query their own notifications easily
    ));

    if (is_wp_error($notification_id)) {
        return $notification_id;
    }

    update_post_meta($notification_id, '_cm_notification_type', sanitize_text_field($type));
    update_post_meta($notification_id, '_cm_notification_link', esc_url_raw($link));
    update_post_meta($notification_id, '_cm_notification_status', 'unread');
    update_post_meta($notification_id, '_cm_recipient_id', intval($user_id));

    return $notification_id;
}

/**
 * Get unread notifications for a user
 *
 * @param int|null $user_id Optional user ID. Defaults to current user.
 * @return WP_Query
 */
function cm_get_unread_notifications($user_id = null) {
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    return new WP_Query(array(
        'post_type'      => 'cm_notification',
        'posts_per_page' => 10,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_recipient_id',
                'value' => $user_id,
            ),
            array(
                'key'   => '_cm_notification_status',
                'value' => 'unread',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

/**
 * Mark a notification as read
 *
 * @param int $notification_id
 * @return bool
 */
function cm_mark_notification_read($notification_id) {
    return update_post_meta($notification_id, '_cm_notification_status', 'read');
}
