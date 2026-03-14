<?php

/**
 * Auto-create essential pages on theme activation
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Creates essential pages for the theme and assigns templates
 */
function cm_create_essential_pages()
{
    $pages = array(
        'register' => array(
            'title'    => 'Register',
            'content'  => '',
            'template' => 'page-templates/page-register.php',
        ),
        'login' => array(
            'title'    => 'Login',
            'content'  => '',
            'template' => 'page-templates/page-login.php',
        ),
        'dashboard' => array(
            'title'    => 'Dashboard',
            'content'  => '',
            'template' => 'page-templates/page-dashboard.php',
        ),
        'list-item' => array(
            'title'    => 'List an Item',
            'content'  => '',
            'template' => 'page-templates/page-list-item.php',
        ),
        'chat' => array(
            'title'    => 'Chat',
            'content'  => '',
            'template' => 'page-templates/page-chat.php',
        ),
        'admin-panel' => array(
            'title'    => 'Admin Panel',
            'content'  => '',
            'template' => 'page-templates/page-admin-panel.php',
        ),
        'verify' => array(
            'title'    => 'Student Verification',
            'content'  => '',
            'template' => 'page-templates/page-verify.php',
        ),
        'verification-pending' => array(
            'title'    => 'Verification Pending',
            'content'  => '',
            'template' => 'page-templates/page-verification-pending.php',
        ),
        'about' => array(
            'title'    => 'About Us',
            'content'  => 'Welcome to CampusMarket, the premier marketplace for university students.',
        ),
        'terms' => array(
            'title'    => 'Terms of Service',
            'content'  => 'Please read these terms carefully before using our service.',
        ),
        'privacy' => array(
            'title'    => 'Privacy Policy',
            'content'  => 'Your privacy is important to us.',
        ),
        'safety' => array(
            'title'    => 'Safety Guidelines',
            'content'  => 'How to trade safely on campus.',
        ),
        'contact' => array(
            'title'    => 'Contact Us',
            'content'  => 'Get in touch with the CampusMarket team.',
        ),
    );

    foreach ($pages as $slug => $page_data) {
        $page_check = get_page_by_path($slug);

        if (!isset($page_check->ID)) {
            $new_page_id = wp_insert_post(array(
                'post_type'      => 'page',
                'post_title'     => $page_data['title'],
                'post_content'   => $page_data['content'],
                'post_status'    => 'publish',
                'post_name'      => $slug,
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ));

            if ($new_page_id && !is_wp_error($new_page_id)) {
                update_post_meta($new_page_id, '_wp_page_template', $page_data['template']);
            }
        } else {
            // Even if the page exists, ensure the template is set properly
            update_post_meta($page_check->ID, '_wp_page_template', $page_data['template']);
        }
    }
}
add_action('after_switch_theme', 'cm_create_essential_pages');

// For development: Also run this on 'admin_init' once so the user doesn't have to re-activate the theme right now
function cm_create_pages_dev_helper()
{
    if (get_option('cm_pages_created_v3') !== 'yes') {
        cm_create_essential_pages();
        update_option('cm_pages_created_v3', 'yes');
    }
}
add_action('admin_init', 'cm_create_pages_dev_helper');
