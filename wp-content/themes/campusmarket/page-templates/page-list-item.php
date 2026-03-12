<?php

/**
 * Template Name: List an Item
 *
 * Frontend form for students to list items/services.
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(wp_login_url(home_url('/list-item/')));
    exit;
}

get_header();

$categories = get_terms(array(
    'taxonomy'   => 'listing_category',
    'hide_empty' => false,
));
?>

<div class="cm-section">
    <div class="cm-container cm-container--narrow">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title">List an Item or Service</h1>
            <p class="cm-page-header__subtitle">Fill out the form below to create your listing. It will be reviewed by an admin before going live.</p>
        </header>

        <div class="cm-form-card">
            <form id="cm-listing-form" class="cm-form" enctype="multipart/form-data">
                <!-- Listing Type Toggle -->
                <div class="cm-form-group">
                    <label class="cm-form-label">What are you listing?</label>
                    <div class="cm-toggle-group">
                        <label class="cm-toggle">
                            <input type="radio" name="listing_type" value="item" checked>
                            <span class="cm-toggle__label">📦 Item for Rent</span>
                        </label>
                        <label class="cm-toggle">
                            <input type="radio" name="listing_type" value="service">
                            <span class="cm-toggle__label">🛠️ Service</span>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div class="cm-form-group">
                    <label for="cm-listing-title" class="cm-form-label">Title <span class="cm-required">*</span></label>
                    <input type="text" id="cm-listing-title" name="title" class="cm-input" placeholder="e.g., Physics Textbook (Halliday 10th Edition)" required maxlength="100">
                    <span class="cm-form-hint">Keep it clear and descriptive</span>
                </div>

                <!-- Description -->
                <div class="cm-form-group">
                    <label for="cm-listing-desc" class="cm-form-label">Description <span class="cm-required">*</span></label>
                    <textarea id="cm-listing-desc" name="description" class="cm-textarea" rows="6" placeholder="Describe your item or service in detail..." required></textarea>
                </div>

                <!-- Category -->
                <div class="cm-form-group">
                    <label for="cm-listing-category" class="cm-form-label">Category <span class="cm-required">*</span></label>
                    <select id="cm-listing-category" name="category" class="cm-select" required>
                        <option value="">Select a category</option>
                        <?php if (! is_wp_error($categories)) : ?>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Price Row -->
                <div class="cm-form-row">
                    <div class="cm-form-group">
                        <label for="cm-listing-price" class="cm-form-label">Price (Rs.)</label>
                        <input type="number" id="cm-listing-price" name="price" class="cm-input" placeholder="0.00" min="0" step="0.01">
                        <span class="cm-form-hint">Leave empty or 0 for free</span>
                    </div>
                    <div class="cm-form-group">
                        <label for="cm-listing-price-type" class="cm-form-label">Price Type</label>
                        <select id="cm-listing-price-type" name="price_type" class="cm-select">
                            <option value="per_day">Per Day</option>
                            <option value="per_hour">Per Hour</option>
                            <option value="fixed">Fixed Price</option>
                        </select>
                    </div>
                </div>

                <!-- Condition (items only) -->
                <div class="cm-form-group" id="cm-condition-group">
                    <label for="cm-listing-condition" class="cm-form-label">Condition</label>
                    <select id="cm-listing-condition" name="condition" class="cm-select">
                        <option value="new">New</option>
                        <option value="like_new">Like New</option>
                        <option value="good" selected>Good</option>
                        <option value="fair">Fair</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="cm-form-group">
                    <label for="cm-listing-location" class="cm-form-label">Pickup Location</label>
                    <input type="text" id="cm-listing-location" name="location" class="cm-input" placeholder="e.g., Library, Block A, Canteen">
                </div>

                <!-- Availability -->
                <div class="cm-form-row">
                    <div class="cm-form-group">
                        <label for="cm-listing-avail-start" class="cm-form-label">Available From</label>
                        <input type="date" id="cm-listing-avail-start" name="availability_start" class="cm-input" min="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    <div class="cm-form-group">
                        <label for="cm-listing-avail-end" class="cm-form-label">Available Until</label>
                        <input type="date" id="cm-listing-avail-end" name="availability_end" class="cm-input">
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="cm-form-group">
                    <label for="cm-listing-image" class="cm-form-label">Photo</label>
                    <div class="cm-upload-area" id="cm-upload-area">
                        <input type="file" id="cm-listing-image" name="listing_image" accept="image/*" class="cm-upload-area__input">
                        <div class="cm-upload-area__content" id="cm-upload-content">
                            <span class="cm-upload-area__icon">📷</span>
                            <p>Click or drag to upload an image</p>
                            <span class="cm-form-hint">JPG, PNG, GIF — Max 2MB</span>
                        </div>
                        <img id="cm-image-preview" class="cm-upload-area__preview" style="display:none;" alt="Preview">
                    </div>
                </div>

                <!-- Submit -->
                <div class="cm-form-group cm-form-group--submit">
                    <button type="submit" class="cm-btn cm-btn--primary cm-btn--lg cm-btn--block" id="cm-listing-submit">
                        Submit Listing for Review
                    </button>
                    <p class="cm-form-hint cm-text-center">Your listing will be reviewed by an admin before being published.</p>
                </div>

                <div class="cm-form-message" id="cm-listing-message"></div>
            </form>
        </div>
    </div>
</div>

<?php
get_footer();
