<?php

/**
 * Archive template for cm_listing — Browse Marketplace
 *
 * @package CampusMarket
 */

get_header();
?>

<div class="cm-section cm-browse-section">
    <div class="cm-container">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title">
                <?php
                if (is_search()) {
                    printf(esc_html__('Results for "%s"', 'campusmarket'), get_search_query());
                } else {
                    esc_html_e('Browse Marketplace', 'campusmarket');
                }
                ?>
            </h1>
            <p class="cm-page-header__subtitle">
                <?php esc_html_e('Find items and services from fellow students', 'campusmarket'); ?>
            </p>
        </header>

        <!-- Filter Bar -->
        <?php get_template_part('template-parts/filter-bar'); ?>

        <!-- Listings Grid -->
        <div class="cm-listings-grid" id="cm-listings-grid">
            <?php if (have_posts()) : ?>
                <div class="cm-grid cm-grid--3" id="cm-listings-container">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/listing-card'); ?>
                    <?php endwhile; ?>
                </div>

                <div class="cm-pagination" id="cm-pagination">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => '&larr; Previous',
                        'next_text' => 'Next &rarr;',
                    ));
                    ?>
                </div>
            <?php else : ?>
                <div class="cm-empty-state">
                    <div class="cm-empty-state__icon">📭</div>
                    <h2><?php esc_html_e('No listings found', 'campusmarket'); ?></h2>
                    <p><?php esc_html_e('Be the first to list an item or service!', 'campusmarket'); ?></p>
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--primary">List an Item</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Loading Spinner -->
        <div class="cm-loading" id="cm-loading" style="display:none;">
            <div class="cm-spinner"></div>
            <p>Loading listings...</p>
        </div>
    </div>
</div>

<?php
get_footer();
