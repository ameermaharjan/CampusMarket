<?php

/**
 * Booking Card Template Part
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$booking_id   = get_the_ID();
$listing_id   = get_post_meta($booking_id, '_cm_listing_id', true);
$renter_id    = get_post_meta($booking_id, '_cm_renter_id', true);
$owner_id     = get_post_meta($booking_id, '_cm_owner_id', true);
$start_date   = get_post_meta($booking_id, '_cm_start_date', true);
$end_date     = get_post_meta($booking_id, '_cm_end_date', true);
$status       = get_post_meta($booking_id, '_cm_status', true) ?: 'pending';
$total_price  = get_post_meta($booking_id, '_cm_total_price', true);
$listing_title = get_the_title($listing_id);
$is_owner     = get_current_user_id() === (int) $owner_id;
?>

<div class="cm-booking-card" data-booking-id="<?php echo esc_attr($booking_id); ?>">
    <div class="cm-booking-card__image">
        <?php
        if ($listing_id && has_post_thumbnail($listing_id)) {
            echo get_the_post_thumbnail($listing_id, 'thumbnail');
        } else {
            echo '<div class="cm-booking-card__placeholder">📦</div>';
        }
        ?>
    </div>

    <div class="cm-booking-card__details">
        <h4 class="cm-booking-card__title">
            <a href="<?php echo esc_url(get_permalink($listing_id)); ?>">
                <?php echo esc_html($listing_title); ?>
            </a>
        </h4>
        <div class="cm-booking-card__meta">
            <span class="cm-booking-card__dates">📅 <?php echo esc_html($start_date); ?> → <?php echo esc_html($end_date); ?></span>
            <span class="cm-booking-card__price">💰 Rs. <?php echo esc_html(number_format((float) $total_price, 2)); ?></span>
            <?php if ($is_owner) : ?>
                <span class="cm-booking-card__user">👤 Renter: <?php echo esc_html(get_userdata($renter_id)->display_name); ?></span>
            <?php else : ?>
                <span class="cm-booking-card__user">👤 Owner: <?php echo esc_html(get_userdata($owner_id)->display_name); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="cm-booking-card__status">
        <?php echo cm_get_status_badge($status); ?>
    </div>

    <div class="cm-booking-card__actions">
        <?php if ($is_owner && 'pending' === $status) : ?>
            <button class="cm-btn cm-btn--success cm-btn--sm cm-booking-action" data-action="confirmed" data-booking-id="<?php echo esc_attr($booking_id); ?>">
                ✅ Accept
            </button>
            <button class="cm-btn cm-btn--danger cm-btn--sm cm-booking-action" data-action="cancelled" data-booking-id="<?php echo esc_attr($booking_id); ?>">
                ❌ Decline
            </button>
        <?php elseif (! $is_owner && 'pending' === $status) : ?>
            <button class="cm-btn cm-btn--danger cm-btn--sm cm-booking-action" data-action="cancelled" data-booking-id="<?php echo esc_attr($booking_id); ?>">
                Cancel
            </button>
        <?php elseif ($is_owner && 'confirmed' === $status) : ?>
            <button class="cm-btn cm-btn--primary cm-btn--sm cm-booking-action" data-action="completed" data-booking-id="<?php echo esc_attr($booking_id); ?>">
                Mark Complete
            </button>
        <?php endif; ?>

        <a href="<?php echo esc_url(home_url('/chat/?with=' . ($is_owner ? $renter_id : $owner_id))); ?>" class="cm-btn cm-btn--ghost cm-btn--sm">
            💬 Chat
        </a>
    </div>
</div>