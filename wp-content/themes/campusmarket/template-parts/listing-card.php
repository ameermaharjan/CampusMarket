<?php
/**
 * Listing Card Template Part — Premium Redesign
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$listing_id   = get_the_ID();
$price        = get_post_meta($listing_id, '_cm_price', true);
$price_type   = get_post_meta($listing_id, '_cm_price_type', true);
$condition    = get_post_meta($listing_id, '_cm_condition', true);
$location     = get_post_meta($listing_id, '_cm_location', true);
$author_id    = get_post_field('post_author', $listing_id);
$author       = get_userdata($author_id);
$listing_intent = get_post_meta($listing_id, '_cm_listing_intent', true);
$is_rental    = ($listing_intent === 'rent' || $price_type === 'per_day' || $price_type === 'per_week' || $price_type === 'per_hour');
$avg_rating   = cm_get_average_rating($listing_id);
?>

<a href="<?php the_permalink(); ?>" class="listing-card group bg-white rounded-xl overflow-hidden border border-slate-200 transition-all duration-500 hover:shadow-xl hover:border-primary/30 hover:-translate-y-2">
    <div class="relative h-48 overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="<?php echo esc_url(get_the_post_thumbnail_url($listing_id, 'cm-listing-card')); ?>" alt="<?php the_title_attribute(); ?>">
        <?php else : ?>
            <div class="w-full h-full bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
            </div>
        <?php endif; ?>
        
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            <span class="px-2 py-1 <?php echo $is_rental ? 'bg-blue-500' : 'bg-green-500'; ?> text-white text-[10px] font-bold uppercase rounded tracking-wider shadow-sm w-fit">
                <?php echo $is_rental ? 'For Rent' : 'For Sale'; ?>
            </span>
            <?php 
            $item_status = get_post_meta($listing_id, '_cm_item_status', true) ?: 'active';
            if ($item_status !== 'active') :
                $status_label = $item_status === 'sold' ? 'Sold' : 'Rented';
                if ($item_status === 'rented') {
                    $rent_until = get_post_meta($listing_id, '_cm_rented_until', true);
                    if ($rent_until) {
                        $status_label .= ' until ' . date('j M', strtotime($rent_until));
                    }
                }
                $status_color = $item_status === 'sold' ? 'bg-slate-900' : 'bg-amber-600';
            ?>
                <span class="px-2 py-1 <?php echo $status_color; ?> text-white text-[10px] font-bold uppercase rounded tracking-wider shadow-md w-fit animate-pulse">
                    <?php echo $status_label; ?>
                </span>
            <?php endif; ?>
            <?php if ($condition) : ?>
                <span class="px-2 py-1 bg-white/90 backdrop-blur-md text-slate-900 text-[10px] font-bold uppercase rounded tracking-wider shadow-sm w-fit">
                    <?php echo esc_html(cm_get_condition_label($condition)); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <button class="absolute top-3 right-3 p-1.5 bg-white/90 backdrop-blur-md rounded-full text-slate-400 hover:text-red-500 transition-all duration-300 shadow-sm hover:scale-110 active:scale-90" onclick="event.preventDefault();">
            <span class="material-symbols-outlined text-lg">favorite</span>
        </button>
    </div>

    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-bold text-slate-900 group-hover:text-primary transition-colors duration-300 truncate mr-2"><?php the_title(); ?></h3>
            <div class="text-right shrink-0">
                <span class="text-lg font-bold text-primary">Rs. <?php echo esc_html($price); ?></span>
                <?php if ($is_rental) : ?>
                    <span class="text-[10px] block text-slate-500 font-medium">per <?php echo $price_type === 'per_day' ? 'day' : 'week'; ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex items-center gap-2 mb-4">
            <div class="w-6 h-6 rounded-full overflow-hidden border border-slate-200 shadow-sm shrink-0">
                <?php echo get_avatar($author_id, 24, '', '', array('class' => 'w-full h-full object-cover')); ?>
            </div>
            <span class="text-xs text-slate-500 truncate max-w-[120px]"><?php echo esc_html($author ? $author->display_name : 'Student'); ?></span>
            <?php if ($avg_rating > 0) : ?>
                <span class="text-slate-300">|</span>
                <div class="flex items-center text-yellow-500">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="text-xs font-bold ml-1"><?php echo esc_html($avg_rating); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php 
        $btn_class = "w-full py-2.5 font-bold rounded-lg transition-all duration-300 flex items-center justify-center gap-2 text-sm";
        if ($item_status !== 'active') : ?>
            <span class="<?php echo $btn_class; ?> bg-slate-100 text-slate-400">
                <span class="material-symbols-outlined text-lg">info</span> View Details
            </span>
        <?php else : ?>
            <?php if ($is_rental) : ?>
                <span class="<?php echo $btn_class; ?> bg-slate-900 text-white group-hover:bg-primary active:scale-[0.98]">
                    <span class="material-symbols-outlined text-lg">calendar_today</span> Rent Now
                </span>
            <?php else : ?>
                <span class="<?php echo $btn_class; ?> bg-primary text-white shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-[0.98]">
                    <span class="material-symbols-outlined text-lg">shopping_cart</span> Buy Now
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</a>