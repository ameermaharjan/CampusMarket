<?php

/**
 * Review Item Template Part
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$reviewer_id = get_post_meta(get_the_ID(), '_cm_reviewer_id', true);
$rating      = (int) get_post_meta(get_the_ID(), '_cm_rating', true);
$reviewer     = get_userdata($reviewer_id);
?>

<div class="cm-review-item">
    <div class="cm-review-item__header">
        <div class="cm-review-item__author">
            <?php echo get_avatar($reviewer_id, 40, '', '', array('class' => 'cm-review-item__avatar')); ?>
            <div>
                <span class="cm-review-item__name">
                    <?php echo $reviewer ? esc_html($reviewer->display_name) : 'Anonymous'; ?>
                    <?php if ($reviewer_id && cm_is_user_verified($reviewer_id)) : ?>
                        <span class="cm-verified-badge" title="Verified Student">✔</span>
                    <?php endif; ?>
                </span>
                <span class="cm-review-item__date"><?php echo esc_html(cm_time_ago(get_the_date('Y-m-d H:i:s'))); ?></span>
            </div>
        </div>
        <div class="cm-review-item__rating">
            <?php echo cm_get_star_html($rating); ?>
        </div>
    </div>
    <div class="cm-review-item__content">
        <?php the_content(); ?>
    </div>
</div>