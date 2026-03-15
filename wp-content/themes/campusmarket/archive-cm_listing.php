<?php
/**
 * Archive/Browse Listings — Premium Marketplace Page
 *
 * @package CampusMarket
 */

get_header();

// Get filter values
$current_category  = isset($_GET['category']) ? absint($_GET['category']) : 0;
$current_intent    = isset($_GET['listing_intent']) ? sanitize_text_field($_GET['listing_intent']) : (isset($_GET['listing_type']) ? sanitize_text_field($_GET['listing_type']) : '');
$current_condition = isset($_GET['condition']) ? sanitize_text_field($_GET['condition']) : '';
$current_sort      = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
$paged             = get_query_var('paged') ? get_query_var('paged') : 1;

// Build query
$args = array(
    'post_type'      => 'cm_listing',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'meta_query'     => array(
        'relation' => 'AND',
        array('key' => '_cm_approval_status', 'value' => 'approved'),
    ),
);

if ($current_category) {
    $args['tax_query'] = array(
        array('taxonomy' => 'listing_category', 'field' => 'term_id', 'terms' => $current_category),
    );
}

if ($current_intent === 'rent' || $current_intent === 'rental') {
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array('key' => '_cm_listing_intent', 'value' => 'rent'),
        array('key' => '_cm_price_type', 'value' => array('per_day', 'per_week', 'per_hour'), 'compare' => 'IN'),
    );
} elseif ($current_intent === 'sale') {
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array('key' => '_cm_listing_intent', 'value' => 'sale'),
        array('key' => '_cm_price_type', 'value' => 'fixed'),
    );
}

if ($current_condition) {
    $args['meta_query'][] = array(
        'key'   => '_cm_condition',
        'value' => $current_condition,
    );
}

switch ($current_sort) {
    case 'price_low':
        $args['meta_key'] = '_cm_price';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'ASC';
        break;
    case 'price_high':
        $args['meta_key'] = '_cm_price';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    default:
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
}

$listings = new WP_Query($args);
$categories = get_terms(array('taxonomy' => 'listing_category', 'hide_empty' => false));
$category_icons = array(
    'books' => 'book', 'electronics' => 'devices', 'tutoring' => 'edit_note',
    'stationery' => 'edit_note', 'sports' => 'sports_soccer', 'music' => 'music_note', 'other' => 'inventory_2',
);
?>

