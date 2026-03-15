<?php
/**
 * Front Page Template — Premium Landing Page
 *
 * @package CampusMarket
 */

get_header();
?>

<!-- Hero Section -->
<section class="relative px-6 py-20 lg:py-32 overflow-hidden mesh-gradient">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
        <div class="relative z-10 flex flex-col gap-8 opacity-0 animate-fade-slide-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider w-fit">
                <span class="material-symbols-outlined text-sm">verified_user</span>
                Exclusively for University Students
            </div>

            <h1 class="text-5xl lg:text-7xl font-black leading-[1.1] tracking-tight text-slate-900">
                The Marketplace <br><span class="text-primary">for Your Campus</span>
            </h1>

            <p class="text-lg lg:text-xl text-slate-600 leading-relaxed max-w-xl">
                The safe and easy way to rent, trade, or buy textbooks, electronics, and campus essentials from verified peers at your university.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="px-8 py-4 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:-translate-y-1 hover:shadow-2xl hover:shadow-primary/40 active:translate-y-0 transition-all duration-300 flex items-center gap-2">
                    Start Browsing <span class="material-symbols-outlined">trending_flat</span>
                </a>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="px-8 py-4 glass-card font-bold rounded-xl hover:bg-white transition-all duration-300 flex items-center gap-2 border-slate-200 !transform-none">
                        Sell an Item <span class="material-symbols-outlined">add_circle</span>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="px-8 py-4 glass-card font-bold rounded-xl hover:bg-white transition-all duration-300 flex items-center gap-2 border-slate-200 !transform-none">
                        Sign Up Free <span class="material-symbols-outlined">add_circle</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <?php
                // Count verified users
                $verified_users_query = new WP_User_Query(array(
                    'meta_key'   => '_cm_verification_status',
                    'meta_value' => 'verified',
                    'count_total' => true,
                ));
                $verified_count = $verified_users_query->get_total();
                
                // Count successful trades (completed or confirmed bookings)
                global $wpdb;
                $trade_count = (int) $wpdb->get_var("
                    SELECT COUNT(*) FROM {$wpdb->posts} p
                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'cm_booking'
                    AND pm.meta_key = '_cm_status'
                    AND pm.meta_value IN ('completed', 'confirmed')
                ");

                $user_count = count_users();
                $listing_count = wp_count_posts('cm_listing')->publish;
                ?>
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-primary/20 flex items-center justify-center text-primary text-xs font-bold hover:scale-110 hover:z-10 transition-transform duration-300">
                        <span class="material-symbols-outlined text-sm">verified</span>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-primary/30 flex items-center justify-center text-primary text-xs font-bold hover:scale-110 hover:z-10 transition-transform duration-300">
                        <span class="material-symbols-outlined text-sm">verified_user</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500">Joined by <?php echo esc_html($verified_count); ?>+ verified students</p>
            </div>
        </div>

        <!-- Hero Illustration -->
        <div class="relative opacity-0 animate-fade-slide-up stagger-2">
            <div class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full"></div>
            <div class="relative glass-card p-4 rounded-3xl shadow-2xl !transform-none hover:rotate-1 hover:scale-[1.03] transition-all duration-500">
                <?php
                $hero_listing = new WP_Query(array(
                    'post_type' => 'cm_listing',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        array('key' => '_cm_approval_status', 'value' => 'approved'),
                    ),
                    'orderby' => 'rand',
                ));
                if ($hero_listing->have_posts()) :
                    $hero_listing->the_post();
                    if (has_post_thumbnail()) :
                ?>
                    <img alt="<?php the_title_attribute(); ?>" class="rounded-2xl w-full h-auto object-cover" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>">
                <?php else : ?>
                    <div class="rounded-2xl w-full h-64 bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                        <span class="material-symbols-outlined text-6xl text-primary/30">storefront</span>
                    </div>
                <?php
                    endif;
                    wp_reset_postdata();
                else :
                ?>
                    <div class="rounded-2xl w-full h-64 bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                        <span class="material-symbols-outlined text-6xl text-primary/30">storefront</span>
                    </div>
                <?php endif; ?>

                <div class="absolute -bottom-6 -left-6 glass-card p-4 rounded-2xl shadow-xl border border-white/50 !transform-none hover:scale-110 duration-300">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500">Active Listings</p>
                            <p class="text-sm font-black text-slate-900"><?php echo esc_html($listing_count); ?> items available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="px-6 py-20 bg-slate-50/50">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-end justify-between mb-12 opacity-0 animate-fade-slide-up">
            <div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Featured Categories</h2>
                <p class="text-slate-500 font-medium">Everything you need for student life</p>
            </div>
            <a class="text-primary font-bold flex items-center gap-1 hover:underline transition-all duration-300 group" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">
                View All <span class="material-symbols-outlined transition-transform group-hover:translate-x-1">chevron_right</span>
            </a>
        </div>

        <?php
        $categories = get_terms(array(
            'taxonomy'   => 'listing_category',
            'hide_empty' => false,
            'number'     => 4,
        ));

        $category_icons = array(
            'books'       => 'book',
            'electronics' => 'devices',
            'tutoring'    => 'edit_note',
            'stationery'  => 'edit_note',
            'sports'      => 'sports_soccer',
            'music'       => 'music_note',
            'other'       => 'inventory_2',
        );

        $category_descriptions = array(
            'books'       => 'Save up to 70%',
            'electronics' => 'Tech for cheap',
            'stationery'  => 'Academic essentials',
            'tutoring'    => 'Learn & grow',
            'sports'      => 'Stay active',
            'music'       => 'Make music',
            'other'       => 'And more',
        );

        if (! is_wp_error($categories) && ! empty($categories)) :
        ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $delay = 1;
                foreach ($categories as $cat) :
                    $slug = strtolower($cat->slug);
                    $icon = isset($category_icons[$slug]) ? $category_icons[$slug] : 'inventory_2';
                    $desc = isset($category_descriptions[$slug]) ? $category_descriptions[$slug] : $cat->count . ' listings';
                ?>
                <a href="<?php echo esc_url(add_query_arg('category', $cat->term_id, get_post_type_archive_link('cm_listing'))); ?>" class="group relative aspect-square overflow-hidden rounded-3xl glass-card cursor-pointer opacity-0 animate-fade-slide-up stagger-<?php echo $delay; ?> bg-gradient-to-br from-primary/20 to-primary/5">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-slate-100 transition-all duration-700 group-hover:scale-110 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[80px] text-primary/20 group-hover:text-primary/30 transition-colors duration-500"><?php echo esc_html($icon); ?></span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-500"></div>
                    <div class="absolute bottom-6 left-6 transition-transform duration-500 group-hover:-translate-y-2">
                        <p class="text-xl font-bold text-white"><?php echo esc_html($cat->name); ?></p>
                        <p class="text-white/70 text-sm"><?php echo esc_html($desc); ?></p>
                    </div>
                </a>
                <?php
                    $delay = min($delay + 1, 4);
                endforeach;
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Latest Listings Section -->
<section class="px-6 py-20">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-end justify-between mb-12 opacity-0 animate-fade-slide-up">
            <div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Latest Listings</h2>
                <p class="text-slate-500 font-medium">Recently added items and services</p>
            </div>
            <a class="text-primary font-bold flex items-center gap-1 hover:underline transition-all duration-300 group" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">
                View All <span class="material-symbols-outlined transition-transform group-hover:translate-x-1">chevron_right</span>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $card_delay = 1;
                while ($featured->have_posts()) : $featured->the_post();
                ?>
                    <div class="opacity-0 animate-fade-slide-up stagger-<?php echo min($card_delay, 6); ?>">
                        <?php get_template_part('template-parts/listing-card'); ?>
                    </div>
                <?php
                    $card_delay++;
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <div class="text-center py-20">
                <span class="material-symbols-outlined text-6xl text-slate-300 mb-4 block">storefront</span>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No listings yet</h3>
                <p class="text-slate-500 mb-6">Be the first to list an item or service!</p>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="px-6 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all">List Your First Item</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="px-6 py-24">
    <div class="max-w-7xl mx-auto text-center mb-16 opacity-0 animate-fade-slide-up">
        <h2 class="text-4xl font-black text-slate-900 mb-4">How It Works</h2>
        <p class="text-slate-500 font-medium max-w-2xl mx-auto">Three simple steps to start trading within your campus community today.</p>
    </div>

    <div class="max-w-5xl mx-auto grid md:grid-cols-3 gap-12 relative">
        <div class="hidden md:block absolute top-1/3 left-[20%] right-[20%] h-0.5 border-t-2 border-dashed border-primary/30"></div>

        <div class="flex flex-col items-center gap-6 relative z-10 opacity-0 animate-fade-slide-up stagger-1">
            <div class="w-20 h-20 rounded-2xl bg-primary flex items-center justify-center text-white shadow-xl shadow-primary/30 transition-transform duration-500 hover:scale-110 hover:-rotate-3">
                <span class="material-symbols-outlined text-4xl">badge</span>
            </div>
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">1. Join with Student ID</h3>
                <p class="text-slate-500 leading-relaxed">Secure verification using your university email to ensure a safe community.</p>
            </div>
        </div>

        <div class="flex flex-col items-center gap-6 relative z-10 opacity-0 animate-fade-slide-up stagger-2">
            <div class="w-20 h-20 rounded-2xl bg-white glass-card flex items-center justify-center text-primary shadow-xl !transform-none hover:scale-110 duration-500">
                <span class="material-symbols-outlined text-4xl">inventory_2</span>
            </div>
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">2. List or Search</h3>
                <p class="text-slate-500 leading-relaxed">Snap a photo to list your items or browse deals from fellow students nearby.</p>
            </div>
        </div>

        <div class="flex flex-col items-center gap-6 relative z-10 opacity-0 animate-fade-slide-up stagger-3">
            <div class="w-20 h-20 rounded-2xl bg-white glass-card flex items-center justify-center text-primary shadow-xl !transform-none hover:scale-110 duration-500">
                <span class="material-symbols-outlined text-4xl">handshake</span>
            </div>
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">3. Meet &amp; Trade</h3>
                <p class="text-slate-500 leading-relaxed"><?php echo esc_html($trade_count); ?>+ successful campus deals completed at safe on-campus handover spots.</p>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Security Section -->
