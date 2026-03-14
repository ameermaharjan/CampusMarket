<?php

/**
 * User Activity Logging Functions
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Log a user activity event
 * 
 * @param int $user_id The user ID.
 * @param string $action The action type (e.g., 'login', 'logout', 'password_change').
 * @param string $description A human-readable description.
 */
function cm_log_user_activity($user_id, $action, $description)
{
    if (!$user_id) return;

    $log_id = wp_insert_post(array(
        'post_type'   => 'cm_activity_log',
        'post_title'  => sprintf('%s - %s', $action, get_userdata($user_id)->user_login),
        'post_status' => 'publish',
        'post_author' => $user_id,
    ));

    if (! is_wp_error($log_id)) {
        update_post_meta($log_id, '_cm_activity_type', sanitize_text_field($action));
        update_post_meta($log_id, '_cm_activity_desc', sanitize_text_field($description));
        update_post_meta($log_id, '_cm_activity_ip', sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
    }
}

/**
 * Hook: On User Login
 */
function cm_activity_hook_login($user_login, $user)
{
    cm_log_user_activity($user->ID, 'login', 'Logged into account successfully');
}
add_action('wp_login', 'cm_activity_hook_login', 10, 2);

/**
 * Hook: On User Logout
 */
function cm_activity_hook_logout($user_id)
{
    if ($user_id) {
        cm_log_user_activity($user_id, 'logout', 'Safely logged out of account');
    }
}
add_action('wp_logout', 'cm_activity_hook_logout');

/**
 * Hook: On Password Change
 */
function cm_activity_hook_password_change($user_id)
{
    cm_log_user_activity($user_id, 'password_change', 'Account password was permanently updated');
}
add_action('after_password_reset', 'cm_activity_hook_password_change');
add_action('profile_update', function($user_id, $old_user_data) {
    // Check if the password was actually updated using standard profile form
    if (!empty($_POST['pass1']) && !empty($_POST['pass2']) && $_POST['pass1'] === $_POST['pass2']) {
        cm_activity_hook_password_change($user_id);
    }
}, 10, 2);

/**
 * Hook: Failed Login Attempt
 */
function cm_activity_hook_failed_login($username)
{
    $user = get_user_by('login', $username);
    if ($user) {
        cm_log_user_activity($user->ID, 'failed_login', 'Failed login attempt detected');
    }
}
add_action('wp_login_failed', 'cm_activity_hook_failed_login');