<div class="max-w-7xl mx-auto px-4 md:px-10 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-64 shrink-0 space-y-8 opacity-0 animate-fade-slide-up">

            <!-- Transaction Intent -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Transaction Type</h3>
                <div class="space-y-2">
                    <a href="<?php echo esc_url(add_query_arg('listing_intent', 'sale', remove_query_arg(array('paged', 'listing_type')))); ?>" class="flex items-center gap-3 cursor-pointer group px-3 py-2 rounded-lg <?php echo $current_intent === 'sale' ? 'bg-primary/10 text-primary' : 'hover:bg-slate-100'; ?> transition-all duration-300">
                        <span class="material-symbols-outlined text-lg">shopping_basket</span>
                        <span class="text-sm font-medium group-hover:text-primary transition-colors duration-300">For Sale</span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('listing_intent', 'rent', remove_query_arg(array('paged', 'listing_type')))); ?>" class="flex items-center gap-3 cursor-pointer group px-3 py-2 rounded-lg <?php echo ($current_intent === 'rent' || $current_intent === 'rental') ? 'bg-primary/10 text-primary' : 'hover:bg-slate-100'; ?> transition-all duration-300">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        <span class="text-sm font-medium group-hover:text-primary transition-colors duration-300">For Rent</span>
                    </a>
                    <?php if ($current_intent) : ?>
                        <a href="<?php echo esc_url(remove_query_arg(array('listing_intent', 'listing_type', 'paged'))); ?>" class="flex items-center gap-3 cursor-pointer group px-3 py-2 rounded-lg hover:bg-slate-100 transition-all duration-300 text-slate-400">
                            <span class="material-symbols-outlined text-lg">close</span>
                            <span class="text-sm font-medium">Clear Filter</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Categories</h3>
                <div class="space-y-1">
                    <?php if (!is_wp_error($categories)) : foreach ($categories as $cat) :
                        $slug = strtolower($cat->slug);
                        $icon = isset($category_icons[$slug]) ? $category_icons[$slug] : 'inventory_2';
                        $is_active = ($current_category == $cat->term_id);
                    ?>
                        <a href="<?php echo esc_url($is_active ? remove_query_arg(array('category', 'paged')) : add_query_arg('category', $cat->term_id, remove_query_arg('paged'))); ?>"
                           class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-300 transform active:scale-95 <?php echo $is_active ? 'bg-primary/10 text-primary font-semibold' : 'hover:bg-slate-100'; ?>">
                            <span class="material-symbols-outlined text-lg"><?php echo esc_html($icon); ?></span>
                            <?php echo esc_html($cat->name); ?>
                            <span class="ml-auto text-xs text-slate-400"><?php echo esc_html($cat->count); ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Condition Filter -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Condition</h3>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $conditions = array('new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair');
                    foreach ($conditions as $key => $label) :
                        $is_active = ($current_condition === $key);
                        $url = $is_active ? remove_query_arg(array('condition', 'paged')) : add_query_arg('condition', $key, remove_query_arg('paged'));
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="px-3 py-1.5 rounded-full border text-xs font-medium transition-all duration-300 transform active:scale-95 <?php echo $is_active ? 'bg-primary border-primary text-white shadow-md shadow-primary/20' : 'border-slate-200 text-slate-600 hover:border-primary hover:text-primary'; ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">

            <!-- Header + Sort -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 opacity-0 animate-fade-slide-up">
                <div>
                    <h1 class="text-2xl font-bold">
                        <?php
                        if ($current_category && !is_wp_error(get_term($current_category))) {
                            echo esc_html(get_term($current_category)->name);
                        } else {
                            echo 'Featured Items';
                        }
                        ?>
                    </h1>
                    <p class="text-sm text-slate-500">Showing <?php echo esc_html($listings->found_posts); ?> items<?php echo $current_intent ? ' (' . esc_html($current_intent) . ')' : ''; ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <form method="get" class="relative">
                        <?php if ($current_category) : ?><input type="hidden" name="category" value="<?php echo esc_attr($current_category); ?>"><?php endif; ?>
                        <?php if ($current_intent) : ?><input type="hidden" name="listing_intent" value="<?php echo esc_attr($current_intent); ?>"><?php endif; ?>
                        <?php if ($current_condition) : ?><input type="hidden" name="condition" value="<?php echo esc_attr($current_condition); ?>"><?php endif; ?>
                        <select name="sort" onchange="this.form.submit()" class="appearance-none bg-white border-none rounded-lg py-2 pl-4 pr-10 text-sm font-medium focus:ring-primary/50 shadow-sm cursor-pointer transition-all duration-300 hover:bg-slate-50">
                            <option value="newest" <?php selected($current_sort, 'newest'); ?>>Newest First</option>
                            <option value="price_low" <?php selected($current_sort, 'price_low'); ?>>Price: Low to High</option>
                            <option value="price_high" <?php selected($current_sort, 'price_high'); ?>>Price: High to Low</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
                    </form>
                </div>
            </div>

            <!-- Listing Grid -->
            <?php if ($listings->have_posts()) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php
                    $card_index = 1;
                    while ($listings->have_posts()) : $listings->the_post();
                        $listing_id   = get_the_ID();
                        $price        = get_post_meta($listing_id, '_cm_price', true);
                        $price_type   = get_post_meta($listing_id, '_cm_price_type', true);
                        $condition    = get_post_meta($listing_id, '_cm_condition', true);
                        $listing_type = get_post_meta($listing_id, '_cm_listing_type', true) ?: 'item';
                        $avg_rating   = cm_get_average_rating($listing_id);
                        $author_id    = get_post_field('post_author', $listing_id);
                        $author       = get_userdata($author_id);
                        $listing_intent = get_post_meta($listing_id, '_cm_listing_intent', true);
                        $is_rental    = ($listing_intent === 'rent' || $price_type === 'per_day' || $price_type === 'per_week' || $price_type === 'per_hour');
                    ?>
                    <div class="opacity-0 animate-fade-slide-up stagger-<?php echo min($card_index, 6); ?>">
                        <?php get_template_part('template-parts/listing-card'); ?>
                    </div>
                    <?php
                        $card_index++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

                <!-- Pagination -->
                <?php if ($listings->max_num_pages > 1) : ?>
                    <div class="mt-12 flex items-center justify-center gap-2 opacity-0 animate-fade-slide-up stagger-6">
                        <?php if ($paged > 1) : ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>" class="h-10 w-10 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-100 transition-all duration-300 hover:scale-105 active:scale-95">
                                <span class="material-symbols-outlined">chevron_left</span>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= min($listings->max_num_pages, 5); $i++) : ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $i)); ?>" class="h-10 w-10 flex items-center justify-center rounded-lg font-bold transition-all duration-300 hover:scale-105 active:scale-95 <?php echo $i === $paged ? 'bg-primary text-white' : 'border border-slate-200 hover:bg-slate-100'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($listings->max_num_pages > 5) : ?>
                            <span class="px-2 text-slate-400">...</span>
                            <a href="<?php echo esc_url(add_query_arg('paged', $listings->max_num_pages)); ?>" class="h-10 w-10 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-100 transition-all duration-300 hover:scale-105 active:scale-95">
                                <?php echo $listings->max_num_pages; ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($paged < $listings->max_num_pages) : ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $paged + 1)); ?>" class="h-10 w-10 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-100 transition-all duration-300 hover:scale-105 active:scale-95">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="text-center py-20">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4 block">search_off</span>
                    <h3 class="text-xl font-bold text-slate-700 mb-2">No listings found</h3>
                    <p class="text-slate-500 mb-6">Try adjusting your filters or search terms.</p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="px-6 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all">View All Listings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
