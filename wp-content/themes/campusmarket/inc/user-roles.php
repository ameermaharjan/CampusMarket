<?php

/**
 * Custom User Roles
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Add Student role on theme activation
 */
function cm_add_student_role()
{
    add_role('student', __('Student', 'campusmarket'), array(
        'read'           => true,
        'edit_posts'     => true,
        'delete_posts'   => true,
        'publish_posts'  => true,
        'upload_files'   => true,
    ));
}
add_action('after_switch_theme', 'cm_add_student_role');

/**
 * Remove Student role on theme deactivation
 */
function cm_remove_student_role()
{
    remove_role('student');
}
add_action('switch_theme', 'cm_remove_student_role');

/**
 * Set default role to student for new registrations
 */
function cm_default_registration_role($default_role)
{
    return 'student';
}
add_filter('pre_option_default_role', 'cm_default_registration_role');

/**
 * Add custom fields to user profile (admin & frontend)
 */
function cm_user_profile_fields($user)
{
    $student_id = get_user_meta($user->ID, '_cm_student_id', true);
    $department = get_user_meta($user->ID, '_cm_department', true);
    $phone      = get_user_meta($user->ID, '_cm_phone', true);
    $verified   = get_user_meta($user->ID, '_cm_verified', true);
    $v_status   = get_user_meta($user->ID, '_cm_verification_status', true);
    $avatar_id  = get_user_meta($user->ID, '_cm_profile_photo', true);
    $id_front   = get_user_meta($user->ID, '_cm_id_card_front', true);
    $id_back    = get_user_meta($user->ID, '_cm_id_card_back', true);
?>
    <h2><?php esc_html_e('CampusMarket Student Info', 'campusmarket'); ?></h2>
    <table class="form-table">
        <tr>
            <th><label for="cm_student_id"><?php esc_html_e('Student ID', 'campusmarket'); ?></label></th>
            <td><input type="text" id="cm_student_id" name="cm_student_id" value="<?php echo esc_attr($student_id); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="cm_department"><?php esc_html_e('Department', 'campusmarket'); ?></label></th>
            <td><input type="text" id="cm_department" name="cm_department" value="<?php echo esc_attr($department); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="cm_phone"><?php esc_html_e('Phone Number', 'campusmarket'); ?></label></th>
            <td><input type="tel" id="cm_phone" name="cm_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="cm_verification_status"><?php esc_html_e('Verification Status', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_verification_status" name="cm_verification_status">
                    <option value="pending" <?php selected($v_status, 'pending'); ?>>⏳ Pending</option>
                    <option value="approved" <?php selected($v_status, 'approved'); ?>>✅ Approved</option>
                    <option value="rejected" <?php selected($v_status, 'rejected'); ?>>❌ Rejected</option>
                </select>
            </td>
        </tr>
        <?php if (current_user_can('manage_options')) : ?>
            <tr>
                <th><label for="cm_verified"><?php esc_html_e('Verified Student (Legacy)', 'campusmarket'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="cm_verified" name="cm_verified" value="1" <?php checked($verified, '1'); ?>>
                        <?php esc_html_e('This student is verified by admin', 'campusmarket'); ?>
                    </label>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <th><?php esc_html_e('Profile Photo', 'campusmarket'); ?></th>
            <td>
                <?php if ($avatar_id) : ?>
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($avatar_id, 'thumbnail')); ?>" class="cm-admin-avatar mb-2" style="max-width: 100px; border-radius: 8px;">
                <?php endif; ?>
                <input type="number" name="cm_profile_photo" value="<?php echo esc_attr($avatar_id); ?>" class="small-text">
                <p class="description"><?php esc_html_e('Attachment ID for profile photo', 'campusmarket'); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e('Student ID (Front)', 'campusmarket'); ?></th>
            <td>
                <?php if ($id_front) : ?>
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($id_front, 'large')); ?>" class="cm-admin-id mb-2" style="max-width: 300px; border-radius: 8px;">
                <?php endif; ?>
                <input type="number" name="cm_id_card_front" value="<?php echo esc_attr($id_front); ?>" class="small-text">
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e('Student ID (Back)', 'campusmarket'); ?></th>
            <td>
                <?php if ($id_back) : ?>
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($id_back, 'large')); ?>" class="cm-admin-id mb-2" style="max-width: 300px; border-radius: 8px;">
                <?php endif; ?>
                <input type="number" name="cm_id_card_back" value="<?php echo esc_attr($id_back); ?>" class="small-text">
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'cm_user_profile_fields');
add_action('edit_user_profile', 'cm_user_profile_fields');

/**
 * Save custom user profile fields
 */
function cm_save_user_profile_fields($user_id)
{
    if (! current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['cm_student_id'])) {
        update_user_meta($user_id, '_cm_student_id', sanitize_text_field(wp_unslash($_POST['cm_student_id'])));
    }
    if (isset($_POST['cm_department'])) {
        update_user_meta($user_id, '_cm_department', sanitize_text_field(wp_unslash($_POST['cm_department'])));
    }
    if (isset($_POST['cm_phone'])) {
        update_user_meta($user_id, '_cm_phone', sanitize_text_field(wp_unslash($_POST['cm_phone'])));
    }
    if (isset($_POST['cm_verification_status'])) {
        $status = sanitize_text_field(wp_unslash($_POST['cm_verification_status']));
        update_user_meta($user_id, '_cm_verification_status', $status);
        // Sync with legacy verified flag if approved
        if ($status === 'approved') {
            update_user_meta($user_id, '_cm_verified', '1');
        } else {
            update_user_meta($user_id, '_cm_verified', '0');
        }
    }
    if (isset($_POST['cm_profile_photo'])) {
        update_user_meta($user_id, '_cm_profile_photo', absint($_POST['cm_profile_photo']));
    }
    if (isset($_POST['cm_id_card_front'])) {
        update_user_meta($user_id, '_cm_id_card_front', absint($_POST['cm_id_card_front']));
    }
    if (isset($_POST['cm_id_card_back'])) {
        update_user_meta($user_id, '_cm_id_card_back', absint($_POST['cm_id_card_back']));
    }
    if (current_user_can('manage_options')) {
        $verified = isset($_POST['cm_verified']) ? '1' : '0';
        update_user_meta($user_id, '_cm_verified', $verified);
    }
}
add_action('personal_options_update', 'cm_save_user_profile_fields');
add_action('edit_user_profile_update', 'cm_save_user_profile_fields');

/**
 * Check if a user is verified
 */
function cm_is_user_verified($user_id = null)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }
    return '1' === get_user_meta($user_id, '_cm_verified', true);
}
