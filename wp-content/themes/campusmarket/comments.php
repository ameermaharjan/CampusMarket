<?php

/**
 * Comments template
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="cm-comments">
    <?php if (have_comments()) : ?>
        <h3 class="cm-comments__title">
            <?php
            $comment_count = get_comments_number();
            printf(
                esc_html(_n('%d Comment', '%d Comments', $comment_count, 'campusmarket')),
                intval($comment_count)
            );
            ?>
        </h3>

        <ol class="cm-comments__list">
            <?php
            wp_list_comments(array(
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size' => 48,
            ));
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php
    comment_form(array(
        'class_form'   => 'cm-comment-form',
        'title_reply'  => esc_html__('Leave a Comment', 'campusmarket'),
        'label_submit' => esc_html__('Post Comment', 'campusmarket'),
    ));
    ?>
</div>