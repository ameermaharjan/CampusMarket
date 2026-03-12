<?php

/**
 * 404 Page Template
 *
 * @package CampusMarket
 */

get_header();
?>

<div class="cm-section">
    <div class="cm-container cm-container--narrow cm-flex cm-flex--center cm-flex--column" style="min-height: 50vh; text-align: center;">
        <div class="cm-404">
            <div class="cm-404__icon">🔍</div>
            <h1 class="cm-404__title">404</h1>
            <h2 class="cm-404__subtitle"><?php esc_html_e('Page Not Found', 'campusmarket'); ?></h2>
            <p class="cm-404__text">
                <?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'campusmarket'); ?>
            </p>
            <div class="cm-404__actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-btn cm-btn--primary">
                    Go to Homepage
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--outline">
                    Browse Marketplace
                </a>
            </div>
            <div class="cm-404__search">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
