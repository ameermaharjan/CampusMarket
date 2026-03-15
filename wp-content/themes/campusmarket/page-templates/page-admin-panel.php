<?php
/**
 * Template Name: Admin Panel
 * Premium Admin Dashboard
 *
 * @package CampusMarket
 */

if (! is_user_logged_in() || ! current_user_can('manage_options')) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();

global $wpdb;

// ─── Core Counts ──────────────────────────────────────────
$total_users      = count_users();
$total_listings   = wp_count_posts('cm_listing');
$published_listings = (int) ($total_listings->publish ?? 0);
$pending_listings   = (int) ($total_listings->pending ?? 0);

// All bookings across all statuses
$total_bookings_count = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'cm_booking' AND post_status != 'trash'"
);

// Verified users count
$verified_users_count = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_cm_verified' AND meta_value = '1'"
);

// ─── Pending Approvals (listings) ────────────────────────
$pending_approval = new WP_Query(array(
    'post_type'      => 'cm_listing',
    'posts_per_page' => 10,
    'meta_query'     => array(
        array('key' => '_cm_approval_status', 'value' => 'pending'),
    ),
));

// ─── Recent Listings ─────────────────────────────────────
$recent_listings = new WP_Query(array(
    'post_type'      => 'cm_listing',
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

// ─── User Growth (last 7 days, real signups) ─────────────
$user_growth = array();
for ($i = 6; $i >= 0; $i--) {
    $date      = date('Y-m-d', strtotime("-{$i} days"));
    $day_label = date('D', strtotime("-{$i} days"));
    $count     = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->users} WHERE DATE(user_registered) = %s",
        $date
    ));
    $user_growth[] = array('label' => $day_label, 'count' => $count);
}
$max_growth = max(1, max(array_column($user_growth, 'count')));

// ─── Booking Volume (last 6 weeks) ────────────────────────
$booking_weeks = array();
for ($i = 5; $i >= 0; $i--) {
    $week_start = date('Y-m-d', strtotime("-{$i} weeks monday this week"));
    $week_end   = date('Y-m-d', strtotime("-{$i} weeks sunday this week"));
    $label      = 'W' . date('W', strtotime("-{$i} weeks"));
    $count      = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'cm_booking'
         AND post_status != 'trash'
         AND post_date >= %s AND post_date <= %s",
        $week_start . ' 00:00:00',
        $week_end . ' 23:59:59'
    ));
    $booking_weeks[] = array('label' => $label, 'count' => $count);
}
$max_booking = max(1, max(array_column($booking_weeks, 'count')));

// ─── Transaction summary numbers ─────────────────────────
$total_bookings_this_month = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'cm_booking'
         AND post_status != 'trash'
         AND MONTH(post_date) = %d AND YEAR(post_date) = %d",
        (int) date('m'),
        (int) date('Y')
    )
);
$total_bookings_today = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'cm_booking'
         AND post_status != 'trash'
         AND DATE(post_date) = %s",
        date('Y-m-d')
    )
);
$total_bookings_all_time = $total_bookings_count;

// ─── Pending user verifications count ───────────────────
$pending_users_query = new WP_User_Query(array(
    'meta_query'  => array(
        array('key' => '_cm_verification_status', 'value' => 'pending')
    ),
    'count_total' => true
));
$pending_count = $pending_users_query->get_total();

// Active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
?>

