<?php

/**
 * Chat Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Send a message
 */
function cm_send_message($data)
{
    $sender_id       = intval($data['sender_id']);
    $receiver_id     = intval($data['receiver_id']);
    $message         = wp_kses_post($data['message']);
    $conversation_id = sanitize_text_field($data['conversation_id']);

    if (empty($message)) {
        return new WP_Error('empty_message', __('Message cannot be empty.', 'campusmarket'));
    }

    // Generate conversation ID if not provided
    if (empty($conversation_id)) {
        $conversation_id = cm_generate_conversation_id($sender_id, $receiver_id);
    }

    $message_id = wp_insert_post(array(
        'post_type'    => 'cm_message',
        'post_title'   => sprintf('Message from %s to %s', get_userdata($sender_id)->display_name, get_userdata($receiver_id)->display_name),
        'post_content' => $message,
        'post_status'  => 'publish',
        'post_author'  => $sender_id,
    ));

    if (is_wp_error($message_id)) {
        return $message_id;
    }

    update_post_meta($message_id, '_cm_sender_id', $sender_id);
    update_post_meta($message_id, '_cm_receiver_id', $receiver_id);
    update_post_meta($message_id, '_cm_conversation_id', $conversation_id);
    update_post_meta($message_id, '_cm_read_status', '0');

    return $message_id;
}

/**
 * Generate a consistent conversation ID between two users
 */
function cm_generate_conversation_id($user1_id, $user2_id)
{
    $ids = array(intval($user1_id), intval($user2_id));
    sort($ids);
    return 'conv_' . $ids[0] . '_' . $ids[1];
}

/**
 * Get messages in a conversation
 */
function cm_get_conversation($conversation_id, $after_id = 0)
{
    $args = array(
        'post_type'      => 'cm_message',
        'posts_per_page' => 50,
        'meta_query'     => array(
            array(
                'key'   => '_cm_conversation_id',
                'value' => $conversation_id,
            ),
        ),
        'orderby' => 'date',
        'order'   => 'ASC',
    );

    // Only fetch new messages after a certain ID
    if ($after_id > 0) {
        $args['post__not_in'] = range(1, $after_id);
        // Actually, use date-based filtering for better performance
        $after_post = get_post($after_id);
        if ($after_post) {
            $args['date_query'] = array(
                array(
                    'after'     => $after_post->post_date,
                    'inclusive' => false,
                ),
            );
        }
    }

    return new WP_Query($args);
}

/**
 * Get all conversations for a user
 */
function cm_get_user_conversations($user_id = null)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    global $wpdb;

    // Get unique conversation IDs for this user
    $conversation_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT pm_conv.meta_value
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_conv ON p.ID = pm_conv.post_id AND pm_conv.meta_key = '_cm_conversation_id'
         WHERE p.post_type = 'cm_message'
         AND p.post_status = 'publish'
         AND (
             EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm_s WHERE pm_s.post_id = p.ID AND pm_s.meta_key = '_cm_sender_id' AND pm_s.meta_value = %d)
             OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm_r WHERE pm_r.post_id = p.ID AND pm_r.meta_key = '_cm_receiver_id' AND pm_r.meta_value = %d)
         )",
        $user_id,
        $user_id
    ));

    $conversations = array();
    foreach ($conversation_ids as $conv_id) {
        // Get the other user
        $other_user_id = cm_get_other_user_in_conversation($conv_id, $user_id);
        $other_user    = get_userdata($other_user_id);

        // Get latest message
        $latest = new WP_Query(array(
            'post_type'      => 'cm_message',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_cm_conversation_id',
                    'value' => $conv_id,
                ),
            ),
            'orderby' => 'date',
            'order'   => 'DESC',
        ));

        // Count unread
        $unread = cm_count_unread_messages($conv_id, $user_id);

        $conversations[] = array(
            'conversation_id' => $conv_id,
            'other_user_id'   => $other_user_id,
            'other_user_name' => $other_user ? $other_user->display_name : 'Unknown',
            'other_user_avatar' => cm_get_user_avatar_url($other_user_id, 48),
            'last_message'    => $latest->have_posts() ? wp_trim_words($latest->posts[0]->post_content, 10) : '',
            'last_date'       => $latest->have_posts() ? $latest->posts[0]->post_date : '',
            'unread_count'    => $unread,
        );
    }

    // Sort by latest message date
    usort($conversations, function ($a, $b) {
        return strtotime($b['last_date']) - strtotime($a['last_date']);
    });

    return $conversations;
}

/**
 * Get the other user in a conversation
 */
function cm_get_other_user_in_conversation($conversation_id, $current_user_id)
{
    // Conversation ID format: conv_ID1_ID2
    $parts = explode('_', $conversation_id);
    if (count($parts) >= 3) {
        $id1 = intval($parts[1]);
        $id2 = intval($parts[2]);
        return $id1 === intval($current_user_id) ? $id2 : $id1;
    }
    return 0;
}

/**
 * Mark messages as read
 */
function cm_mark_as_read($conversation_id, $user_id)
{
    $messages = new WP_Query(array(
        'post_type'      => 'cm_message',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_conversation_id',
                'value' => $conversation_id,
            ),
            array(
                'key'   => '_cm_receiver_id',
                'value' => $user_id,
            ),
            array(
                'key'   => '_cm_read_status',
                'value' => '0',
            ),
        ),
    ));

    foreach ($messages->posts as $msg_id) {
        update_post_meta($msg_id, '_cm_read_status', '1');
    }

    return count($messages->posts);
}

/**
 * Count unread messages in a conversation for a user
 */
function cm_count_unread_messages($conversation_id, $user_id)
{
    $unread = new WP_Query(array(
        'post_type'      => 'cm_message',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_conversation_id',
                'value' => $conversation_id,
            ),
            array(
                'key'   => '_cm_receiver_id',
                'value' => $user_id,
            ),
            array(
                'key'   => '_cm_read_status',
                'value' => '0',
            ),
        ),
    ));

    return $unread->found_posts;
}

/**
 * Get total unread messages for a user across all conversations
 */
function cm_get_total_unread($user_id = null)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    $unread = new WP_Query(array(
        'post_type'      => 'cm_message',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_cm_receiver_id',
                'value' => $user_id,
            ),
            array(
                'key'   => '_cm_read_status',
                'value' => '0',
            ),
        ),
    ));

    return $unread->found_posts;
}