<section class="px-6 py-20">
    <div class="max-w-7xl mx-auto glass-card rounded-[3rem] p-10 lg:p-20 overflow-hidden relative !transform-none !hover:transform-none">
        <div class="absolute top-0 right-0 p-10 opacity-10">
            <span class="material-symbols-outlined text-[15rem] animate-float">security</span>
        </div>

        <div class="max-w-2xl relative z-10 opacity-0 animate-fade-slide-up">
            <h2 class="text-4xl font-black text-slate-900 mb-6">Trust &amp; Security First</h2>

            <div class="grid gap-8">
                <div class="flex gap-5 group cursor-default">
                    <div class="shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary transition-all duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined font-bold">verified</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-1 transition-colors duration-300 group-hover:text-primary">Peer Verification</h4>
                        <p class="text-slate-500">Every user is verified with their official university email and ID, eliminating anonymous scammers.</p>
                    </div>
                </div>

                <div class="flex gap-5 group cursor-default">
                    <div class="shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary transition-all duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined font-bold">location_on</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-1 transition-colors duration-300 group-hover:text-primary">Campus Safe Zones</h4>
                        <p class="text-slate-500">We suggest high-traffic, monitored campus locations for safe and convenient item handovers.</p>
                    </div>
                </div>

                <div class="flex gap-5 group cursor-default">
                    <div class="shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary transition-all duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined font-bold">rate_review</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-1 transition-colors duration-300 group-hover:text-primary">Student Ratings</h4>
                        <p class="text-slate-500">View transparency reports and ratings from other students to trade with total confidence.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="px-6 py-20 mb-10">
    <div class="max-w-7xl mx-auto text-center">
        <h2 class="text-4xl font-black text-slate-900 mb-4">Ready to Start?</h2>
        <p class="text-slate-500 max-w-xl mx-auto mb-8">Join your campus community and start sharing resources today.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="px-8 py-4 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:-translate-y-1 transition-all duration-300">List an Item</a>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="px-8 py-4 border-2 border-slate-200 font-bold rounded-xl hover:border-primary hover:text-primary transition-all duration-300">Browse Marketplace</a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/register/')); ?>" class="px-8 py-4 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:-translate-y-1 transition-all duration-300">Sign Up Free</a>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="px-8 py-4 border-2 border-slate-200 font-bold rounded-xl hover:border-primary hover:text-primary transition-all duration-300">Browse Marketplace</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
get_footer();
