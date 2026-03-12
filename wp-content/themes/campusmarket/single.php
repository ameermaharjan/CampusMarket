<?php

/**
 * Generic single post template
 *
 * @package CampusMarket
 */

get_header();
?>

<div class="cm-section">
    <div class="cm-container cm-container--narrow">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('cm-single-post'); ?>>
                <header class="cm-post-header">
                    <h1 class="cm-post-header__title"><?php the_title(); ?></h1>
                    <div class="cm-post-header__meta">
                        <span class="cm-post-header__author">
                            <?php echo get_avatar(get_the_author_meta('ID'), 32); ?>
                            <?php the_author(); ?>
                        </span>
                        <span class="cm-post-header__date"><?php echo esc_html(get_the_date()); ?></span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="cm-post-featured-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="cm-entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="cm-post-footer">
                    <?php
                    the_post_navigation(array(
                        'prev_text' => '&larr; %title',
                        'next_text' => '%title &rarr;',
                    ));
                    ?>
                </footer>

                <?php
                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
                ?>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php
get_footer();
