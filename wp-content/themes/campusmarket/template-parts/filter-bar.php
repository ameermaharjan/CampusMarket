<?php

/**
 * Filter Bar Template Part
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$categories = get_terms(array(
    'taxonomy'   => 'listing_category',
    'hide_empty' => false,
));

$current_cat = get_queried_object();
?>

<div class="cm-filter-bar" id="cm-filter-bar">
    <form class="cm-filter-bar__form" id="cm-filter-form">
        <div class="cm-filter-bar__row">
            <!-- Search -->
            <div class="cm-filter-bar__group">
                <label for="cm-filter-search" class="cm-filter-bar__label">Search</label>
                <input type="text" id="cm-filter-search" name="search" class="cm-input" placeholder="Search listings..." value="<?php echo esc_attr(get_search_query()); ?>">
            </div>

            <!-- Category -->
            <div class="cm-filter-bar__group">
                <label for="cm-filter-category" class="cm-filter-bar__label">Category</label>
                <select id="cm-filter-category" name="category" class="cm-select">
                    <option value="">All Categories</option>
                    <?php if (! is_wp_error($categories)) : ?>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"
                                <?php
                                $is_selected = false;
                                if (is_tax('listing_category') && $current_cat->term_id === $cat->term_id) {
                                    $is_selected = true;
                                } elseif (isset($_GET['category']) && intval($_GET['category']) === $cat->term_id) {
                                    $is_selected = true;
                                }
                                selected($is_selected);
                                ?>>
                                <?php echo esc_html($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Listing Type -->
            <div class="cm-filter-bar__group">
                <label for="cm-filter-type" class="cm-filter-bar__label">Type</label>
                <select id="cm-filter-type" name="listing_type" class="cm-select">
                    <option value="">All Types</option>
                    <option value="item">📦 Items</option>
                    <option value="service">🛠️ Services</option>
                </select>
            </div>

            <!-- Condition -->
            <div class="cm-filter-bar__group">
                <label for="cm-filter-condition" class="cm-filter-bar__label">Condition</label>
                <select id="cm-filter-condition" name="condition" class="cm-select">
                    <option value="">Any Condition</option>
                    <option value="new">New</option>
                    <option value="like_new">Like New</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                </select>
            </div>
        </div>

        <div class="cm-filter-bar__row">
            <!-- Price Range -->
            <div class="cm-filter-bar__group cm-filter-bar__group--price">
                <label class="cm-filter-bar__label">Price Range</label>
                <div class="cm-filter-bar__price-inputs">
                    <input type="number" name="min_price" class="cm-input cm-input--sm" placeholder="Min" min="0" step="1">
                    <span class="cm-filter-bar__separator">—</span>
                    <input type="number" name="max_price" class="cm-input cm-input--sm" placeholder="Max" min="0" step="1">
                </div>
            </div>

            <!-- Sort -->
            <div class="cm-filter-bar__group">
                <label for="cm-filter-sort" class="cm-filter-bar__label">Sort By</label>
                <select id="cm-filter-sort" name="sort" class="cm-select">
                    <option value="date">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="cm-filter-bar__group cm-filter-bar__group--actions">
                <button type="submit" class="cm-btn cm-btn--primary" id="cm-filter-apply">
                    🔍 Apply Filters
                </button>
                <button type="button" class="cm-btn cm-btn--ghost" id="cm-filter-reset">
                    Clear
                </button>
            </div>
        </div>
    </form>

    <div class="cm-filter-bar__results" id="cm-filter-results-count">
        <!-- Updated via AJAX -->
    </div>
</div>