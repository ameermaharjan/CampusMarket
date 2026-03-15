<?php

/**
 * Custom Post Types & Taxonomies
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register Custom Post Types
 */
function cm_register_post_types()
{

    // ─── LISTING CPT ────────────────────────────────────
    $listing_labels = array(
        'name'               => _x('Listings', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Listing', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Listings', 'Admin Menu text', 'campusmarket'),
        'add_new'            => __('Add New Listing', 'campusmarket'),
        'add_new_item'       => __('Add New Listing', 'campusmarket'),
        'edit_item'          => __('Edit Listing', 'campusmarket'),
        'new_item'           => __('New Listing', 'campusmarket'),
        'view_item'          => __('View Listing', 'campusmarket'),
        'search_items'       => __('Search Listings', 'campusmarket'),
        'not_found'          => __('No listings found', 'campusmarket'),
        'not_found_in_trash' => __('No listings found in Trash', 'campusmarket'),
        'all_items'          => __('All Listings', 'campusmarket'),
    );

    register_post_type('cm_listing', array(
        'labels'             => $listing_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'listing', 'with_front' => false),
        'capability_type'    => 'post',
        'has_archive'        => 'browse',
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-store',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    ));

    // ─── BOOKING CPT ────────────────────────────────────
    $booking_labels = array(
        'name'               => _x('Bookings', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Booking', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Bookings', 'Admin Menu text', 'campusmarket'),
        'add_new'            => __('Add New Booking', 'campusmarket'),
        'add_new_item'       => __('Add New Booking', 'campusmarket'),
        'edit_item'          => __('Edit Booking', 'campusmarket'),
        'view_item'          => __('View Booking', 'campusmarket'),
        'all_items'          => __('All Bookings', 'campusmarket'),
        'search_items'       => __('Search Bookings', 'campusmarket'),
        'not_found'          => __('No bookings found', 'campusmarket'),
    );

    register_post_type('cm_booking', array(
        'labels'             => $booking_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array('title', 'custom-fields'),
        'show_in_rest'       => false,
    ));

    // ─── REVIEW CPT ─────────────────────────────────────
    $review_labels = array(
        'name'               => _x('Reviews', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Review', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Reviews', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Reviews', 'campusmarket'),
        'search_items'       => __('Search Reviews', 'campusmarket'),
        'not_found'          => __('No reviews found', 'campusmarket'),
    );

    register_post_type('cm_review', array(
        'labels'             => $review_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-star-filled',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => false,
    ));

    // ─── MESSAGE CPT ────────────────────────────────────
    $message_labels = array(
        'name'               => _x('Messages', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Message', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Messages', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Messages', 'campusmarket'),
        'search_items'       => __('Search Messages', 'campusmarket'),
        'not_found'          => __('No messages found', 'campusmarket'),
    );

    register_post_type('cm_message', array(
        'labels'             => $message_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-format-chat',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => false,
    ));

    // ─── NOTIFICATION CPT ───────────────────────────────
    $notification_labels = array(
        'name'               => _x('Notifications', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Notification', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Notifications', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Notifications', 'campusmarket'),
        'not_found'          => __('No notifications found', 'campusmarket'),
    );

    register_post_type('cm_notification', array(
        'labels'             => $notification_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-bell',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => false,
    ));
    // ─── ACTIVITY LOG CPT ───────────────────────────────
    $activity_labels = array(
        'name'               => _x('Activity Logs', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Activity Log', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Activity Logs', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Activity Logs', 'campusmarket'),
        'not_found'          => __('No activity logs found', 'campusmarket'),
    );

    register_post_type('cm_activity_log', array(
        'labels'             => $activity_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 10,
        'menu_icon'          => 'dashicons-update-alt',
        'supports'           => array('title', 'custom-fields'),
        'show_in_rest'       => false,
    ));

    // ─── FEEDBACK CPT ───────────────────────────────────
    $feedback_labels = array(
        'name'               => _x('Feedback', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Feedback', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Feedback', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Feedback', 'campusmarket'),
        'search_items'       => __('Search Feedback', 'campusmarket'),
        'not_found'          => __('No feedback found', 'campusmarket'),
    );

    register_post_type('cm_feedback', array(
        'labels'             => $feedback_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 11,
        'menu_icon'          => 'dashicons-testimonial',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => false,
    ));

    // ─── REPORT CPT ─────────────────────────────────────
    $report_labels = array(
        'name'               => _x('Reports', 'Post type general name', 'campusmarket'),
        'singular_name'      => _x('Report', 'Post type singular name', 'campusmarket'),
        'menu_name'          => _x('Reports', 'Admin Menu text', 'campusmarket'),
        'all_items'          => __('All Reports', 'campusmarket'),
        'search_items'       => __('Search Reports', 'campusmarket'),
        'not_found'          => __('No reports found', 'campusmarket'),
    );

    register_post_type('cm_report', array(
        'labels'             => $report_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 12,
        'menu_icon'          => 'dashicons-flag',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => false,
    ));
}
add_action('init', 'cm_register_post_types');

/**
 * Register Custom Taxonomies
 */
function cm_register_taxonomies()
{

    // ─── LISTING CATEGORY ───────────────────────────────
    $cat_labels = array(
        'name'              => _x('Listing Categories', 'taxonomy general name', 'campusmarket'),
        'singular_name'     => _x('Listing Category', 'taxonomy singular name', 'campusmarket'),
        'search_items'      => __('Search Categories', 'campusmarket'),
        'all_items'         => __('All Categories', 'campusmarket'),
        'parent_item'       => __('Parent Category', 'campusmarket'),
        'parent_item_colon' => __('Parent Category:', 'campusmarket'),
        'edit_item'         => __('Edit Category', 'campusmarket'),
        'update_item'       => __('Update Category', 'campusmarket'),
        'add_new_item'      => __('Add New Category', 'campusmarket'),
        'new_item_name'     => __('New Category Name', 'campusmarket'),
        'menu_name'         => __('Categories', 'campusmarket'),
    );

    register_taxonomy('listing_category', array('cm_listing'), array(
        'hierarchical'      => true,
        'labels'            => $cat_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'category'),
        'show_in_rest'      => true,
    ));

    // ─── LISTING TAG ────────────────────────────────────
    $tag_labels = array(
        'name'                       => _x('Listing Tags', 'taxonomy general name', 'campusmarket'),
        'singular_name'              => _x('Listing Tag', 'taxonomy singular name', 'campusmarket'),
        'search_items'               => __('Search Tags', 'campusmarket'),
        'popular_items'              => __('Popular Tags', 'campusmarket'),
        'all_items'                  => __('All Tags', 'campusmarket'),
        'edit_item'                  => __('Edit Tag', 'campusmarket'),
        'update_item'                => __('Update Tag', 'campusmarket'),
        'add_new_item'               => __('Add New Tag', 'campusmarket'),
        'new_item_name'              => __('New Tag Name', 'campusmarket'),
        'separate_items_with_commas' => __('Separate tags with commas', 'campusmarket'),
        'add_or_remove_items'        => __('Add or remove tags', 'campusmarket'),
        'choose_from_most_used'      => __('Choose from most used tags', 'campusmarket'),
        'menu_name'                  => __('Tags', 'campusmarket'),
    );

    register_taxonomy('listing_tag', array('cm_listing'), array(
        'hierarchical'      => false,
        'labels'            => $tag_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'tag'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'cm_register_taxonomies');

/**
 * Add Meta Boxes for Listing
 */
function cm_listing_meta_boxes()
{
    add_meta_box(
        'cm_listing_details',
        __('Listing Details', 'campusmarket'),
        'cm_listing_details_callback',
        'cm_listing',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_listing_meta_boxes');

/**
 * Listing Details Meta Box Callback
 */
function cm_listing_details_callback($post)
{
    wp_nonce_field('cm_listing_details_nonce', 'cm_listing_nonce');

    $price             = get_post_meta($post->ID, '_cm_price', true);
    $price_type        = get_post_meta($post->ID, '_cm_price_type', true);
    $condition         = get_post_meta($post->ID, '_cm_condition', true);
    $location          = get_post_meta($post->ID, '_cm_location', true);
    $listing_type      = get_post_meta($post->ID, '_cm_listing_type', true);
    $availability_start = get_post_meta($post->ID, '_cm_availability_start', true);
    $availability_end  = get_post_meta($post->ID, '_cm_availability_end', true);
    $approval_status   = get_post_meta($post->ID, '_cm_approval_status', true);

    if (empty($approval_status)) {
        $approval_status = 'pending';
    }
?>
    <table class="form-table">
        <tr>
            <th><label for="cm_price"><?php esc_html_e('Price (Rs.)', 'campusmarket'); ?></label></th>
            <td><input type="number" id="cm_price" name="cm_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="cm_price_type"><?php esc_html_e('Price Type', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_price_type" name="cm_price_type">
                    <option value="per_day" <?php selected($price_type, 'per_day'); ?>>Per Day</option>
                    <option value="per_hour" <?php selected($price_type, 'per_hour'); ?>>Per Hour</option>
                    <option value="fixed" <?php selected($price_type, 'fixed'); ?>>Fixed Price</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="cm_condition"><?php esc_html_e('Condition', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_condition" name="cm_condition">
                    <option value="new" <?php selected($condition, 'new'); ?>>New</option>
                    <option value="like_new" <?php selected($condition, 'like_new'); ?>>Like New</option>
                    <option value="good" <?php selected($condition, 'good'); ?>>Good</option>
                    <option value="fair" <?php selected($condition, 'fair'); ?>>Fair</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="cm_listing_type"><?php esc_html_e('Listing Type', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_listing_type" name="cm_listing_type">
                    <option value="item" <?php selected($listing_type, 'item'); ?>>Item</option>
                    <option value="service" <?php selected($listing_type, 'service'); ?>>Service</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="cm_location"><?php esc_html_e('Location', 'campusmarket'); ?></label></th>
            <td><input type="text" id="cm_location" name="cm_location" value="<?php echo esc_attr($location); ?>" class="regular-text" placeholder="e.g., Library, Block A"></td>
        </tr>
        <tr>
            <th><label for="cm_availability_start"><?php esc_html_e('Available From', 'campusmarket'); ?></label></th>
            <td><input type="date" id="cm_availability_start" name="cm_availability_start" value="<?php echo esc_attr($availability_start); ?>"></td>
        </tr>
        <tr>
            <th><label for="cm_availability_end"><?php esc_html_e('Available Until', 'campusmarket'); ?></label></th>
            <td><input type="date" id="cm_availability_end" name="cm_availability_end" value="<?php echo esc_attr($availability_end); ?>"></td>
        </tr>
        <tr>
            <th><label for="cm_approval_status"><?php esc_html_e('Approval Status', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_approval_status" name="cm_approval_status">
                    <option value="pending" <?php selected($approval_status, 'pending'); ?>>⏳ Pending</option>
                    <option value="approved" <?php selected($approval_status, 'approved'); ?>>✅ Approved</option>
                    <option value="rejected" <?php selected($approval_status, 'rejected'); ?>>❌ Rejected</option>
                </select>
            </td>
        </tr>
    </table>
<?php
}

/**
 * Save Listing Meta
 */
function cm_save_listing_meta($post_id)
{
    // Verify nonce
    if (! isset($_POST['cm_listing_nonce']) || ! wp_verify_nonce($_POST['cm_listing_nonce'], 'cm_listing_details_nonce')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'cm_price'              => '_cm_price',
        'cm_price_type'         => '_cm_price_type',
        'cm_condition'          => '_cm_condition',
        'cm_location'           => '_cm_location',
        'cm_listing_type'       => '_cm_listing_type',
        'cm_availability_start' => '_cm_availability_start',
        'cm_availability_end'   => '_cm_availability_end',
        'cm_approval_status'    => '_cm_approval_status',
    );

    foreach ($fields as $field_name => $meta_key) {
        if (isset($_POST[$field_name])) {
            $value = sanitize_text_field(wp_unslash($_POST[$field_name]));
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}
add_action('save_post_cm_listing', 'cm_save_listing_meta');

/**
 * Add Meta Boxes for Booking
 */
function cm_booking_meta_boxes()
{
    add_meta_box(
        'cm_booking_details',
        __('Booking Details', 'campusmarket'),
        'cm_booking_details_callback',
        'cm_booking',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_booking_meta_boxes');

/**
 * Booking Details Meta Box Callback
 */
function cm_booking_details_callback($post)
{
    wp_nonce_field('cm_booking_details_nonce', 'cm_booking_nonce');

    $listing_id  = get_post_meta($post->ID, '_cm_listing_id', true);
    $renter_id   = get_post_meta($post->ID, '_cm_renter_id', true);
    $owner_id    = get_post_meta($post->ID, '_cm_owner_id', true);
    $start_date  = get_post_meta($post->ID, '_cm_start_date', true);
    $end_date    = get_post_meta($post->ID, '_cm_end_date', true);
    $status      = get_post_meta($post->ID, '_cm_status', true);
    $total_price = get_post_meta($post->ID, '_cm_total_price', true);

    $listing_title = $listing_id ? get_the_title($listing_id) : 'N/A';
    $renter_name   = $renter_id ? get_userdata($renter_id)->display_name : 'N/A';
    $owner_name    = $owner_id ? get_userdata($owner_id)->display_name : 'N/A';
?>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e('Listing', 'campusmarket'); ?></th>
            <td><?php echo esc_html($listing_title); ?> (ID: <?php echo esc_html($listing_id); ?>)</td>
        </tr>
        <tr>
            <th><?php esc_html_e('Renter', 'campusmarket'); ?></th>
            <td><?php echo esc_html($renter_name); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e('Owner', 'campusmarket'); ?></th>
            <td><?php echo esc_html($owner_name); ?></td>
        </tr>
        <tr>
            <th><label for="cm_start_date"><?php esc_html_e('Start Date', 'campusmarket'); ?></label></th>
            <td><input type="date" id="cm_start_date" name="cm_start_date" value="<?php echo esc_attr($start_date); ?>"></td>
        </tr>
        <tr>
            <th><label for="cm_end_date"><?php esc_html_e('End Date', 'campusmarket'); ?></label></th>
            <td><input type="date" id="cm_end_date" name="cm_end_date" value="<?php echo esc_attr($end_date); ?>"></td>
        </tr>
        <tr>
            <th><label for="cm_booking_status"><?php esc_html_e('Status', 'campusmarket'); ?></label></th>
            <td>
                <select id="cm_booking_status" name="cm_booking_status">
                    <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                    <option value="confirmed" <?php selected($status, 'confirmed'); ?>>Confirmed</option>
                    <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                    <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e('Total Price', 'campusmarket'); ?></th>
            <td>Rs. <?php echo esc_html($total_price); ?></td>
        </tr>
    </table>
<?php
}

/**
 * Save Booking Meta
 */
function cm_save_booking_meta($post_id)
{
    if (! isset($_POST['cm_booking_nonce']) || ! wp_verify_nonce($_POST['cm_booking_nonce'], 'cm_booking_details_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cm_start_date'])) {
        update_post_meta($post_id, '_cm_start_date', sanitize_text_field(wp_unslash($_POST['cm_start_date'])));
    }
    if (isset($_POST['cm_end_date'])) {
        update_post_meta($post_id, '_cm_end_date', sanitize_text_field(wp_unslash($_POST['cm_end_date'])));
    }
    if (isset($_POST['cm_booking_status'])) {
        update_post_meta($post_id, '_cm_status', sanitize_text_field(wp_unslash($_POST['cm_booking_status'])));
    }
}
add_action('save_post_cm_booking', 'cm_save_booking_meta');

/**
 * Only show approved listings on frontend queries
 */
function cm_filter_approved_listings($query)
{
    if (is_admin() || ! $query->is_main_query()) {
        return;
    }

    if (is_post_type_archive('cm_listing') || is_tax('listing_category') || is_tax('listing_tag')) {
        $query->set('meta_query', array(
            array(
                'key'   => '_cm_approval_status',
                'value' => 'approved',
            ),
        ));
        if (isset($_GET['category']) && ! empty($_GET['category'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'listing_category',
                    'field'    => 'term_id',
                    'terms'    => intval($_GET['category']),
                ),
            ));
        }
        $query->set('posts_per_page', 12);
    }

    // Search - filter to approved listings only
    if ($query->is_search() && isset($query->query_vars['post_type']) && 'cm_listing' === $query->query_vars['post_type']) {
        $query->set('meta_query', array(
            array(
                'key'   => '_cm_approval_status',
                'value' => 'approved',
            ),
        ));
    }
}
add_action('pre_get_posts', 'cm_filter_approved_listings');
