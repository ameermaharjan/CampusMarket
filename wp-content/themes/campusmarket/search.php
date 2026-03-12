<?php

/**
 * Search results template
 *
 * @package CampusMarket
 */

get_header();
?>

<div class="cm-section">
    <div class="cm-container">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title">
                <?php
                printf(
                    esc_html__('Search Results for: "%s"', 'campusmarket'),
                    '<span>' . get_search_query() . '</span>'
                );
                ?>
            </h1>
            <p class="cm-page-header__count">
                <?php
                global $wp_query;
                printf(
                    esc_html(_n('%d result found', '%d results found', $wp_query->found_posts, 'campusmarket')),
                    intval($wp_query->found_posts)
                );
                ?>
            </p>
        </header>

        <div class="cm-search-form--inline">
            <?php get_search_form(); ?>
        </div>

        <?php if (have_posts()) : ?>
            <div class="cm-grid cm-grid--3">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    if ('cm_listing' === get_post_type()) {
                        get_template_part('template-parts/listing-card');
                    } else {
                    ?>
                        <article class="cm-post-card">
                            <div class="cm-post-card__content">
                                <h2 class="cm-post-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <div class="cm-post-card__excerpt"><?php the_excerpt(); ?></div>
                            </div>
                        </article>
                    <?php
                    }
                    ?>
                <?php endwhile; ?>
            </div>

            <div class="cm-pagination">
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
                <div class="cm-empty-state__icon">🔍</div>
                <h2><?php esc_html_e('No results found', 'campusmarket'); ?></h2>
                <p><?php esc_html_e('Try adjusting your search terms or browse our categories.', 'campusmarket'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--primary">
                    Browse All Listings
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
