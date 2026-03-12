<?php

/**
 * Listing Category Taxonomy Archive
 *
 * @package CampusMarket
 */

get_header();

$term = get_queried_object();
?>

<div class="cm-section cm-browse-section">
    <div class="cm-container">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title"><?php echo esc_html($term->name); ?></h1>
            <?php if ($term->description) : ?>
                <p class="cm-page-header__subtitle"><?php echo esc_html($term->description); ?></p>
            <?php else : ?>
                <p class="cm-page-header__subtitle"><?php printf(esc_html__('Showing all listings in %s', 'campusmarket'), esc_html($term->name)); ?></p>
            <?php endif; ?>
        </header>

        <?php get_template_part('template-parts/filter-bar'); ?>

        <div class="cm-listings-grid" id="cm-listings-grid">
            <?php if (have_posts()) : ?>
                <div class="cm-grid cm-grid--3" id="cm-listings-container">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/listing-card'); ?>
                    <?php endwhile; ?>
                </div>
                <div class="cm-pagination">
                    <?php the_posts_pagination(array('mid_size' => 2, 'prev_text' => '&larr;', 'next_text' => '&rarr;')); ?>
                </div>
            <?php else : ?>
                <div class="cm-empty-state">
                    <div class="cm-empty-state__icon">📭</div>
                    <h2>No listings in this category yet</h2>
                    <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--primary">Browse All Listings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
