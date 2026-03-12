<?php

/**
 * CampusMarket Theme Functions
 *
 * @package CampusMarket
 * @version 1.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// Theme constants
define('CM_VERSION', '1.0.0');
define('CM_DIR', get_template_directory());
define('CM_URI', get_template_directory_uri());

/**
 * ─── INCLUDE FILES ──────────────────────────────────────
 */
require_once CM_DIR . '/inc/custom-post-types.php';
require_once CM_DIR . '/inc/user-roles.php';
require_once CM_DIR . '/inc/admin-columns.php';
require_once CM_DIR . '/inc/template-functions.php';
require_once CM_DIR . '/inc/booking-functions.php';
require_once CM_DIR . '/inc/review-functions.php';
require_once CM_DIR . '/inc/chat-functions.php';
require_once CM_DIR . '/inc/ajax-handlers.php';
require_once CM_DIR . '/inc/setup-pages.php';

/**
 * ─── THEME SETUP ────────────────────────────────────────
 */
function cm_theme_setup()
{
    // Title tag support
    add_theme_support('title-tag');

    // Post thumbnails
    add_theme_support('post-thumbnails');
    add_image_size('cm-listing-card', 400, 300, true);
    add_image_size('cm-listing-detail', 800, 600, true);
    add_image_size('cm-avatar', 80, 80, true);

    // HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Custom logo
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Register nav menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'campusmarket'),
        'footer'  => esc_html__('Footer Menu', 'campusmarket'),
    ));
}
add_action('after_setup_theme', 'cm_theme_setup');

/**
 * ─── ENQUEUE STYLES & SCRIPTS ───────────────────────────
 */
function cm_enqueue_assets()
{
    // Google Fonts: Inter + Outfit
    wp_enqueue_style(
        'cm-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap',
        array(),
        null
    );

    // Theme stylesheet (style.css)
    wp_enqueue_style('cm-style', get_stylesheet_uri(), array('cm-google-fonts'), CM_VERSION);

    // Main CSS
    wp_enqueue_style('cm-main', CM_URI . '/assets/css/main.css', array('cm-style'), CM_VERSION);

    // Dashboard CSS (only on dashboard page)
    if (is_page_template('page-templates/page-dashboard.php') || is_page_template('page-templates/page-admin-panel.php')) {
        wp_enqueue_style('cm-dashboard', CM_URI . '/assets/css/dashboard.css', array('cm-main'), CM_VERSION);
    }

    // Chat CSS (only on chat page)
    if (is_page_template('page-templates/page-chat.php')) {
        wp_enqueue_style('cm-chat', CM_URI . '/assets/css/chat.css', array('cm-main'), CM_VERSION);
    }

    // Main JS
    wp_enqueue_script('cm-main', CM_URI . '/assets/js/main.js', array('jquery'), CM_VERSION, true);

    // Localize script with AJAX URL and nonce
    wp_localize_script('cm-main', 'cmData', array(
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('cm_nonce'),
        'themeUrl'    => CM_URI,
        'isLoggedIn'  => is_user_logged_in(),
        'currentUser' => get_current_user_id(),
    ));

    // Listing form JS
    if (is_page_template('page-templates/page-list-item.php')) {
        wp_enqueue_script('cm-listing-form', CM_URI . '/assets/js/listing-form.js', array('jquery', 'cm-main'), CM_VERSION, true);
    }

    // Filters JS (browse page)
    if (is_post_type_archive('cm_listing') || is_tax('listing_category') || is_search()) {
        wp_enqueue_script('cm-filters', CM_URI . '/assets/js/filters.js', array('jquery', 'cm-main'), CM_VERSION, true);
    }

    // Booking JS (single listing)
    if (is_singular('cm_listing')) {
        wp_enqueue_script('cm-booking', CM_URI . '/assets/js/booking.js', array('jquery', 'cm-main'), CM_VERSION, true);
    }

    // Dashboard JS
    if (is_page_template('page-templates/page-dashboard.php') || is_page_template('page-templates/page-admin-panel.php')) {
        wp_enqueue_script('cm-dashboard', CM_URI . '/assets/js/dashboard.js', array('jquery', 'cm-main'), CM_VERSION, true);
    }

    // Chat JS
    if (is_page_template('page-templates/page-chat.php')) {
        wp_enqueue_script('cm-chat', CM_URI . '/assets/js/chat.js', array('jquery', 'cm-main'), CM_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'cm_enqueue_assets');

/**
 * ─── WIDGET AREAS ───────────────────────────────────────
 */
function cm_widgets_init()
{
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'campusmarket'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'campusmarket'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widgets', 'campusmarket'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Footer widget area.', 'campusmarket'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'cm_widgets_init');

/**
 * ─── CUSTOM EXCERPT LENGTH ──────────────────────────────
 */
function cm_excerpt_length($length)
{
    return 20;
}
add_filter('excerpt_length', 'cm_excerpt_length');

function cm_excerpt_more($more)
{
    return '&hellip;';
}
add_filter('excerpt_more', 'cm_excerpt_more');

/**
 * ─── BODY CLASS ─────────────────────────────────────────
 */
function cm_body_classes($classes)
{
    if (is_user_logged_in()) {
        $classes[] = 'cm-logged-in';
    }
    if (is_front_page()) {
        $classes[] = 'cm-front-page';
    }
    return $classes;
}
add_filter('body_class', 'cm_body_classes');

/**
 * ─── ALLOW STUDENTS TO UPLOAD FILES ─────────────────────
 */
function cm_allow_student_uploads($caps, $cap, $user_id)
{
    if ('upload_files' === $cap) {
        $user = get_userdata($user_id);
        if ($user && in_array('student', (array) $user->roles, true)) {
            $caps = array('exist');
        }
    }
    return $caps;
}
add_filter('map_meta_cap', 'cm_allow_student_uploads', 10, 3);

/**
 * ─── REDIRECT AFTER LOGIN ───────────────────────────────
 */
function cm_login_redirect($redirect_to, $request, $user)
{
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('student', $user->roles, true)) {
            return home_url('/dashboard/');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'cm_login_redirect', 10, 3);

/**
 * ─── FLUSH REWRITE RULES ON THEME ACTIVATION ────────────
 */
function cm_rewrite_flush()
{
    cm_register_post_types();
    cm_register_taxonomies();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'cm_rewrite_flush');
