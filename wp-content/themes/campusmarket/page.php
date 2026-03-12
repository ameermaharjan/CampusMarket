<?php

/**
 * Generic page template
 *
 * @package CampusMarket
 */

get_header();
?>

<div class="cm-section">
    <div class="cm-container cm-container--narrow">
        <?php while (have_posts()) : the_post(); ?>
            <article id="page-<?php the_ID(); ?>" <?php post_class('cm-page-content'); ?>>
                <header class="cm-page-header">
                    <h1 class="cm-page-header__title"><?php the_title(); ?></h1>
                </header>
                <div class="cm-entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php
get_footer();
