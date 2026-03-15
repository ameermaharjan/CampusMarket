<?php
/**
 * Author Profile Template — Public User Page
 *
 * @package CampusMarket
 */

get_header();

$author_id = get_queried_object_id();
$author = get_userdata($author_id);

if (!$author) {
    echo '<div class="max-w-7xl mx-auto px-4 py-20 text-center"><h2 class="text-2xl font-bold">User not found.</h2></div>';
    get_footer();
    exit;
}

$is_verified = cm_is_user_verified($author_id);
$member_since = date('F Y', strtotime($author->user_registered));
$department = get_user_meta($author_id, '_cm_department', true) ?: 'Student Member';

// Real Listing Count
$listings_query = new WP_Query(array(
    'post_type'      => 'cm_listing',
    'author'         => $author_id,
    'post_status'    => 'publish',
    'posts_per_page' => -1
));
$total_listings = $listings_query->found_posts;

// Trade Count (Completed/Confirmed Bookings)
global $wpdb;
$trades_count = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
     WHERE p.post_type = 'cm_booking'
     AND p.post_status != 'trash'
     AND pm.meta_key = '_cm_status'
     AND pm.meta_value IN ('completed', 'confirmed')
     AND p.ID IN (
         SELECT post_id FROM {$wpdb->postmeta} 
         WHERE (meta_key = '_cm_owner_id' AND meta_value = %s)
         OR (meta_key = '_cm_renter_id' AND meta_value = %s)
     )",
    $author_id, $author_id
));

// Average Rating
$avg_rating_raw = $wpdb->get_var($wpdb->prepare(
    "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,1)))
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
     WHERE p.post_type = 'cm_review'
     AND p.post_status = 'publish'
     AND pm.meta_key = '_cm_review_rating'
     AND p.ID IN (
         SELECT pm2.post_id FROM {$wpdb->postmeta} pm2
         WHERE pm2.meta_key = '_cm_target_user_id' AND pm2.meta_value = %s
     )",
    $author_id
));
$avg_rating = ($avg_rating_raw !== null) ? number_format((float) $avg_rating_raw, 1) : null;
?>

<div class="relative min-h-screen pb-20">
    <!-- Background Accents -->
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/5 via-background-light to-background-light"></div>
    
    <!-- Hero Profile Section -->
    <div class="w-full bg-white border-b border-slate-200 pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-4 md:px-10">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-10">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="size-40 md:size-48 rounded-3xl overflow-hidden ring-8 ring-slate-50 shadow-2xl transition-transform duration-500 group-hover:scale-[1.02]">
                        <?php echo get_avatar($author_id, 200, '', '', array('class' => 'w-full h-full object-cover')); ?>
                    </div>
                    <?php if ($is_verified) : ?>
                        <div class="absolute -bottom-3 -right-3 bg-primary text-white p-3 rounded-2xl shadow-xl border-4 border-white animate-bounce-subtle" title="Verified ID">
                            <span class="material-symbols-outlined text-2xl block" style="font-variation-settings: 'FILL' 1;">verified</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Basic Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-4">
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight"><?php echo esc_html($author->display_name); ?></h1>
                        <?php if ($is_verified) : ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest rounded-full border border-primary/20">
                                <span class="material-symbols-outlined text-xs">check_circle</span> Verified Student
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-wrap justify-center md:justify-start gap-6 text-slate-500 font-medium">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary/60">school</span>
                            <?php echo esc_html($department); ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary/60">calendar_month</span>
                            Member since <?php echo esc_html($member_since); ?>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap justify-center md:justify-start gap-4">
                        <?php if (is_user_logged_in() && get_current_user_id() !== $author_id) : ?>
                            <a href="<?php echo esc_url(home_url('/chat/?with=' . $author_id)); ?>" class="px-8 py-3.5 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all flex items-center gap-2 active:scale-95">
                                <span class="material-symbols-outlined">chat_bubble</span>
                                MESSAGE STUDENT
                            </a>
                            <button class="cm-open-report-modal px-8 py-3.5 bg-rose-50 text-rose-600 rounded-2xl font-black text-sm border border-rose-100 hover:bg-rose-100 transition-all active:scale-95 flex items-center gap-2" data-user-id="<?php echo esc_attr($author_id); ?>" data-user-name="<?php echo esc_attr($author->display_name); ?>">
                                <span class="material-symbols-outlined text-lg">flag</span>
                                REPORT
                            </button>
                        <?php elseif (!is_user_logged_in()) : ?>
                            <button onclick="window.cmToast('Please login to contact students', 'info')" class="px-8 py-3.5 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all flex items-center gap-2 active:scale-95">
                                <span class="material-symbols-outlined">chat_bubble</span>
                                MESSAGE STUDENT
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-1 gap-4 w-full md:w-auto">
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 text-center md:text-left min-w-[160px]">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Trades</p>
                        <h4 class="text-3xl font-black text-slate-900"><?php echo $trades_count; ?></h4>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 text-center md:text-left min-w-[160px]">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Avg Rating</p>
                        <div class="flex items-center justify-center md:justify-start gap-1">
                            <h4 class="text-3xl font-black text-slate-900"><?php echo $avg_rating ?: '—'; ?></h4>
                            <?php if ($avg_rating) : ?>
                                <span class="material-symbols-outlined text-amber-400 font-variation-star" style="font-variation-settings: 'FILL' 1;">star</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Listings Grid -->
    <div class="max-w-7xl mx-auto px-4 md:px-10 mt-20">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Active Listings</h2>
                <p class="text-slate-500 font-medium">Items currently available for rent or purchase</p>
            </div>
            <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 shadow-sm">
                <?php echo $total_listings; ?> Listings
            </div>
        </div>

        <?php if ($listings_query->have_posts()) : ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php while ($listings_query->have_posts()) : $listings_query->the_post(); ?>
                    <div class="opacity-0 animate-fade-slide-up stagger">
                        <?php get_template_part('template-parts/listing-card'); ?>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <div class="glass-panel rounded-[3rem] p-20 text-center">
                <div class="size-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 border border-slate-100">
                    <span class="material-symbols-outlined text-5xl text-slate-200">grid_off</span>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-4">No Active Listings</h3>
                <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">
                    This user hasn't posted any items yet, or they've all been sold/rented out.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
