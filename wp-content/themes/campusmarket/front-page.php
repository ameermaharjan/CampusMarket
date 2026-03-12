<?php

/**
 * Front Page Template — Homepage
 *
 * @package CampusMarket
 */

get_header();
?>

<!-- Hero Section -->
<section class="cm-hero">
    <div class="cm-container cm-hero__content">
        <h1 class="cm-hero__title">
            Rent, Share &amp; Connect<br>
            <span class="cm-hero__highlight">With Your Campus Community</span>
        </h1>
        <p class="cm-hero__subtitle">
            The peer-to-peer marketplace for students. Rent textbooks, electronics, offer tutoring services, and more.
        </p>
        <div class="cm-hero__search">
            <?php get_search_form(); ?>
        </div>
        <div class="cm-hero__stats">
            <?php
            $listing_count = wp_count_posts('cm_listing')->publish;
            $user_count    = count_users();
            ?>
            <div class="cm-hero__stat">
                <span class="cm-hero__stat-number"><?php echo esc_html($listing_count); ?></span>
                <span class="cm-hero__stat-label">Active Listings</span>
            </div>
            <div class="cm-hero__stat">
                <span class="cm-hero__stat-number"><?php echo esc_html($user_count['total_users']); ?></span>
                <span class="cm-hero__stat-label">Students</span>
            </div>
            <div class="cm-hero__stat">
                <span class="cm-hero__stat-number"><?php
                                                     $cats = get_terms(array('taxonomy' => 'listing_category', 'hide_empty' => false));
                                                     echo ! is_wp_error($cats) ? count($cats) : '0';
                                                     ?></span>
                <span class="cm-hero__stat-label">Categories</span>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="cm-section cm-categories-section">
    <div class="cm-container">
        <div class="cm-section-header">
            <h2 class="cm-section-header__title">Browse by Category</h2>
            <p class="cm-section-header__subtitle">Find what you need quickly</p>
        </div>

        <?php
        $category_icons = array(
            'books'       => '📚',
            'electronics' => '💻',
            'tutoring'    => '📝',
            'sports'      => '⚽',
            'stationery'  => '✏️',
            'music'       => '🎵',
            'other'       => '📦',
        );

        $categories = get_terms(array(
            'taxonomy'   => 'listing_category',
            'hide_empty' => false,
        ));

        if (! is_wp_error($categories) && ! empty($categories)) :
        ?>
            <div class="cm-grid cm-grid--4 cm-category-grid">
                <?php foreach ($categories as $cat) :
                    $slug = strtolower($cat->slug);
                    $icon = isset($category_icons[$slug]) ? $category_icons[$slug] : '📦';
                ?>
                    <a href="<?php echo esc_url(add_query_arg('category', $cat->term_id, home_url('/browse/'))); ?>" class="cm-category-card">
                        <span class="cm-category-card__icon"><?php echo $icon; ?></span>
                        <h3 class="cm-category-card__name"><?php echo esc_html($cat->name); ?></h3>
                        <span class="cm-category-card__count"><?php echo esc_html($cat->count); ?> listings</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Listings Section -->
<section class="cm-section cm-featured-section">
    <div class="cm-container">
        <div class="cm-section-header">
            <h2 class="cm-section-header__title">Latest Listings</h2>
            <p class="cm-section-header__subtitle">Recently added items and services</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-section-header__link">
                View All &rarr;
            </a>
        </div>

        <?php
        $featured = new WP_Query(array(
            'post_type'      => 'cm_listing',
            'posts_per_page' => 8,
            'meta_query'     => array(
                array(
                    'key'   => '_cm_approval_status',
                    'value' => 'approved',
                ),
            ),
            'orderby' => 'date',
            'order'   => 'DESC',
        ));

        if ($featured->have_posts()) :
        ?>
            <div class="cm-grid cm-grid--4">
                <?php while ($featured->have_posts()) : $featured->the_post(); ?>
                    <?php get_template_part('template-parts/listing-card'); ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="cm-empty-state">
                <div class="cm-empty-state__icon">🏪</div>
                <h3>No listings yet</h3>
                <p>Be the first to list an item or service!</p>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--primary">List Your First Item</a>
                <?php endif; ?>
            </div>
        <?php endif;
        wp_reset_postdata(); ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="cm-section cm-how-it-works">
    <div class="cm-container">
        <div class="cm-section-header">
            <h2 class="cm-section-header__title">How It Works</h2>
            <p class="cm-section-header__subtitle">Get started in 3 simple steps</p>
        </div>

        <div class="cm-grid cm-grid--3">
            <div class="cm-step-card">
                <div class="cm-step-card__number">1</div>
                <div class="cm-step-card__icon">📝</div>
                <h3 class="cm-step-card__title">List Your Item</h3>
                <p class="cm-step-card__desc">Create a listing for your item or service. Add photos, set your price, and describe what you're offering.</p>
            </div>
            <div class="cm-step-card">
                <div class="cm-step-card__number">2</div>
                <div class="cm-step-card__icon">🔍</div>
                <h3 class="cm-step-card__title">Browse &amp; Book</h3>
                <p class="cm-step-card__desc">Search the marketplace, filter by category, and book what you need with just a few clicks.</p>
            </div>
            <div class="cm-step-card">
                <div class="cm-step-card__number">3</div>
                <div class="cm-step-card__icon">🤝</div>
                <h3 class="cm-step-card__title">Connect &amp; Exchange</h3>
                <p class="cm-step-card__desc">Chat with the owner, arrange pickup, and leave a review after your experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cm-section cm-cta-section">
    <div class="cm-container">
        <div class="cm-cta">
            <h2 class="cm-cta__title">Ready to Start?</h2>
            <p class="cm-cta__text">Join your campus community and start sharing resources today.</p>
            <div class="cm-cta__buttons">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--accent cm-btn--lg">List an Item</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--outline-white cm-btn--lg">Browse Marketplace</a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="cm-btn cm-btn--accent cm-btn--lg">Sign Up Free</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--outline-white cm-btn--lg">Browse Marketplace</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