<div class="flex min-h-[calc(100vh-80px)]">

    <!-- Sidebar Navigation -->
    <aside class="w-72 flex-shrink-0 border-r border-primary/10 bg-white/50 backdrop-blur-xl flex flex-col">
        <div class="p-6 flex items-center gap-3">
            <div class="size-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">school</span>
            </div>
            <div>
                <h2 class="text-lg font-bold leading-tight tracking-tight text-slate-900">CampusMarket</h2>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-widest text-primary">Admin Control</p>
            </div>
        </div>
        <nav class="flex-1 px-4 space-y-1.5">
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'overview' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'overview')); ?>">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm font-semibold">Overview</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'verifications' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'verifications')); ?>">
                <span class="material-symbols-outlined">verified_user</span>
                <span class="text-sm font-semibold">Verifications</span>
                <?php 
                $pending_users_query = new WP_User_Query(array(
                    'meta_query' => array(
                        array('key' => '_cm_verification_status', 'value' => 'pending')
                    ),
                    'count_total' => true
                ));
                $pending_count = $pending_users_query->get_total();
                if ($pending_count > 0) : 
                ?>
                    <span class="ml-auto bg-white/20 px-2 py-0.5 rounded text-[10px]"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'users' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'users')); ?>">
                <span class="material-symbols-outlined">group</span>
                <span class="text-sm font-semibold">Users</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'listings' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'listings')); ?>">
                <span class="material-symbols-outlined">shopping_bag</span>
                <span class="text-sm font-semibold">Listings</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'pending' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'pending')); ?>">
                <span class="material-symbols-outlined">pending_actions</span>
                <span class="text-sm font-semibold">Listing Approvals</span>
            </a>
        </nav>
        <div class="p-4 mt-auto">
            <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="size-10 rounded-full border-2 border-primary/20 overflow-hidden">
                        <?php echo get_avatar(get_current_user_id(), 40, '', '', array('class' => 'w-full h-full object-cover')); ?>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900"><?php echo esc_html(wp_get_current_user()->display_name); ?></p>
                        <p class="text-xs text-slate-500">Super Admin</p>
                    </div>
                </div>
                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="w-full flex items-center justify-center gap-2 py-2 text-xs font-bold text-slate-600 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Site
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 bg-slate-50 overflow-y-auto">

        <?php if ($active_tab === 'overview') : ?>
        <div class="space-y-8 min-w-0">
            <!-- Welcome Hero -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 animate-fade-slide-up">
                <div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-900">Dashboard Overview</h1>
                    <p class="text-slate-500 font-medium mt-1">Real-time marketplace insights for <span class="text-primary font-bold"><?php echo esc_html(current_time('M j, Y')); ?></span></p>
                </div>
                <div class="flex gap-3">
                    <button class="flex items-center gap-2 px-5 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold shadow-sm hover:border-primary/30 transition-all">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        Last 30 Days
                    </button>
                    <button class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-2xl text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-lg">download</span>
                        Export Report
                    </button>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-slide-up stagger-1">
                <div class="glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-primary/10 text-primary rounded-xl">
                            <span class="material-symbols-outlined block">group</span>
                        </div>
                        <span class="text-emerald-500 text-xs font-black bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">trending_up</span>+12%
                        </span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Total Users</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($total_users['total_users']); ?></p>
                </div>
                <div class="glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-purple-100 text-purple-600 rounded-xl">
                            <span class="material-symbols-outlined block">inventory_2</span>
                        </div>
                        <span class="text-emerald-500 text-xs font-black bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">trending_up</span>+5%
                        </span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Active Listings</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($published_listings); ?></p>
                </div>
                <div class="glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-orange-100 text-orange-600 rounded-xl">
                            <span class="material-symbols-outlined block">verified_user</span>
                        </div>
                        <span class="text-rose-500 text-xs font-black bg-rose-50 px-3 py-1 rounded-full border border-rose-100 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">trending_down</span>-2%
                        </span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Pending Approvals</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($pending_approval->found_posts); ?></p>
                </div>
                <div class="glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl">
                            <span class="material-symbols-outlined block">payments</span>
                        </div>
                        <span class="text-emerald-500 text-xs font-black bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">trending_up</span>+18%
                        </span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Bookings</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($total_bookings_count); ?></p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-fade-slide-up stagger-2">
                <!-- User Growth -->
                <div class="glass-panel p-8 rounded-3xl border-none shadow-sm overflow-hidden relative">
                    <div class="flex items-center justify-between mb-10">
                        <h4 class="font-bold text-xl text-slate-900">User Growth</h4>
                        <div class="flex gap-4">
                            <span class="flex items-center gap-2 text-xs font-bold text-slate-500"><span class="w-3 h-3 bg-primary rounded-full shadow-[0_0_8px_rgba(17,82,212,0.4)]"></span> New Signups</span>
                        </div>
                    </div>
                    <div class="h-64 flex items-end gap-3 w-full pb-6 relative">
                        <?php foreach($user_growth as $day) :
                            $pct = $max_growth > 0 ? max(4, round(($day['count'] / $max_growth) * 100)) : 4;
                        ?>
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <span class="text-[9px] font-bold text-slate-400"><?php echo $day['count'] ?: ''; ?></span>
                                <div class="w-full bg-slate-100 rounded-t-2xl relative group cursor-pointer" style="height: <?php echo $pct; ?>%">
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-primary to-primary/80 rounded-t-2xl transition-all group-hover:shadow-[0_0_15px_rgba(17,82,212,0.3)] h-full"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between px-2 text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                        <?php foreach($user_growth as $day) : ?>
                            <span><?php echo esc_html($day['label']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Booking Volume -->
                <div class="glass-panel p-8 rounded-3xl border-none shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-10">
                        <h4 class="font-bold text-xl text-slate-900">Booking Volume</h4>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Last 6 Weeks</span>
                    </div>
                    <div class="h-48 flex items-end gap-3 w-full pb-4">
                        <?php foreach($booking_weeks as $week) :
                            $pct = $max_booking > 0 ? max(4, round(($week['count'] / $max_booking) * 100)) : 4;
                        ?>
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <span class="text-[9px] font-bold text-slate-400"><?php echo $week['count'] ?: ''; ?></span>
                                <div class="w-full bg-emerald-100 rounded-t-xl relative" style="height: <?php echo $pct; ?>%">
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-t-xl h-full"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between px-1 text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">
                        <?php foreach($booking_weeks as $week) : ?>
                            <span><?php echo esc_html($week['label']); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-6 grid grid-cols-3 gap-6">
                        <div class="text-center p-3 rounded-2xl hover:bg-primary/5 transition-colors cursor-pointer">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Today</p>
                            <p class="text-xl font-black text-primary"><?php echo $total_bookings_today; ?></p>
                        </div>
                        <div class="text-center p-3 rounded-2xl hover:bg-primary/5 transition-colors cursor-pointer">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">This Month</p>
                            <p class="text-xl font-black text-primary"><?php echo $total_bookings_this_month; ?></p>
                        </div>
                        <div class="text-center p-3 rounded-2xl hover:bg-primary/5 transition-colors cursor-pointer">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">All Time</p>
                            <p class="text-xl font-black text-primary"><?php echo $total_bookings_all_time; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification & Activity Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 animate-fade-slide-up stagger-3">
                <!-- Pending Approvals Table -->
                <div class="xl:col-span-2 glass-panel rounded-3xl border-none shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="font-bold text-xl text-slate-900">Listing Review Queue</h4>
                        <a href="<?php echo esc_url(add_query_arg('tab', 'pending')); ?>" class="text-primary text-sm font-bold hover:bg-primary/5 px-4 py-2 rounded-xl transition-all">View Full Queue</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-50">
                                    <th class="px-8 py-5">Listing</th>
                                    <th class="px-8 py-5">Seller</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if ($pending_approval->have_posts()) : while ($pending_approval->have_posts()) : $pending_approval->the_post(); 
                                    $l_id = get_the_ID();
                                    $l_author = get_userdata(get_post_field('post_author', $l_id));
                                ?>
                                    <tr class="hover:bg-primary/[0.02] transition-colors group" id="admin-listing-<?php echo $l_id; ?>">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl border border-slate-100 overflow-hidden shadow-sm">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <img class="w-full h-full object-cover" src="<?php the_post_thumbnail_url('thumbnail'); ?>" />
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900 group-hover:text-primary transition-colors"><?php the_title(); ?></p>
                                                    <p class="text-xs text-slate-400">Rs. <?php echo esc_html(get_post_meta($l_id, '_cm_price', true)); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <p class="text-xs font-bold text-slate-600"><?php echo $l_author ? esc_html($l_author->display_name) : 'Anonymous'; ?></p>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-all cm-admin-approve" data-listing-id="<?php echo $l_id; ?>" data-action="rejected">
                                                    <span class="material-symbols-outlined text-xl">close</span>
                                                </button>
                                                <button class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all cm-admin-approve" data-listing-id="<?php echo $l_id; ?>" data-action="approved">
                                                    Approve
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); else: ?>
                                    <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 italic">No listings pending review</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Flagged Priority List -->
                <div class="glass-panel p-8 rounded-3xl border-none shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <h4 class="font-bold text-xl text-slate-900">Priority Alerts</h4>
                        <span class="bg-rose-500 text-white text-[10px] font-black px-3 py-1 rounded-full">ACTION REQD</span>
                    </div>
                    <div class="space-y-5 flex-1 overflow-y-auto max-h-[400px] custom-scrollbar">
                        <?php 
                        // Show recent listings as "priority" for demo
                        if ($recent_listings->have_posts()) : while ($recent_listings->have_posts()) : $recent_listings->the_post(); 
                            $l_id = get_the_ID();
                        ?>
                            <div class="p-4 rounded-2xl bg-white/40 border border-white hover:border-rose-200 transition-all group">
                                <div class="flex gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0 shadow-sm border border-slate-50">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <img class="w-full h-full object-cover" src="<?php the_post_thumbnail_url('thumbnail'); ?>" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <h5 class="font-bold text-sm truncate text-slate-900 group-hover:text-primary transition-colors"><?php the_title(); ?></h5>
                                            <span class="material-symbols-outlined text-rose-500 text-lg">error</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">System Audit Flag</p>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-primary font-black text-sm">Rs. <?php echo esc_html(get_post_meta($l_id, '_cm_price', true)); ?></span>
                                            <div class="flex gap-1">
                                                <a href="<?php the_permalink(); ?>" class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all"><span class="material-symbols-outlined text-sm">visibility</span></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); endif; ?>
                    </div>
                    <button class="w-full mt-6 py-3 border border-slate-200 rounded-2xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-all">View All Alerts</button>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'verifications') : ?>
            <!-- Verification Management UI -->
            <div class="flex gap-8 h-[calc(100vh-160px)] animate-fade-slide-up">
                <!-- Main Queue -->
                <div class="flex-1 space-y-8 overflow-y-auto custom-scrollbar pr-4">
                    <header class="flex justify-between items-end">
                        <div>
                            <h1 class="text-3xl font-black tracking-tight text-slate-900">Student Verifications</h1>
                            <p class="text-slate-500 mt-1">Reviewing pending identification requests for platform access</p>
                        </div>
                    </header>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="glass-panel p-6 rounded-2xl">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-primary/10 rounded-xl text-primary">
                                    <span class="material-symbols-outlined">pending_actions</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Pending Requests</p>
                            <p class="text-3xl font-black mt-1"><?php echo $pending_count; ?></p>
                        </div>
                        <div class="glass-panel p-6 rounded-2xl">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500">
                                    <span class="material-symbols-outlined">check_circle</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Verified Students</p>
                            <?php $verified_count = count(get_users(array('meta_key' => '_cm_verified', 'meta_value' => '1'))); ?>
                            <p class="text-3xl font-black mt-1"><?php echo $verified_count; ?></p>
                        </div>
                        <div class="glass-panel p-6 rounded-2xl">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500">
                                    <span class="material-symbols-outlined">timer</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Avg Review Time</p>
                            <p class="text-3xl font-black mt-1">2.4h</p>
                        </div>
                    </div>

                    <div class="glass-panel rounded-2xl overflow-hidden min-h-[400px]">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="font-bold text-slate-900">Queue</h3>
                            <div class="relative w-64">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                                <input class="w-full bg-slate-100 border-none rounded-xl pl-10 text-sm focus:ring-2 focus:ring-primary/20" placeholder="Search students..." type="text">
                            </div>
                        </div>
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Student</th>
                                    <th class="px-6 py-4">Submission Date</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php 
                                // Fetch all unverified users or users with pending status
                                $pending_users_query = new WP_User_Query(array(
                                    'meta_query' => array(
                                        array('key' => '_cm_verification_status', 'value' => 'pending')
                                    ),
                                    'orderby' => 'registered',
                                    'order' => 'DESC'
                                ));
                                $pending_list = $pending_users_query->get_results();
                                
                                foreach($pending_list as $u) : 
                                    $submit_date = $u->user_registered;
                                ?>
                                    <tr class="hover:bg-primary/5 transition-colors group cursor-pointer cm-inspect-user" data-user-json='<?php 
                                        $id_front_id = get_user_meta($u->ID, "_cm_id_card_front", true) ?: get_user_meta($u->ID, "_cm_id_url", true);
                                        $id_back_id = get_user_meta($u->ID, "_cm_id_card_back", true);
                                        $identity_id = get_user_meta($u->ID, "_cm_verified_identity_photo", true);
                                        $profile_id = get_user_meta($u->ID, "_cm_profile_photo", true);
                                        
                                        echo esc_attr(json_encode([
                                            "id" => $u->ID,
                                            "name" => $u->display_name,
                                            "email" => $u->user_email,
                                            "phone" => get_user_meta($u->ID, "_cm_phone", true) ?: "N/A",
                                            "dept" => get_user_meta($u->ID, "_cm_department", true) ?: "General",
                                            "sid" => get_user_meta($u->ID, "_cm_student_id", true) ?: "N/A",
                                            "id_front" => $id_front_id ? wp_get_attachment_image_url($id_front_id, "large") : "",
                                            "id_back" => $id_back_id ? wp_get_attachment_image_url($id_back_id, "large") : "",
                                            "identity" => $identity_id ? wp_get_attachment_image_url($identity_id, "large") : "",
                                            "profile" => $profile_id ? wp_get_attachment_image_url($profile_id, "large") : "",
                                            "date" => date("M j, Y", strtotime($u->user_registered))
                                        ])); 
                                    ?>'>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary">
                                                    <?php echo substr($u->display_name, 0, 1); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900"><?php echo esc_html($u->display_name); ?></p>
                                                    <p class="text-xs text-slate-500"><?php echo esc_html($u->user_email); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <?php echo date('M j, Y', strtotime($submit_date)); ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cm-admin-verify" data-user-id="<?php echo $u->ID; ?>" data-verify="1" title="Approve">
                                                    <span class="material-symbols-outlined">check_circle</span>
                                                </button>
                                                <button class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cm-admin-verify" data-user-id="<?php echo $u->ID; ?>" data-verify="0" title="Reject">
                                                    <span class="material-symbols-outlined">cancel</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                </div>
            </div>

            <script>
            (function($){
                $(document).on('click', '.cm-inspect-user', function(e){
                    // Prevent trigger if clicking on action buttons
                    if ($(e.target).closest('.cm-admin-verify').length) return;

                    var data = $(this).data('user-json');
                    var $modal = $('#cm-verification-modal');
                    
                    // Populate Name & Details
                    $('#modal-user-name').text(data.name);
                    
                    var detailsHtml = `
                        <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Email</span><span class="text-sm font-bold text-slate-700 dark:text-slate-300">${data.email}</span></div>
                        <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Phone</span><span class="text-sm font-bold text-slate-700 dark:text-slate-300">${data.phone}</span></div>
                        <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Dept</span><span class="text-sm font-bold text-primary">${data.dept}</span></div>
                        <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ID Num</span><span class="text-sm font-bold uppercase text-slate-700 dark:text-slate-300">${data.sid}</span></div>
                        <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Submitted</span><span class="text-sm font-medium text-slate-500">${data.date}</span></div>
                    `;
                    $('#modal-details').html(detailsHtml);
                    $('.cm-admin-verify-modal').attr('data-user-id', data.id);

                    // Populate Gallery
                    var galleryHtml = '';
                    if(data.identity) galleryHtml += `
                        <div class="space-y-3 bg-primary/5 p-4 rounded-3xl border border-primary/20 group cursor-zoom-in modal-img-zoom" data-url="${data.identity}" data-label="Live Camera Capture">
                            <p class="text-[10px] font-black text-primary uppercase flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">verified_user</span> Live Camera Capture (Archived)
                            </p>
                            <img src="${data.identity}" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-xl border-4 border-white dark:border-slate-800 transition-transform group-hover:scale-[1.02]" />
                        </div>`;
                    
                    if(data.id_front) galleryHtml += `
                        <div class="space-y-3 p-4 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm group cursor-zoom-in modal-img-zoom" data-url="${data.id_front}" data-label="Student ID (Front)">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Student ID (Front)</p>
                            <img src="${data.id_front}" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-md transition-transform group-hover:scale-[1.02]" />
                        </div>`;

                    if(data.id_back) galleryHtml += `
                        <div class="space-y-3 p-4 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm group cursor-zoom-in modal-img-zoom" data-url="${data.id_back}" data-label="Student ID (Back)">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Student ID (Back)</p>
                            <img src="${data.id_back}" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-md transition-transform group-hover:scale-[1.02]" />
                        </div>`;

                    if(data.profile && data.profile !== data.identity) galleryHtml += `
                        <div class="space-y-3 p-4 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm group cursor-zoom-in modal-img-zoom" data-url="${data.profile}" data-label="Current Profile Photo">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Current Profile Photo</p>
                            <img src="${data.profile}" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-md opacity-70 transition-transform group-hover:scale-[1.02]" />
                        </div>`;

                    $('#modal-gallery').html(galleryHtml);
                    
                    // Show Modal
                    $modal.removeClass('hidden').addClass('active');
                });

                // Image Zoom Logic
                $(document).on('click', '.modal-img-zoom', function(){
                    var url = $(this).data('url');
                    var label = $(this).data('label');
                    $('#zoomed-image').attr('src', url);
                    $('#zoomed-label').text(label);
                    $('#image-zoom-overlay').removeClass('hidden').addClass('flex');
                });
                
                $(document).on('click', '#image-zoom-overlay', function(){
                    $(this).removeClass('flex').addClass('hidden');
                });

                // Close Modal Logic
                $(document).on('click', '.close-modal, .modal-backdrop', function(){
                    $('#cm-verification-modal').removeClass('active');
                    setTimeout(function(){
                        $('#cm-verification-modal').addClass('hidden');
                    }, 500);
                });

                // Unified Verify Action (for both row buttons and modal buttons)
                $(document).on('click', '.cm-admin-verify, .cm-admin-verify-modal', function(e){
                    e.stopPropagation(); // Prevent row click from showing modal if row button is clicked
                    
                    var $btn = $(this);
                    var userId = $btn.data('user-id');
                    var verify = $btn.data('verify');
                    var remarks = '';

                    var actionLabel = verify == 1 ? 'APPROVE' : 'REJECT';
                    var confirmMsg = verify == 1 ? 'Are you sure you want to approve this student?' : 'Are you sure you want to reject this student?';

                    if(verify == 0) {
                        remarks = prompt('Enter rejection reason (Required):');
                        if (!remarks) return; // Cancel or empty
                    } else {
                        if(!confirm(confirmMsg)) return;
                    }

                    $btn.prop('disabled', true).addClass('opacity-50');

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'cm_verify_user',
                            user_id: userId,
                            verify: verify,
                            remarks: remarks,
                            nonce: '<?php echo wp_create_nonce('cm_nonce'); ?>'
                        },
                        success: function(response){
                            if(response.success) {
                                // Redirect to Verifications tab to refresh counts and list
                                window.location.href = window.location.pathname + '?tab=verifications';
                            } else {
                                alert('Error: ' + response.data);
                                $btn.prop('disabled', false).removeClass('opacity-50');
                            }
                        },
                        complete: function(){
                            $btn.prop('disabled', false).removeClass('opacity-50');
                        }
                    });
                });
            })(jQuery);
            </script>
        <?php elseif ($active_tab === 'users') : ?>
            <div class="mb-6 opacity-0 animate-fade-slide-up">
                <h2 class="text-2xl font-bold">User Management</h2>
                <p class="text-slate-500">All registered users</p>
            </div>

            <div class="glass-panel rounded-xl overflow-hidden opacity-0 animate-fade-slide-up stagger-1">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">User</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Email</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Role</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Verified</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = get_users(array('number' => 20, 'orderby' => 'registered', 'order' => 'DESC'));
                            foreach ($users as $u) :
                            ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden">
                                            <?php echo get_avatar($u->ID, 36, '', '', array('class' => 'w-full h-full object-cover')); ?>
                                        </div>
                                        <span class="font-semibold text-sm"><?php echo esc_html($u->display_name); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo esc_html($u->user_email); ?></td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-semibold"><?php echo esc_html(implode(', ', $u->roles)); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <?php if (cm_is_user_verified($u->ID)) : ?>
                                            <span class="material-symbols-outlined text-green-500 text-sm" style="font-variation-settings: 'FILL' 1;" title="Verified">verified</span>
                                            <button class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider cm-admin-verify" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="0">Revoke</button>
                                        <?php else : ?>
                                            <span class="material-symbols-outlined text-amber-500 text-sm" title="Unverified">pending_actions</span>
                                            <button class="text-[10px] font-bold text-slate-400 hover:text-green-500 uppercase tracking-wider cm-admin-verify" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="1">Verify</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?php echo esc_html(date('M j, Y', strtotime($u->user_registered))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($active_tab === 'listings') : ?>
            <div class="mb-6 opacity-0 animate-fade-slide-up">
                <h2 class="text-2xl font-bold">All Listings</h2>
                <p class="text-slate-500">Manage marketplace listings</p>
            </div>

            <?php
            $all_listings = new WP_Query(array(
                'post_type'      => 'cm_listing',
                'posts_per_page' => 20,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));
            ?>

            <div class="glass-panel rounded-xl overflow-hidden opacity-0 animate-fade-slide-up stagger-1">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Listing</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Seller</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Price</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Status</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Date</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($all_listings->have_posts()) : $all_listings->the_post();
                                $l_id = get_the_ID();
                                $l_price = get_post_meta($l_id, '_cm_price', true);
                                $l_status = get_post_meta($l_id, '_cm_approval_status', true);
                                $l_author = get_userdata(get_post_field('post_author', $l_id));
                            ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <img class="w-full h-full object-cover" src="<?php echo esc_url(get_the_post_thumbnail_url($l_id, 'thumbnail')); ?>" alt="">
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-semibold text-sm truncate max-w-[180px]"><?php the_title(); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm"><?php echo $l_author ? esc_html($l_author->display_name) : 'N/A'; ?></td>
                                <td class="px-6 py-4 text-sm font-bold">Rs. <?php echo esc_html($l_price); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php
                                        echo $l_status === 'approved' ? 'bg-green-100 text-green-700' : ($l_status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700');
                                    ?>"><?php echo esc_html(ucfirst($l_status ?: 'pending')); ?></span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?php echo esc_html(get_the_date('M j, Y')); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <a href="<?php the_permalink(); ?>" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-primary transition-all" title="View">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </a>
                                        <?php if ($l_status !== 'approved') : ?>
                                            <button class="p-1.5 rounded-lg hover:bg-green-50 text-slate-400 hover:text-green-600 transition-all cm-admin-approve" data-listing-id="<?php echo esc_attr($l_id); ?>" data-action="approved" title="Approve">
                                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                            </button>
                                        <?php endif; ?>
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-all cm-admin-approve" data-listing-id="<?php echo esc_attr($l_id); ?>" data-action="rejected" title="Reject">
                                            <span class="material-symbols-outlined text-sm">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($active_tab === 'pending') : ?>
            <!-- Dedicated Pending Approvals View -->
            <div class="space-y-8 animate-fade-slide-up">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">Listing Approvals</h1>
                    <p class="text-slate-500 mt-1">Found <span class="text-primary font-bold"><?php echo $pending_approval->found_posts; ?></span> listings awaiting moderation</p>
                </div>

                <?php if ($pending_approval->have_posts()) : ?>
                    <div class="glass-panel rounded-3xl overflow-hidden border-none shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-400 text-[10px] font-bold uppercase tracking-widest">
                                <tr>
                                    <th class="px-8 py-5">Listing Information</th>
                                    <th class="px-8 py-5">Seller Details</th>
                                    <th class="px-8 py-5">Price</th>
                                    <th class="px-8 py-5 text-right">Verification Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php while ($pending_approval->have_posts()) : $pending_approval->the_post(); 
                                    $l_id = get_the_ID();
                                    $l_author = get_userdata(get_post_field('post_author', $l_id));
                                ?>
                                    <tr class="hover:bg-primary/[0.02] transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-16 h-16 rounded-2xl border border-slate-100 overflow-hidden shadow-sm shrink-0">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <img class="w-full h-full object-cover" src="<?php the_post_thumbnail_url('thumbnail'); ?>" />
                                                    <?php else: ?>
                                                        <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                                            <span class="material-symbols-outlined text-3xl">image</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-base font-bold text-slate-900 group-hover:text-primary transition-colors truncate"><?php the_title(); ?></p>
                                                    <p class="text-xs text-slate-400 mt-0.5"><?php echo esc_html(get_the_date('M j, Y \a\t g:i a')); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="size-8 rounded-full bg-primary/5 flex items-center justify-center text-primary font-bold text-xs">
                                                    <?php echo substr($l_author->display_name, 0, 1); ?>
                                                </div>
                                                <p class="text-sm font-semibold text-slate-700"><?php echo esc_html($l_author->display_name); ?></p>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span class="text-base font-black text-slate-900">Rs. <?php echo esc_html(get_post_meta($l_id, '_cm_price', true)); ?></span>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button class="px-5 py-2.5 bg-rose-50 text-rose-600 text-xs font-bold rounded-xl hover:bg-rose-500 hover:text-white transition-all cm-admin-approve active:scale-95" data-listing-id="<?php echo $l_id; ?>" data-action="rejected">
                                                    Reject
                                                </button>
                                                <button class="px-6 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all cm-admin-approve active:scale-95" data-listing-id="<?php echo $l_id; ?>" data-action="approved">
                                                    Approve Listing
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="glass-panel rounded-3xl p-20 text-center">
                        <div class="size-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="material-symbols-outlined text-4xl">check_circle</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-2">Queue is Empty</h3>
                        <p class="text-slate-500">All student listings have been moderated. Check back later!</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<!-- High-Detail Verification Modal -->
<div id="cm-verification-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md transition-opacity duration-300 opacity-0 modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 md:p-8">
        <div class="bg-white dark:bg-slate-900 w-full max-w-5xl max-h-[90vh] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row translate-y-8 opacity-0 transition-all duration-500 modal-content scale-95 border border-white/20">
            <!-- Left: Document Gallery (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-8 border-r border-slate-100 dark:border-slate-800 custom-scrollbar bg-slate-50/30">
                <div class="flex items-center gap-3 mb-8">
                    <div class="size-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined font-bold">visibility</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Identity Information Documents</h3>
                </div>
                <div id="modal-gallery" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Images will be injected here -->
                </div>

                <!-- Fullscreen Image Zoom Overlay -->
                <div id="image-zoom-overlay" class="fixed inset-0 z-[110] bg-black/95 hidden items-center justify-center p-4 cursor-zoom-out">
                    <img src="" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl" id="zoomed-image" />
                    <button class="absolute top-8 right-8 text-white hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-4xl">close</span>
                    </button>
                    <p class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/60 text-xs font-bold uppercase tracking-widest" id="zoomed-label">Image Zoom</p>
                </div>
            </div>

            <!-- Right: Details & Actions -->
            <div class="w-full md:w-[400px] flex flex-col p-8 bg-white dark:bg-slate-900">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <p class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-2">Verification Detail</p>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight" id="modal-user-name">Student Name</h2>
                    </div>
                    <button class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 dark:text-slate-500 transition-colors flex items-center justify-center close-modal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-6 flex-1">
                    <div class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-4" id="modal-details">
                        <!-- Details will be injected here -->
                    </div>

                    <div class="p-5 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 mb-2">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            <span class="text-[10px] font-black uppercase tracking-widest">Security Audit</span>
                        </div>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Cross-verify the student ID photo with the live camera snapshot. Ensure student ID number matches the university record.
                        </p>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4">
                    <button class="group py-4 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl font-black text-sm hover:bg-rose-600 hover:text-white transition-all cm-admin-verify-modal active:scale-95" data-user-id="" data-verify="0">
                        REJECT
                    </button>
                    <button class="py-4 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all cm-admin-verify-modal active:translate-y-0" data-user-id="" data-verify="1">
                        APPROVE
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#cm-verification-modal.active { display: block; }
#cm-verification-modal.active .modal-backdrop { opacity: 1; }
#cm-verification-modal.active .modal-content { 
    opacity: 1; 
    transform: translateY(0) scale(1); 
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { 
    background: #e2e8f0; 
    border-radius: 10px; 
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>

<?php get_footer(); ?>
