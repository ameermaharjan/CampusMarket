<?php

/**
 * Single Listing Template — Item Detail Page
 *
 * @package CampusMarket
 */

get_header();

while (have_posts()) : the_post();

    $listing_id   = get_the_ID();
    $price        = get_post_meta($listing_id, '_cm_price', true);
    $price_type   = get_post_meta($listing_id, '_cm_price_type', true);
    $condition    = get_post_meta($listing_id, '_cm_condition', true);
    $location     = get_post_meta($listing_id, '_cm_location', true);
    $listing_type = get_post_meta($listing_id, '_cm_listing_type', true) ?: 'item';
    $avail_start  = get_post_meta($listing_id, '_cm_availability_start', true);
    $avail_end    = get_post_meta($listing_id, '_cm_availability_end', true);
    $author_id    = get_post_field('post_author', $listing_id);
    $author       = get_userdata($author_id);
    $avg_rating   = cm_get_average_rating($listing_id);
    $review_count = cm_get_review_count($listing_id);
    $categories   = get_the_terms($listing_id, 'listing_category');
    $is_owner     = cm_is_listing_owner($listing_id);
?>

    <div class="cm-section cm-listing-detail">
        <div class="cm-container">
            <!-- Breadcrumb -->
            <nav class="cm-breadcrumb">
                <a href="<?php echo esc_url(home_url()); ?>">Home</a>
                <span class="cm-breadcrumb__sep">/</span>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">Marketplace</a>
                <span class="cm-breadcrumb__sep">/</span>
                <?php if (! empty($categories) && ! is_wp_error($categories)) : ?>
                    <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                    <span class="cm-breadcrumb__sep">/</span>
                <?php endif; ?>
                <span class="cm-breadcrumb__current"><?php the_title(); ?></span>
            </nav>

            <div class="cm-listing-detail__grid">
                <!-- Left Column: Image + Description -->
                <div class="cm-listing-detail__main">
                    <!-- Image -->
                    <div class="cm-listing-detail__image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('cm-listing-detail'); ?>
                        <?php else : ?>
                            <div class="cm-listing-detail__placeholder">
                                <?php echo 'service' === $listing_type ? '🛠️' : '📦'; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="cm-listing-detail__description">
                        <h2>Description</h2>
                        <div class="cm-entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <div class="cm-listing-detail__reviews" id="cm-reviews-section">
                        <div class="cm-flex cm-flex--between">
                            <h2>Reviews <?php if ($review_count > 0) : ?><span class="cm-text-muted">(<?php echo esc_html($review_count); ?>)</span><?php endif; ?></h2>
                            <?php if ($review_count > 0) : ?>
                                <div class="cm-listing-detail__avg-rating">
                                    <?php echo cm_get_star_html($avg_rating); ?>
                                    <span class="cm-text-muted"><?php echo esc_html($avg_rating); ?>/5</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php
                        $reviews = cm_get_listing_reviews($listing_id);
                        if ($reviews->have_posts()) :
                            while ($reviews->have_posts()) : $reviews->the_post();
                                get_template_part('template-parts/review-item');
                            endwhile;
                            wp_reset_postdata();
                        else :
                        ?>
                            <p class="cm-text-muted">No reviews yet. Be the first to review!</p>
                        <?php endif; ?>

                        <!-- Review Form -->
                        <?php if (is_user_logged_in() && ! $is_owner) : ?>
                            <div class="cm-review-form" id="cm-review-form">
                                <h3>Leave a Review</h3>
                                <form id="cm-submit-review-form">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                                    <div class="cm-form-group">
                                        <label>Rating</label>
                                        <div class="cm-star-input" id="cm-star-input">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <span class="cm-star-input__star" data-rating="<?php echo $i; ?>">☆</span>
                                            <?php endfor; ?>
                                            <input type="hidden" name="rating" id="cm-rating-input" value="5">
                                        </div>
                                    </div>
                                    <div class="cm-form-group">
                                        <label for="cm-review-comment">Your Review</label>
                                        <textarea id="cm-review-comment" name="comment" class="cm-textarea" rows="4" placeholder="Share your experience..." required></textarea>
                                    </div>
                                    <button type="submit" class="cm-btn cm-btn--primary">Submit Review</button>
                                </form>
                                <div class="cm-review-form__message" id="cm-review-message"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="cm-listing-detail__sidebar">
                    <!-- Price & Info Card -->
                    <div class="cm-detail-card">
                        <div class="cm-detail-card__price">
                            <?php echo cm_get_price_display($listing_id); ?>
                        </div>

                        <div class="cm-detail-card__info">
                            <div class="cm-detail-card__row">
                                <span class="cm-detail-card__label">Type</span>
                                <span class="cm-detail-card__value"><?php echo cm_get_listing_type_label($listing_type); ?></span>
                            </div>
                            <?php if ($condition) : ?>
                                <div class="cm-detail-card__row">
                                    <span class="cm-detail-card__label">Condition</span>
                                    <span class="cm-detail-card__value"><?php echo esc_html(cm_get_condition_label($condition)); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($location) : ?>
                                <div class="cm-detail-card__row">
                                    <span class="cm-detail-card__label">Location</span>
                                    <span class="cm-detail-card__value">📍 <?php echo esc_html($location); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($avail_start && $avail_end) : ?>
                                <div class="cm-detail-card__row">
                                    <span class="cm-detail-card__label">Available</span>
                                    <span class="cm-detail-card__value"><?php echo esc_html($avail_start); ?> — <?php echo esc_html($avail_end); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (! empty($categories) && ! is_wp_error($categories)) : ?>
                                <div class="cm-detail-card__row">
                                    <span class="cm-detail-card__label">Category</span>
                                    <span class="cm-detail-card__value">
                                        <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="cm-detail-card__row">
                                <span class="cm-detail-card__label">Posted</span>
                                <span class="cm-detail-card__value"><?php echo esc_html(cm_time_ago(get_the_date('Y-m-d H:i:s'))); ?></span>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <?php if (is_user_logged_in() && ! $is_owner) : ?>
                            <div class="cm-booking-form" id="cm-booking-form">
                                <h3>Book This <?php echo esc_html(ucfirst($listing_type)); ?></h3>
                                <form id="cm-submit-booking-form">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                                    <input type="hidden" name="price" value="<?php echo esc_attr($price); ?>">
                                    <input type="hidden" name="price_type" value="<?php echo esc_attr($price_type); ?>">
                                    <div class="cm-form-group">
                                        <label for="cm-start-date">Start Date</label>
                                        <input type="date" id="cm-start-date" name="start_date" class="cm-input" required min="<?php echo esc_attr(date('Y-m-d')); ?>">
                                    </div>
                                    <div class="cm-form-group">
                                        <label for="cm-end-date">End Date</label>
                                        <input type="date" id="cm-end-date" name="end_date" class="cm-input" required min="<?php echo esc_attr(date('Y-m-d')); ?>">
                                    </div>
                                    <div class="cm-booking-total" id="cm-booking-total" style="display:none;">
                                        <span>Estimated Total:</span>
                                        <strong id="cm-total-price">Rs. 0.00</strong>
                                    </div>
                                    <button type="submit" class="cm-btn cm-btn--primary cm-btn--block">
                                        📅 Request Booking
                                    </button>
                                </form>
                                <div class="cm-booking-form__message" id="cm-booking-message"></div>
                            </div>
                        <?php elseif (! is_user_logged_in()) : ?>
                            <div class="cm-detail-card__login">
                                <p>Log in to book this item or contact the seller.</p>
                                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="cm-btn cm-btn--primary cm-btn--block">Log In</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Seller Info Card -->
                    <div class="cm-detail-card cm-seller-card">
                        <h3>About the Seller</h3>
                        <div class="cm-seller-card__profile">
                            <?php echo get_avatar($author_id, 64, '', '', array('class' => 'cm-seller-card__avatar')); ?>
                            <div class="cm-seller-card__info">
                                <h4>
                                    <?php echo esc_html($author->display_name); ?>
                                    <?php if (cm_is_user_verified($author_id)) : ?>
                                        <span class="cm-verified-badge" title="Verified Student">✔</span>
                                    <?php endif; ?>
                                </h4>
                                <span class="cm-text-muted">
                                    <?php echo esc_html(cm_count_user_listings($author_id)); ?> listings
                                </span>
                            </div>
                        </div>
                        <?php if (is_user_logged_in() && ! $is_owner) : ?>
                            <a href="<?php echo esc_url(home_url('/chat/?with=' . $author_id)); ?>" class="cm-btn cm-btn--outline cm-btn--block">
                                💬 Contact Seller
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
endwhile;
get_footer();
