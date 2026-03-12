<?php

/**
 * Index template - Main fallback template
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
                if (is_home() && ! is_front_page()) {
                    single_post_title();
                } else {
                    esc_html_e('Latest Posts', 'campusmarket');
                }
                ?>
            </h1>
        </header>

        <?php if (have_posts()) : ?>
            <div class="cm-grid cm-grid--3">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('cm-post-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="cm-post-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('cm-listing-card'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="cm-post-card__content">
                            <h2 class="cm-post-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="cm-post-card__excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <div class="cm-post-card__meta">
                                <span class="cm-post-card__date"><?php echo esc_html(get_the_date()); ?></span>
                            </div>
                        </div>
                    </article>
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
                <h2><?php esc_html_e('Nothing found', 'campusmarket'); ?></h2>
                <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for.', 'campusmarket'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
