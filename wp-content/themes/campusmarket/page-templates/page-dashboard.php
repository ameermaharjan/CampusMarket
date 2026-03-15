<?php
/**
 * Template Name: User Dashboard
 * Premium Dashboard Page
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

get_header();

global $wpdb;

$current_user        = wp_get_current_user();
$user_id             = $current_user->ID;
$is_verified         = cm_is_user_verified($user_id);
$verification_status = get_user_meta($user_id, '_cm_verification_status', true);
$member_since        = date('M Y', strtotime($current_user->user_registered));

// Active listings count (real)
$user_listings = new WP_Query(array(
    'post_type'      => 'cm_listing',
    'author'         => $user_id,
    'posts_per_page' => -1,
    'post_status'    => 'publish',
));
$total_listings = $user_listings->found_posts;
wp_reset_postdata();

// Items sold or rented: count distinct completed bookings involving user's listings
$items_traded = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
     WHERE p.post_type = 'cm_booking'
     AND p.post_status != 'trash'
     AND pm.meta_key = '_cm_seller_id'
     AND pm.meta_value = %s",
    $user_id
));

// Average rating from reviews on user's listings
$avg_rating_raw = $wpdb->get_var($wpdb->prepare(
    "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,1)))
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
     WHERE p.post_type = 'cm_review'
     AND p.post_status = 'publish'
     AND pm.meta_key = '_cm_rating'
     AND p.post_author != %d
     AND p.ID IN (
         SELECT pm2.post_id FROM {$wpdb->postmeta} pm2
         WHERE pm2.meta_key = '_cm_seller_id' AND pm2.meta_value = %s
     )",
    $user_id,
    $user_id
));
$rating = $avg_rating_raw ? number_format((float) $avg_rating_raw, 1) : '—';

// Active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'listings';
?>


<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(17, 82, 212, 0.1);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.6);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(17, 82, 212, 0.1);
    }
</style>

<div class="relative min-h-screen">
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/10 via-background-light to-background-light"></div>
    
    <main class="max-w-7xl mx-auto w-full px-6 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar -->
        <aside class="lg:col-span-3 space-y-6 opacity-0 animate-fade-slide-up">
            <div class="glass-panel p-6 rounded-xl text-center">
                <div class="relative inline-block mb-4">
                    <div class="h-28 w-28 rounded-full border-4 border-white shadow-xl overflow-hidden mx-auto bg-slate-200">
                        <?php                         $avatar_url = cm_get_user_avatar_url($user_id, 150);
                        ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <?php if ($is_verified) : ?>
                        <div class="absolute bottom-1 right-1 bg-primary text-white p-1.5 rounded-full border-2 border-white" title="Verified Student">
                            <span class="material-symbols-outlined text-xs block">verified</span>
                        </div>
                    <?php else: ?>
                        <div class="absolute bottom-1 right-1 bg-amber-500 text-white p-1.5 rounded-full border-2 border-white" title="Unverified Account">
                            <span class="material-symbols-outlined text-xs block">warning</span>
                        </div>
                    <?php endif; ?>
                </div>
                <h1 class="text-xl font-bold text-slate-900"><?php echo esc_html($current_user->display_name); ?></h1>
                <p class="text-slate-500 text-sm font-medium"><?php echo esc_html(get_user_meta($user_id, '_cm_department', true) ?: 'Student'); ?></p>
                <?php if ($is_verified) : ?>
                    <div class="mt-2 text-center w-full px-2.5 py-1.5 rounded-xl text-xs font-bold bg-primary/10 text-primary uppercase tracking-widest flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">verified</span> Verified Student
                    </div>
                <?php else: 
                    $ver_status = get_user_meta($user_id, '_cm_verification_status', true);
                    if ($ver_status === 'pending') :
                ?>
                    <a href="<?php echo esc_url(home_url('/edit-profile/')); ?>" class="mt-2 w-full text-center px-2.5 py-1.5 rounded-xl text-xs font-bold bg-amber-100/50 text-amber-700 hover:bg-amber-100 transition-colors uppercase tracking-widest flex items-center justify-center gap-1 border border-amber-200/50">
                        <span class="material-symbols-outlined text-[14px]">hourglass_empty</span> Pending Review
                    </a>
                <?php elseif ($ver_status === 'rejected') : ?>
                    <a href="<?php echo esc_url(home_url('/edit-profile/')); ?>" class="mt-2 w-full text-center px-2.5 py-1.5 rounded-xl text-xs font-bold bg-rose-100/50 text-rose-700 hover:bg-rose-100 transition-colors uppercase tracking-widest flex items-center justify-center gap-1 border border-rose-200/50">
                        <span class="material-symbols-outlined text-[14px]">error</span> Rejected - Update Docs
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/verify/')); ?>" class="mt-2 w-full text-center px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors uppercase tracking-widest flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">warning</span> Unverified Account
                    </a>
                <?php endif; endif; ?>
                <a href="<?php echo esc_url(home_url('/edit-profile/')); ?>" class="mt-6 w-full py-2.5 bg-primary text-white rounded-lg font-semibold text-sm hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    Edit Profile
                </a>
            </div>

            <nav class="glass-panel rounded-xl overflow-hidden">
                <a class="flex items-center gap-3 px-6 py-4 <?php echo $active_tab === 'listings' ? 'bg-primary/10 text-primary border-r-4 border-primary font-semibold' : 'text-slate-600 hover:bg-slate-50 transition-colors'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'listings')); ?>">
                    <span class="material-symbols-outlined">grid_view</span>
                    Dashboard
                </a>
                <a class="flex items-center gap-3 px-6 py-4 <?php echo $active_tab === 'requests' ? 'bg-primary/10 text-primary border-r-4 border-primary font-semibold' : 'text-slate-600 hover:bg-slate-50 transition-colors'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'requests')); ?>">
                    <span class="material-symbols-outlined">assignment_return</span>
                    Rental Requests
                </a>
                <a class="flex items-center gap-3 px-6 py-4 <?php echo $active_tab === 'bookings' ? 'bg-primary/10 text-primary border-r-4 border-primary font-semibold' : 'text-slate-600 hover:bg-slate-50 transition-colors'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'bookings')); ?>">
                    <span class="material-symbols-outlined">history</span>
                    Transactions
                </a>
            </nav>

            <div class="glass-panel p-6 rounded-xl">
                <h3 class="font-bold text-sm uppercase tracking-wider text-slate-400 mb-4">Account Stats</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-sm">Rating</span>
                        <div class="flex items-center text-primary font-bold">
                            <span class="material-symbols-outlined text-sm mr-1">star</span>
                            <?php echo esc_html($rating !== '—' ? $rating : 'No reviews yet'); ?>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-sm">Total Trades</span>
                        <span class="text-slate-900 font-bold"><?php echo esc_html($items_traded); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-sm">Active Listings</span>
                        <span class="text-slate-900 font-bold"><?php echo esc_html($total_listings); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if (current_user_can('manage_options')) : ?>
                <a href="<?php echo esc_url(home_url('/admin-panel/')); ?>" class="flex items-center justify-center gap-2 p-4 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-all">
                    <span class="material-symbols-outlined">admin_panel_settings</span> Admin Panel
                </a>
            <?php endif; ?>
        </aside>

        <!-- Main Content -->
        <section class="lg:col-span-9 space-y-8">
            <?php if (!$is_verified) : ?>
                <div class="glass-panel p-8 rounded-3xl border-l-8 <?php echo $verification_status === 'rejected' ? 'border-rose-500 bg-rose-500/5' : 'border-primary bg-primary/5'; ?> relative overflow-hidden group animate-fade-slide-up">
                    <div class="absolute -right-8 -top-8 size-48 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors"></div>
                    <div class="relative flex flex-col md:flex-row items-center gap-6">
                        <div class="size-16 rounded-2xl <?php echo $verification_status === 'rejected' ? 'bg-rose-100 text-rose-600' : 'bg-primary/10 text-primary'; ?> flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-3xl"><?php echo $verification_status === 'rejected' ? 'gpp_bad' : 'verified_user'; ?></span>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-xl font-bold text-slate-900"><?php echo $verification_status === 'rejected' ? 'Verification Rejected' : 'Verify Your Account'; ?></h3>
                            <p class="text-slate-600 text-sm mt-1">
                                <?php if ($verification_status === 'rejected') : ?>
                                    Your verification was rejected: <span class="font-semibold text-rose-600">"<?php echo esc_html(get_user_meta($user_id, '_cm_verification_remarks', true)); ?>"</span>. Please update your documents.
                                <?php else: ?>
                                    Get the verified badge to build trust and unlock full marketplace features.
                                <?php endif; ?>
                            </p>
                        </div>
                        <a href="<?php echo esc_url(home_url($verification_status === 'rejected' ? '/verify/' : '/verify/')); ?>" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg active:scale-95 flex items-center gap-2">
                            <?php echo $verification_status === 'rejected' ? 'Resubmit Documents' : 'Get Verified Now'; ?>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex flex-wrap gap-4 opacity-0 animate-fade-slide-up stagger-1">
                <div class="flex-1 min-w-[200px] glass-card p-6 rounded-xl">
                    <p class="text-slate-500 text-sm font-medium mb-1">Average Rating</p>
                    <?php $rating_display = ($rating !== '—') ? $rating : '—'; ?>
                    <h4 class="text-3xl font-bold text-slate-900"><?php echo esc_html($rating_display); ?><?php if ($rating !== '—') : ?><span class="text-lg text-slate-400 font-normal">/5.0</span><?php endif; ?></h4>
                    <?php if ($rating !== '—') : ?>
                    <div class="mt-2 w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-1000" style="width: <?php echo (floatval($rating) / 5) * 100; ?>%"></div>
                    </div>
                    <?php else : ?>
                    <p class="text-xs text-slate-400 mt-2">No reviews yet</p>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-[200px] glass-card p-6 rounded-xl">
                    <p class="text-slate-500 text-sm font-medium mb-1">Items Sold/Rented</p>
                    <h4 class="text-3xl font-bold text-slate-900"><?php echo esc_html($items_traded); ?></h4>
                    <p class="text-green-600 text-xs mt-2 flex items-center">
                        <span class="material-symbols-outlined text-xs mr-1">trending_up</span>
                        Active Student Member
                    </p>
                </div>
                <div class="flex-1 min-w-[200px] glass-card p-6 rounded-xl">
                    <p class="text-slate-500 text-sm font-medium mb-1">Member Since</p>
                    <h4 class="text-3xl font-bold text-slate-900"><?php echo date('Y', strtotime($current_user->user_registered)); ?></h4>
                    <p class="text-slate-400 text-xs mt-2"><?php echo esc_html($member_since); ?></p>
                </div>
            </div>

            <div class="glass-panel rounded-xl overflow-hidden opacity-0 animate-fade-slide-up stagger-2">
                <div class="border-b border-slate-200 px-6">
                    <div class="flex gap-8">
                        <a href="<?php echo esc_url(add_query_arg('tab', 'listings')); ?>" class="py-4 border-b-2 text-sm font-bold transition-all <?php echo $active_tab === 'listings' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'; ?>">Active Listings</a>
                        <a href="<?php echo esc_url(add_query_arg('tab', 'requests')); ?>" class="py-4 border-b-2 text-sm font-bold transition-all <?php echo $active_tab === 'requests' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'; ?>">Incoming Requests</a>
                        <a href="<?php echo esc_url(add_query_arg('tab', 'bookings')); ?>" class="py-4 border-b-2 text-sm font-bold transition-all <?php echo $active_tab === 'bookings' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'; ?>">My Bookings</a>
                    </div>
                </div>
                <div class="p-6">
                    <?php if ($active_tab === 'listings') : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php
                            $my_listings = new WP_Query(array(
                                'post_type'      => 'cm_listing',
                                'author'         => $user_id,
                                'posts_per_page' => 8,
                                'post_status'    => 'publish',
                            ));

                            if ($my_listings->have_posts()) :
                                while ($my_listings->have_posts()) : $my_listings->the_post();
                                    $l_id = get_the_ID();
                            ?>
                                <div class="group relative bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <div class="aspect-[4/3] bg-slate-100 relative overflow-hidden">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>">
                                        <?php endif; ?>
                                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-xs font-bold text-primary">
                                            Rs. <?php echo esc_html(get_post_meta($l_id, '_cm_price', true)); ?>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h5 class="font-bold text-slate-900 truncate"><?php the_title(); ?></h5>
                                        <p class="text-xs text-slate-500 mt-1"><?php echo esc_html(cm_get_intent_label($l_id)); ?> • <?php echo get_the_date('j M'); ?></p>
                                        <div class="mt-4 flex items-center justify-between">
                                            <?php 
                                            $item_status = get_post_meta($l_id, '_cm_item_status', true) ?: 'active';
                                            $status_label = 'Active';
                                            $status_class = 'bg-green-100 text-green-700';

                                            if ($item_status === 'sold') {
                                                $status_label = 'Sold';
                                                $status_class = 'bg-slate-100 text-slate-600';
                                            } elseif ($item_status === 'rented') {
                                                $rent_until = get_post_meta($l_id, '_cm_rented_until', true);
                                                $status_label = $rent_until ? 'Rented until ' . date('j M', strtotime($rent_until)) : 'Rented';
                                                $status_class = 'bg-amber-100 text-amber-700';
                                            }
                                            ?>
                                            <span class="text-[10px] px-2.5 py-1 <?php echo $status_class; ?> border border-current/20 rounded-full font-bold uppercase tracking-wide shadow-sm flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                <?php echo $status_label; ?>
                                            </span>
                                            <a href="<?php echo esc_url(add_query_arg('edit', $l_id, home_url('/list-item/'))); ?>" class="text-primary hover:underline text-xs font-bold">Edit Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                endwhile;
                                wp_reset_postdata();
                            endif;
                            ?>
                            <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center p-6 hover:border-primary/50 hover:bg-primary/5 transition-all cursor-pointer">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-3">
                                    <span class="material-symbols-outlined">add</span>
                                </div>
                                <p class="text-slate-900 font-bold text-sm">Post New Listing</p>
                                <p class="text-slate-500 text-xs mt-1 text-center">Reach verified students on campus</p>
                            </a>
                        </div>
                    <?php elseif ($active_tab === 'notifications') : ?>
                        <!-- Notifications Tab -->
                        <?php
                        $notif_args = array(
                            'post_type' => 'cm_notification',
                            'posts_per_page' => 20,
                            'meta_query' => array(
                                array('key' => '_cm_recipient_id', 'value' => $user_id)
                            ),
                            'orderby' => 'date',
                            'order' => 'DESC'
                        );
                        $notifications = new WP_Query($notif_args);
                        
                        if ($notifications->have_posts()) :
                            while ($notifications->have_posts()) : $notifications->the_post();
                                $n_id = get_the_ID();
                                $n_type = get_post_meta($n_id, '_cm_notification_type', true);
                                $n_status = get_post_meta($n_id, '_cm_notification_status', true);
                                $n_link = get_post_meta($n_id, '_cm_notification_link', true);
                                
                                $icon = 'notifications';
                                $color = 'text-primary bg-primary/10';
                                
                                if ($n_type === 'booking_request') { $icon = 'calendar_month'; $color = 'text-amber-500 bg-amber-50'; }
                                elseif ($n_type === 'booking_update') { $icon = 'update'; $color = 'text-blue-500 bg-blue-50'; }
                                elseif ($n_type === 'new_message') { $icon = 'chat_bubble'; $color = 'text-green-500 bg-green-50'; }
                                elseif ($n_type === 'listing_approval') { $icon = 'verified'; $color = 'text-emerald-500 bg-emerald-50'; }
                                elseif ($n_type === 'user_verified') { $icon = 'verified_user'; $color = 'text-green-600 bg-green-100'; }
                                elseif ($n_type === 'user_rejected') { $icon = 'gpp_bad'; $color = 'text-red-500 bg-red-50'; }
                        ?>
                            <a href="<?php echo $n_link ? esc_url($n_link) : '#'; ?>" class="block p-4 border border-slate-100 rounded-lg bg-slate-50/50 transition-all hover:bg-white hover:shadow-md mb-4 <?php echo $n_status === 'unread' ? 'border-primary/20 bg-primary/5' : ''; ?>">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 <?php echo $color; ?>">
                                        <span class="material-symbols-outlined"><?php echo $icon; ?></span>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex items-center justify-between pointer-events-none">
                                            <p class="font-bold text-slate-900 pr-8"><?php echo wp_kses_post(get_the_content()); ?></p>
                                            <div class="text-right whitespace-nowrap">
                                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                        ?>
                            <div class="text-center py-16">
                                <span class="material-symbols-outlined text-5xl text-slate-300 mb-4 block">notifications_off</span>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">You're all caught up!</h3>
                                <p class="text-slate-500">No recent notifications to display.</p>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <!-- Other tabs content (Requests/Bookings) -->
                        <?php 
                        $items = ($active_tab === 'requests') ? cm_get_rental_requests($user_id) : cm_get_user_bookings($user_id);
                        if ($items && $items->have_posts()) :
                            while ($items->have_posts()) : $items->the_post();
                                $b_id = get_the_ID();
                                $b_listing_id = get_post_meta($b_id, '_cm_listing_id', true);
                                $b_status = get_post_meta($b_id, '_cm_status', true);
                        ?>
                            <div class="p-4 border border-slate-100 rounded-lg bg-slate-50/50 transition-all hover:bg-white hover:shadow-md mb-4 flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-slate-200 overflow-hidden shrink-0">
                                        <?php if (has_post_thumbnail($b_listing_id)) : ?>
                                            <img class="w-full h-full object-cover" src="<?php echo esc_url(get_the_post_thumbnail_url($b_listing_id, 'thumbnail')); ?>" alt="">
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-bold"><?php echo esc_html(get_the_title($b_listing_id)); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo esc_html(get_the_date()); ?> • 
                                            <span class="font-semibold <?php echo ($active_tab === 'requests') ? 'text-primary' : 'text-slate-700'; ?>">
                                                <?php 
                                                if ($active_tab === 'requests') {
                                                    $renter_id = get_post_meta($b_id, '_cm_renter_id', true);
                                                    $renter = get_userdata($renter_id);
                                                    echo 'From: ' . ($renter ? esc_html($renter->display_name) : 'Student');
                                                } else {
                                                    echo 'Order placed';
                                                }
                                                ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right mr-2">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $b_status === 'confirmed' ? 'bg-green-100 text-green-700' : ($b_status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'); ?>">
                                            <?php echo esc_html($b_status ?: 'pending'); ?>
                                        </span>
                                        <?php if ($b_status === 'confirmed') : 
                                            $end_date = get_post_meta($b_id, '_cm_end_date', true);
                                            $display_end = date('j M', strtotime($end_date));
                                            if (strpos($end_date, ' ') !== false || strpos($end_date, 'T') !== false) {
                                                $display_end = date('j M, g:i a', strtotime($end_date));
                                            }
                                        ?>
                                            <p class="text-[10px] text-slate-400 mt-1">Ends: <?php echo $display_end; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($active_tab === 'requests') : ?>
                                        <?php if ($b_status === 'pending') : ?>
                                            <div class="flex gap-1">
                                                <button title="Approve" class="p-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all cm-booking-action" data-booking-id="<?php echo esc_attr($b_id); ?>" data-action="confirmed">
                                                    <span class="material-symbols-outlined text-sm">check</span>
                                                </button>
                                                <button title="Cancel" class="p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all cm-booking-action" data-booking-id="<?php echo esc_attr($b_id); ?>" data-action="cancelled">
                                                    <span class="material-symbols-outlined text-sm">close</span>
                                                </button>
                                            </div>
                                        <?php elseif ($b_status === 'confirmed' && get_post_meta($b_id, '_cm_return_notified', true)) : 
                                            // Only show confirm return for rentals
                                            $b_intent = get_post_meta($b_listing_id, '_cm_listing_intent', true);
                                            if ($b_intent === 'rent') :
                                        ?>
                                            <div class="flex flex-col gap-2">
                                                <button class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-all cm-booking-action" data-booking-id="<?php echo esc_attr($b_id); ?>" data-action="confirm_return">
                                                    Confirm Return
                                                </button>
                                                <button class="px-3 py-1.5 bg-red-100 text-red-600 text-[10px] font-bold rounded-lg hover:bg-red-200 transition-all cm-booking-action" data-booking-id="<?php echo esc_attr($b_id); ?>" data-action="reject_return">
                                                    Not Returned?
                                                </button>
                                            </div>
                                        <?php endif; endif; ?>
                                    <?php else : ?>
                                        <?php 
                                        $b_intent = get_post_meta($b_listing_id, '_cm_listing_intent', true);
                                        if ($b_status === 'confirmed' && $b_intent === 'rent' && !get_post_meta($b_id, '_cm_return_notified', true)) : ?>
                                            <button class="px-3 py-1.5 bg-amber-500 text-white text-xs font-bold rounded-lg hover:bg-amber-600 transition-all cm-booking-action" data-booking-id="<?php echo esc_attr($b_id); ?>" data-action="notify_returned">
                                                Notify Returned
                                            </button>
                                        <?php elseif ($b_status === 'confirmed' && $b_intent === 'rent' && get_post_meta($b_id, '_cm_return_notified', true) && !get_post_meta($b_id, '_cm_return_confirmed', true)) : ?>
                                            <span class="text-[10px] font-bold text-amber-600 uppercase">Return notified...</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                        ?>
                            <div class="text-center py-16">
                                <span class="material-symbols-outlined text-5xl text-slate-300 mb-4 block">fact_check</span>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">No items found</h3>
                                <p class="text-slate-500">You don't have any <?php echo $active_tab; ?> yet.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="glass-panel p-6 rounded-xl animate-fade-slide-up stagger-3">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Recent Activity
                    </h3>
                    <button class="text-xs font-bold text-primary hover:underline">View All</button>
                </div>
                <div class="space-y-6">
                    <?php
                    // Fetch real activity or show placeholders if none
                    $args = array(
                        'post_type' => array('cm_booking', 'cm_activity_log'),
                        'posts_per_page' => 5,
                        'meta_query' => array(
                            'relation' => 'OR',
                            // For Bookings
                            array('key' => '_cm_renter_id', 'value' => $user_id),
                            array('key' => '_cm_owner_id', 'value' => $user_id),
                            // For Activity Logs (post_author works too, but we will rely on standard WP_Query author params below)
                        )
                    );
                    
                    // We need a custom query to get bookings (where user is owner/renter) OR activities (where user is author)
                    // The simplest approach is to use two queries and merge them, or write a custom WHERE filter.
                    // For performance, getting them separately and merging/sorting is safer.
                    
                    $bookings_args = array(
                        'post_type' => 'cm_booking',
                        'posts_per_page' => 5,
                        'meta_query' => array(
                            'relation' => 'OR',
                            array('key' => '_cm_renter_id', 'value' => $user_id),
                            array('key' => '_cm_owner_id', 'value' => $user_id)
                        )
                    );
                    $bookings_q = new WP_Query($bookings_args);
                    
                    $logs_args = array(
                        'post_type' => 'cm_activity_log',
                        'author' => $user_id,
                        'posts_per_page' => 5,
                    );
                    $logs_q = new WP_Query($logs_args);
                    
                    // Merge and sort
                    $all_activities = array_merge($bookings_q->posts, $logs_q->posts);
                    usort($all_activities, function($a, $b) {
                        return strtotime($b->post_date) - strtotime($a->post_date);
                    });
                    
                    // Limit to 5
                    $all_activities = array_slice($all_activities, 0, 5);

                    if (!empty($all_activities)) :
                        foreach ($all_activities as $act) : setup_postdata($act);
                            $act_id = $act->ID;
                            
                            if ($act->post_type === 'cm_booking') {
                                $act_listing_id = get_post_meta($act_id, '_cm_listing_id', true);
                                $act_owner_id = get_post_meta($act_id, '_cm_owner_id', true);
                                $is_owner = ($act_owner_id == $user_id);
                                ?>
                                <div class="flex gap-4">
                                    <div class="h-10 w-10 rounded-lg <?php echo $is_owner ? 'bg-primary/10 text-primary' : 'bg-blue-50 text-blue-600'; ?> flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined"><?php echo $is_owner ? 'payments' : 'shopping_bag'; ?></span>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="text-sm font-medium text-slate-900">
                                            <?php echo $is_owner ? 'Rental Payment for' : 'Ordered'; ?> 
                                            <span class="font-bold"><?php echo esc_html(get_the_title($act_listing_id)); ?></span>
                                        </p>
                                        <p class="text-xs text-slate-500"><?php echo human_time_diff(strtotime($act->post_date), current_time('timestamp')); ?> ago</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-slate-900">Rs. <?php echo esc_html(get_post_meta($act_listing_id, '_cm_price', true)); ?></span>
                                        <p class="text-[10px] <?php echo $is_owner ? 'text-green-600' : 'text-primary'; ?> font-bold uppercase tracking-tight"><?php echo esc_html(get_post_meta($act_id, '_cm_status', true)); ?></p>
                                    </div>
                                </div>
                                <?php
                            } else {
                                // cm_activity_log
                                $type = get_post_meta($act_id, '_cm_activity_type', true);
                                $desc = get_post_meta($act_id, '_cm_activity_desc', true);
                                
                                $icon = 'info';
                                $bg_class = 'bg-slate-100 text-slate-600';
                                
                                if ($type === 'login') {
                                    $icon = 'login';
                                    $bg_class = 'bg-emerald-50 text-emerald-600';
                                } elseif ($type === 'logout') {
                                    $icon = 'logout';
                                    $bg_class = 'bg-amber-50 text-amber-600';
                                } elseif ($type === 'password_change') {
                                    $icon = 'key';
                                    $bg_class = 'bg-purple-50 text-purple-600';
                                } elseif ($type === 'failed_login') {
                                    $icon = 'warning';
                                    $bg_class = 'bg-red-50 text-red-600';
                                }
                                ?>
                                <div class="flex gap-4">
                                    <div class="h-10 w-10 rounded-lg <?php echo $bg_class; ?> flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined"><?php echo $icon; ?></span>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="text-sm font-medium text-slate-900">
                                            Account Security: <span class="font-bold capitalize"><?php echo esc_html(str_replace('_', ' ', $type)); ?></span>
                                        </p>
                                        <p class="text-xs text-slate-500"><?php echo esc_html($desc); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight"><?php echo human_time_diff(strtotime($act->post_date), current_time('timestamp')); ?> ago</p>
                                    </div>
                                </div>
                                <?php
                            }
                        endforeach;
                        wp_reset_postdata();
                    else :
                    ?>
                        <div class="flex gap-4 opacity-60">
                            <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined">notifications</span>
                            </div>
                            <div class="flex-grow">
                                <p class="text-sm text-slate-500 italic">No recent activity to show.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>
<?php get_footer(); ?>

