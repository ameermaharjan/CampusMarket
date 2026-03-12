<?php

/**
 * Header template
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'campusmarket'); ?></a>

    <header class="cm-header" id="cm-header">
        <div class="cm-container cm-flex cm-flex--between">

            <!-- Logo / Site Branding -->
            <div class="cm-header__brand">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-header__logo-text">
                        <span class="cm-header__logo-icon">🎓</span>
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Primary Navigation -->
            <nav class="cm-header__nav" id="cm-main-nav" aria-label="<?php esc_attr_e('Primary Navigation', 'campusmarket'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'cm-nav-list',
                    'menu_id'        => 'primary-menu',
                    'depth'          => 2,
                    'fallback_cb'    => 'cm_fallback_menu',
                ));
                ?>
            </nav>

            <!-- User Actions -->
            <div class="cm-header__actions">
                <?php if (is_user_logged_in()) : ?>
                    <!-- Search Integrated in Header -->
                    <div class="cm-header__search-mini">
                        <?php get_search_form(); ?>
                    </div>
                    <?php $current_user = wp_get_current_user(); ?>
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--primary cm-btn--sm">
                        <span class="cm-btn__icon">+</span> List Item
                    </a>
                    <div class="cm-header__user-menu" id="cm-user-menu">
                        <button class="cm-header__user-toggle" id="cm-user-toggle" aria-expanded="false">
                            <?php echo get_avatar($current_user->ID, 36, '', '', array('class' => 'cm-header__avatar')); ?>
                            <span class="cm-header__username"><?php echo esc_html($current_user->display_name); ?></span>
                            <svg class="cm-header__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="cm-header__dropdown" id="cm-user-dropdown">
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="cm-header__dropdown-item">
                                📊 Dashboard
                            </a>
                            <a href="<?php echo esc_url(home_url('/chat/')); ?>" class="cm-header__dropdown-item">
                                💬 Messages
                            </a>
                            <?php if (current_user_can('manage_options')) : ?>
                                <a href="<?php echo esc_url(home_url('/admin-panel/')); ?>" class="cm-header__dropdown-item">
                                    ⚙️ Admin Panel
                                </a>
                            <?php endif; ?>
                            <hr class="cm-header__dropdown-divider">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="cm-header__dropdown-item cm-header__dropdown-item--danger">
                                🚪 Logout
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="cm-header__search-mini">
                        <?php get_search_form(); ?>
                    </div>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="cm-nav-link cm-text-sm">Log In</a>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="cm-btn cm-btn--primary cm-btn--sm">Join Now</a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button class="cm-header__hamburger" id="cm-hamburger" aria-label="<?php esc_attr_e('Toggle Menu', 'campusmarket'); ?>" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

        </div>
    </header>

    <main id="main-content" class="cm-main">
        <?php

        /**
         * Fallback menu if no menu is assigned
         */
        function cm_fallback_menu()
        {
            echo '<ul class="cm-nav-list">';
            echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
            echo '<li><a href="' . esc_url(get_post_type_archive_link('cm_listing')) . '">Browse</a></li>';
            if (is_user_logged_in()) {
                echo '<li><a href="' . esc_url(home_url('/dashboard/')) . '">Dashboard</a></li>';
            }
            echo '</ul>';
        }
        ?>
<script>
(function(){
    var h = document.getElementById('cm-header');
    if(!h) return;
    window.addEventListener('scroll', function(){
        h.classList.toggle('cm-header--scrolled', window.scrollY > 20);
    }, {passive:true});
})();
</script>
