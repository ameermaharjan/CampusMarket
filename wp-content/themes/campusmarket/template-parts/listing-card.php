<?php

/**
 * Listing Card Template Part
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$listing_type = get_post_meta(get_the_ID(), '_cm_listing_type', true) ?: 'item';
$condition    = get_post_meta(get_the_ID(), '_cm_condition', true);
$location     = get_post_meta(get_the_ID(), '_cm_location', true);
$avg_rating   = cm_get_average_rating(get_the_ID());
$review_count = cm_get_review_count(get_the_ID());
$categories   = get_the_terms(get_the_ID(), 'listing_category');
$author_id    = get_post_field('post_author', get_the_ID());
?>

<article class="cm-listing-card" id="listing-<?php the_ID(); ?>">
    <a href="<?php the_permalink(); ?>" class="cm-listing-card__link">
        <div class="cm-listing-card__image">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('cm-listing-card'); ?>
            <?php else : ?>
                <div class="cm-listing-card__placeholder">
                    <?php echo 'service' === $listing_type ? '🛠️' : '📦'; ?>
                </div>
            <?php endif; ?>

            <div class="cm-listing-card__badges">
                <span class="cm-badge cm-badge--type"><?php echo esc_html(ucfirst($listing_type)); ?></span>
                <?php if ($condition) : ?>
                    <span class="cm-badge cm-badge--condition"><?php echo esc_html(cm_get_condition_label($condition)); ?></span>
                <?php endif; ?>
            </div>

            <div class="cm-listing-card__price-tag">
                <?php echo cm_get_price_display(get_the_ID()); ?>
            </div>
        </div>

        <div class="cm-listing-card__body">
            <h3 class="cm-listing-card__title"><?php the_title(); ?></h3>

            <?php if (! empty($categories) && ! is_wp_error($categories)) : ?>
                <span class="cm-listing-card__category">
                    <?php echo esc_html($categories[0]->name); ?>
                </span>
            <?php endif; ?>

            <?php if ($location) : ?>
                <span class="cm-listing-card__location">📍 <?php echo esc_html($location); ?></span>
            <?php endif; ?>

            <div class="cm-listing-card__footer">
                <div class="cm-listing-card__rating">
                    <?php if ($review_count > 0) : ?>
                        <?php echo cm_get_star_html($avg_rating); ?>
                        <span class="cm-listing-card__review-count">(<?php echo esc_html($review_count); ?>)</span>
                    <?php else : ?>
                        <span class="cm-listing-card__no-reviews">No reviews yet</span>
                    <?php endif; ?>
                </div>
                <div class="cm-listing-card__author">
                    <?php echo get_avatar($author_id, 24, '', '', array('class' => 'cm-listing-card__avatar')); ?>
                    <span><?php echo esc_html(get_the_author()); ?></span>
                    <?php if (cm_is_user_verified($author_id)) : ?>
                        <span class="cm-verified-badge" title="Verified Student">✔</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </a>
</article>