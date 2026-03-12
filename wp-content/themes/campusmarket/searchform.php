<?php

/**
 * Custom search form
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<form role="search" method="get" class="cm-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text" for="cm-search-input"><?php esc_html_e('Search listings', 'campusmarket'); ?></label>
    <div class="cm-search-form__wrapper">
        <svg class="cm-search-form__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input
            type="search"
            id="cm-search-input"
            class="cm-search-form__input"
            placeholder="<?php esc_attr_e('Search for books, electronics, tutoring...', 'campusmarket'); ?>"
            value="<?php echo esc_attr(get_search_query()); ?>"
            name="s" />
        <input type="hidden" name="post_type" value="cm_listing" />
        <button type="submit" class="cm-search-form__btn cm-btn cm-btn--primary">
            <?php esc_html_e('Search', 'campusmarket'); ?>
        </button>
    </div>
</form>