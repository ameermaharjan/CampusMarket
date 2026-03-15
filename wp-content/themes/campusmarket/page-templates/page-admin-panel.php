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

// ─── Admin Actions ───────────────────────────────────────
if (isset($_GET['delete_booking']) && current_user_can('manage_options')) {
    $booking_id = intval($_GET['delete_booking']);
    wp_delete_post($booking_id, true);
    wp_redirect(remove_query_arg('delete_booking'));
    exit;
}

if (isset($_GET['delete_feedback']) && current_user_can('manage_options')) {
    $feedback_id = intval($_GET['delete_feedback']);
    wp_delete_post($feedback_id, true);
    wp_redirect(remove_query_arg('delete_feedback'));
    exit;
}

if (isset($_GET['delete_report']) && current_user_can('manage_options')) {
    $report_id = intval($_GET['delete_report']);
    wp_delete_post($report_id, true);
    wp_redirect(remove_query_arg('delete_report'));
    exit;
}

if (isset($_GET['resolve_report']) && current_user_can('manage_options')) {
    $report_id = intval($_GET['resolve_report']);
    update_post_meta($report_id, '_cm_report_status', 'resolved');
    
    // Notify reporter
    $report = get_post($report_id);
    if ($report) {
        $reporter_id = $report->post_author;
        $message = __('your report has been solved thank you', 'campusmarket');
        $notif_id = cm_add_notification($reporter_id, 'report_resolved', $message, home_url('/dashboard/'));
        if (!is_wp_error($notif_id)) {
            update_post_meta($notif_id, '_cm_sender_name', 'Admin');
        }
    }
    
    wp_redirect(remove_query_arg('resolve_report'));
    exit;
}

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

// ─── Verification Stats ──────────────────────────────
$pending_users_query = new WP_User_Query(array(
    'meta_query'  => array(
        array('key' => '_cm_verification_status', 'value' => 'pending')
    ),
    'count_total' => true
));
$pending_count = $pending_users_query->get_total();

$verified_count = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_cm_verified' AND meta_value = '1'"
);

$rejected_count = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_cm_verification_status' AND meta_value = 'rejected'"
);

// Avg Review Time Calculation
$avg_review_time_label = 'N/A';
$processed_users = get_users(array(
    'meta_query' => array(
        'relation' => 'AND',
        array('key' => '_cm_verification_processed_at', 'compare' => 'EXISTS')
    ),
    'fields' => array('ID', 'user_registered')
));

if ($processed_users) {
    $total_diff = 0;
    $count = 0;
    foreach ($processed_users as $u) {
        $processed_at = get_user_meta($u->ID, '_cm_verification_processed_at', true);
        $submitted_at = get_user_meta($u->ID, '_cm_verification_submitted_at', true) ?: $u->user_registered;
        
        if ($processed_at && $submitted_at) {
            $diff = strtotime($processed_at) - strtotime($submitted_at);
            if ($diff > 0) {
                $total_diff += $diff;
                $count++;
            }
        }
    }
    
    if ($count > 0) {
        $avg_seconds = $total_diff / $count;
        if ($avg_seconds < 3600) {
            $avg_review_time_label = round($avg_seconds / 60) . 'm';
        } else {
            $avg_review_time_label = round($avg_seconds / 3600, 1) . 'h';
        }
    }
}

// ─── Trend Calculations (Month-over-Month) ─────────────────
function cm_get_period_count($type, $start, $end) {
    global $wpdb;
    if ($type === 'user') {
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered BETWEEN %s AND %s",
            $start . ' 00:00:00',
            $end . ' 23:59:59'
        ));
    } else {
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'trash' AND post_date BETWEEN %s AND %s",
            $type,
            $start . ' 00:00:00',
            $end . ' 23:59:59'
        ));
    }
}

$this_month_start = date('Y-m-01');
$this_month_end   = date('Y-m-t');
$last_month_start = date('Y-m-01', strtotime('-1 month'));
$last_month_end   = date('Y-m-t', strtotime('-1 month'));

function cm_calculate_growth($current, $previous) {
    if ($previous <= 0) return $current > 0 ? 100 : 0;
    return round((($current - $previous) / $previous) * 100);
}

// User Growth
$users_this_month = cm_get_period_count('user', $this_month_start, $this_month_end);
$users_last_month = cm_get_period_count('user', $last_month_start, $last_month_end);
$user_growth_pct  = cm_calculate_growth($users_this_month, $users_last_month);

// Listing Growth
$listings_this_month = cm_get_period_count('cm_listing', $this_month_start, $this_month_end);
$listings_last_month = cm_get_period_count('cm_listing', $last_month_start, $last_month_end);
$listing_growth_pct  = cm_calculate_growth($listings_this_month, $listings_last_month);

// Booking Growth
$bookings_this_month = cm_get_period_count('cm_booking', $this_month_start, $this_month_end);
$bookings_last_month = cm_get_period_count('cm_booking', $last_month_start, $last_month_end);
$booking_growth_pct  = cm_calculate_growth($bookings_this_month, $bookings_last_month);

// ─── Priority Alerts (Pending Verifications) ────────────
$priority_users = new WP_User_Query(array(
    'meta_query' => array(
        array('key' => '_cm_verification_status', 'value' => 'pending')
    ),
    'number' => 5,
    'orderby' => 'registered',
    'order' => 'DESC'
));
$priority_alerts = $priority_users->get_results();

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
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'bookings' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'bookings')); ?>">
                <span class="material-symbols-outlined">payments</span>
                <span class="text-sm font-semibold">Bookings</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'feedback' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'feedback')); ?>">
                <span class="material-symbols-outlined">rate_review</span>
                <span class="text-sm font-semibold">Feedback</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $active_tab === 'reports' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5'; ?>" href="<?php echo esc_url(add_query_arg('tab', 'reports')); ?>">
                <span class="material-symbols-outlined">flag</span>
                <span class="text-sm font-semibold">Reports</span>
                <?php 
                $reports_count = wp_count_posts('cm_report')->publish;
                if ($reports_count > 0) : 
                ?>
                    <span class="ml-auto bg-white/20 px-2 py-0.5 rounded text-[10px]"><?php echo $reports_count; ?></span>
                <?php endif; ?>
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
                <a href="<?php echo esc_url(add_query_arg('tab', 'users')); ?>" class="block glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-primary/10 text-primary rounded-xl group-hover:bg-primary group-hover:text-white transition-all">
                            <span class="material-symbols-outlined block">group</span>
                        </div>
                        <?php if ($user_growth_pct != 0) : ?>
                        <span class="<?php echo $user_growth_pct >= 0 ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-rose-500 bg-rose-50 border-rose-100'; ?> text-xs font-black px-3 py-1 rounded-full border flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs"><?php echo $user_growth_pct >= 0 ? 'trending_up' : 'trending_down'; ?></span><?php echo ($user_growth_pct >= 0 ? '+' : '') . $user_growth_pct; ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Total Users</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($total_users['total_users']); ?></p>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'listings')); ?>" class="block glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-purple-100 text-purple-600 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined block">inventory_2</span>
                        </div>
                        <?php if ($listing_growth_pct != 0) : ?>
                        <span class="<?php echo $listing_growth_pct >= 0 ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-rose-500 bg-rose-50 border-rose-100'; ?> text-xs font-black px-3 py-1 rounded-full border flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs"><?php echo $listing_growth_pct >= 0 ? 'trending_up' : 'trending_down'; ?></span><?php echo ($listing_growth_pct >= 0 ? '+' : '') . $listing_growth_pct; ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Active Listings</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($published_listings); ?></p>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'pending')); ?>" class="block glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-orange-100 text-orange-600 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined block">verified_user</span>
                        </div>
                        <span class="text-rose-500 text-xs font-black bg-rose-50 px-3 py-1 rounded-full border border-rose-100 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">pending_actions</span>Pending
                        </span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Pending Approvals</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($pending_approval->found_posts); ?></p>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'bookings')); ?>" class="block glass-panel p-7 rounded-3xl shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined block">payments</span>
                        </div>
                        <?php if ($booking_growth_pct != 0) : ?>
                        <span class="<?php echo $booking_growth_pct >= 0 ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-rose-500 bg-rose-50 border-rose-100'; ?> text-xs font-black px-3 py-1 rounded-full border flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs"><?php echo $booking_growth_pct >= 0 ? 'trending_up' : 'trending_down'; ?></span><?php echo ($booking_growth_pct >= 0 ? '+' : '') . $booking_growth_pct; ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Bookings</h3>
                    <p class="text-4xl font-black mt-1 text-slate-900"><?php echo esc_html($total_bookings_count); ?></p>
                </a>
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
                        if (!empty($priority_alerts)) : foreach ($priority_alerts as $u) : 
                            $u_dept = get_user_meta($u->ID, '_cm_department', true) ?: 'Student';
                        ?>
                            <div class="p-4 rounded-2xl bg-white/40 border border-white hover:border-rose-200 transition-all group">
                                <div class="flex gap-4">
                                    <div class="w-14 h-14 rounded-full bg-slate-100 overflow-hidden shrink-0 shadow-sm border border-slate-50 flex items-center justify-center">
                                        <span class="text-lg font-bold text-primary"><?php echo substr($u->display_name, 0, 1); ?></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <h5 class="font-bold text-sm truncate text-slate-900"><?php echo esc_html($u->display_name); ?></h5>
                                            <span class="material-symbols-outlined text-rose-500 text-lg">verified_user</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Pending Verification</p>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-primary font-black text-xs"><?php echo esc_html($u_dept); ?></span>
                                            <a href="<?php echo esc_url(add_query_arg('tab', 'verifications')); ?>" class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-bold rounded-lg hover:bg-primary hover:text-white transition-all">Review ID</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="flex flex-col items-center justify-center h-full py-10 opacity-40">
                                <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
                                <p class="text-xs font-bold">All caught up!</p>
                            </div>
                        <?php endif; ?>
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

                    <div class="grid grid-cols-4 gap-6">
                        <a href="#verification-queue" class="glass-panel p-6 rounded-2xl hover:bg-primary/5 hover:border-primary/20 transition-all group block">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-primary/10 rounded-xl text-primary group-hover:bg-primary group-hover:text-white transition-all">
                                    <span class="material-symbols-outlined">pending_actions</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Pending Requests</p>
                            <p class="text-2xl font-black mt-1"><?php echo $pending_count; ?></p>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'users', 'status' => 'rejected'))); ?>" class="glass-panel p-6 rounded-2xl hover:bg-rose-50 hover:border-rose-200 transition-all group block">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-rose-500/10 rounded-xl text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-all">
                                    <span class="material-symbols-outlined">cancel</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Rejected Students</p>
                            <p class="text-2xl font-black mt-1"><?php echo $rejected_count; ?></p>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'users', 'status' => 'verified'))); ?>" class="glass-panel p-6 rounded-2xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group block">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                    <span class="material-symbols-outlined">check_circle</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Verified Students</p>
                            <p class="text-2xl font-black mt-1"><?php echo $verified_count; ?></p>
                        </a>
                        <div class="glass-panel p-6 rounded-2xl">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500">
                                    <span class="material-symbols-outlined">timer</span>
                                </div>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">Avg Review Time</p>
                            <p class="text-2xl font-black mt-1"><?php echo $avg_review_time_label; ?></p>
                        </div>
                    </div>

                    <div id="verification-queue" class="glass-panel rounded-2xl overflow-hidden min-h-[400px]">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="font-bold text-slate-900">Queue</h3>
                            <div class="relative w-64">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                                <input id="cm-search-queue" class="w-full bg-slate-100 border-none rounded-xl pl-10 text-sm focus:ring-2 focus:ring-primary/20" placeholder="Search students..." type="text">
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
                                
                                if ($pending_list) :
                                    foreach($pending_list as $u) : 
                                        $submit_date = $u->user_registered;
                                ?>
                                    <tr class="hover:bg-primary/5 transition-colors group cursor-pointer cm-inspect-user" data-user-json='<?php 
                                        $u_status = get_user_meta($u->ID, "_cm_verification_status", true);
                                        $is_v = cm_is_user_verified($u->ID) ? "1" : "0";
                                        
                                        echo esc_attr(json_encode([
                                            "id" => $u->ID,
                                            "name" => $u->display_name,
                                            "email" => $u->user_email,
                                            "phone" => get_user_meta($u->ID, "_cm_phone", true) ?: "N/A",
                                            "dept" => get_user_meta($u->ID, "_cm_department", true) ?: "General",
                                            "sid" => get_user_meta($u->ID, "_cm_student_id", true) ?: "N/A",
                                            "date" => date("M j, Y", strtotime($u->user_registered)),
                                            "profile" => cm_get_user_avatar_url($u->ID),
                                            "identity" => wp_get_attachment_image_url(get_user_meta($u->ID, "_cm_verified_identity_photo", true), "large"),
                                            "id_front" => wp_get_attachment_image_url(get_user_meta($u->ID, "_cm_id_card_front", true), "large") ?: wp_get_attachment_image_url(get_user_meta($u->ID, "_cm_id_url", true), "large"),
                                            "id_back" => wp_get_attachment_image_url(get_user_meta($u->ID, "_cm_id_card_back", true), "large"),
                                            "status" => $u_status,
                                            "remarks" => get_user_meta($u->ID, "_cm_verification_remarks", true) ?: "",
                                            "is_verified" => $is_v
                                        ])); 
                                    ?>'>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary">
                                                    <?php echo substr($u->display_name, 0, 1); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900"><?php echo esc_html($u->display_name); ?></p>
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-xs text-slate-500"><?php echo esc_html($u->user_email); ?></p>
                                                        <span class="text-[9px] font-bold bg-slate-100 px-1.5 py-0.5 rounded text-slate-400 uppercase"><?php echo esc_html(get_user_meta($u->ID, '_cm_student_id', true) ?: 'No SID'); ?></span>
                                                    </div>
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
            <?php else : ?>
                <div class="glass-panel rounded-3xl p-32 text-center opacity-0 animate-fade-slide-up">
                    <div class="size-24 bg-primary/5 rounded-full flex items-center justify-center mx-auto mb-8">
                        <span class="material-symbols-outlined text-5xl text-primary/40">verified_user</span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-900 mb-4">No Pending Verifications</h3>
                    <p class="text-slate-500 max-w-md mx-auto leading-relaxed">The queue is empty. All current student ID submissions have been processed and indexed.</p>
                </div>
            <?php endif; ?>
        </div>
<?php elseif ($active_tab === 'users') : ?>
            <div class="mb-6 opacity-0 animate-fade-slide-up">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold">User Management</h2>
                        <div class="flex items-center gap-2">
                            <p class="text-slate-500 text-sm">
                                <?php 
                                    if ($user_status_filter === 'verified') echo 'Verified Students only';
                                    elseif ($user_status_filter === 'rejected') echo 'Rejected Students only';
                                    else echo 'All registered users';
                                ?>
                            </p>
                            <?php if (isset($_GET['status']) || isset($_GET['s_user'])) : ?>
                                <a href="<?php echo esc_url(remove_query_arg(array('status', 's_user'))); ?>" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest ml-2">Show All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="get" class="relative w-72">
                        <input type="hidden" name="tab" value="users">
                        <?php if(isset($_GET['status'])) : ?>
                            <input type="hidden" name="status" value="<?php echo esc_attr($_GET['status']); ?>">
                        <?php endif; ?>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input name="s_user" value="<?php echo isset($_GET['s_user']) ? esc_attr($_GET['s_user']) : ''; ?>" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl pl-10 text-sm focus:ring-2 focus:ring-primary/20 shadow-sm" placeholder="Search by name, email or SID..." type="text">
                    </form>
                </div>
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
                            $user_status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                            $user_query_args = array('number' => 20, 'orderby' => 'registered', 'order' => 'DESC');
                            
                            if ($user_status_filter === 'verified') {
                                $user_query_args['meta_key'] = '_cm_verified';
                                $user_query_args['meta_value'] = '1';
                            } elseif ($user_status_filter === 'rejected') {
                                $user_query_args['meta_key'] = '_cm_verification_status';
                                $user_query_args['meta_value'] = 'rejected';
                            }
                            
                            if (isset($_GET['s_user']) && !empty($_GET['s_user'])) {
                                $search_term = sanitize_text_field($_GET['s_user']);
                                
                                // Query 1: Search standard user fields
                                $u_args_std = array(
                                    'search'         => '*' . $search_term . '*',
                                    'search_columns' => array('display_name', 'user_email', 'user_login'),
                                    'fields'         => 'ID',
                                    'number'         => -1
                                );
                                if ($user_status_filter === 'verified') {
                                    $u_args_std['meta_key'] = '_cm_verified';
                                    $u_args_std['meta_value'] = '1';
                                } elseif ($user_status_filter === 'rejected') {
                                    $u_args_std['meta_key'] = '_cm_verification_status';
                                    $u_args_std['meta_value'] = 'rejected';
                                }
                                $ids_std = get_users($u_args_std);

                                // Query 2: Search meta fields
                                $u_args_meta = array(
                                    'meta_query' => array(
                                        'relation' => 'AND',
                                    ),
                                    'fields' => 'ID',
                                    'number' => -1
                                );
                                if ($user_status_filter === 'verified') {
                                    $u_args_meta['meta_query'][] = array('key' => '_cm_verified', 'value' => '1');
                                } elseif ($user_status_filter === 'rejected') {
                                    $u_args_meta['meta_query'][] = array('key' => '_cm_verification_status', 'value' => 'rejected');
                                }
                                
                                $u_args_meta['meta_query'][] = array(
                                    'relation' => 'OR',
                                    array('key' => '_cm_student_id', 'value' => $search_term, 'compare' => 'LIKE'),
                                    array('key' => '_cm_phone', 'value' => $search_term, 'compare' => 'LIKE'),
                                    array('key' => '_cm_department', 'value' => $search_term, 'compare' => 'LIKE')
                                );
                                $ids_meta = get_users($u_args_meta);

                                $merged_ids = array_unique(array_merge($ids_std, $ids_meta));
                                if (empty($merged_ids)) {
                                    $user_query_args['include'] = array(0);
                                } else {
                                    $user_query_args['include'] = $merged_ids;
                                }
                            }
                            
                            $users = get_users($user_query_args);
                            foreach ($users as $u) :
                                $u_status = get_user_meta($u->ID, '_cm_verification_status', true);
                                $is_v = cm_is_user_verified($u->ID) ? '1' : '0';
                                
                                $user_data = array(
                                    'id'       => $u->ID,
                                    'name'     => $u->display_name,
                                    'email'    => $u->user_email,
                                    'phone'    => get_user_meta($u->ID, '_cm_phone', true) ?: 'N/A',
                                    'dept'     => get_user_meta($u->ID, '_cm_department', true) ?: 'N/A',
                                    'sid'      => get_user_meta($u->ID, '_cm_student_id', true) ?: 'N/A',
                                    'date'     => date('M j, Y', strtotime($u->user_registered)),
                                    'profile'  => cm_get_user_avatar_url($u->ID),
                                    'identity' => wp_get_attachment_image_url(get_user_meta($u->ID, '_cm_verified_identity_photo', true), 'large'),
                                    'id_front' => wp_get_attachment_image_url(get_user_meta($u->ID, '_cm_id_card_front', true), 'large') ?: wp_get_attachment_image_url(get_user_meta($u->ID, '_cm_id_url', true), 'large'),
                                    'id_back'  => wp_get_attachment_image_url(get_user_meta($u->ID, '_cm_id_card_back', true), 'large'),
                                    'status'   => $u_status,
                                    'remarks'  => get_user_meta($u->ID, '_cm_verification_remarks', true) ?: '',
                                    'is_verified' => $is_v
                                );
                            ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors cm-inspect-user cursor-pointer group" data-user-json='<?php echo esc_attr(json_encode($user_data)); ?>'>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden group-hover:ring-2 ring-primary/20 transition-all">
                                            <?php echo get_avatar($u->ID, 36, '', '', array('class' => 'w-full h-full object-cover')); ?>
                                        </div>
                                        <span class="font-semibold text-sm group-hover:text-primary transition-colors"><?php echo esc_html($u->display_name); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo esc_html($u->user_email); ?></td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-semibold"><?php echo esc_html(implode(', ', $u->roles)); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <?php if ($is_v === '1') : ?>
                                            <span class="px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">verified</span>
                                                Verified
                                            </span>
                                            <button class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider cm-admin-verify px-2 py-1" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="0">Revoke</button>
                                        <?php elseif ($u_status === 'rejected') : ?>
                                            <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-full text-[10px] font-bold uppercase tracking-wider">Rejected</span>
                                            <button class="text-[10px] font-bold text-slate-400 hover:text-green-500 uppercase tracking-wider cm-admin-verify px-2 py-1" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="1">Re-Verify</button>
                                        <?php else : ?>
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-full text-[10px] font-bold uppercase tracking-wider">Unverified</span>
                                            <div class="flex items-center gap-1 ml-2">
                                                <button class="p-1 text-red-400 hover:text-red-600 cm-admin-verify" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="0" title="Reject">
                                                    <span class="material-symbols-outlined text-sm">cancel</span>
                                                </button>
                                                <button class="p-1 text-green-400 hover:text-green-600 cm-admin-verify" data-user-id="<?php echo esc_attr($u->ID); ?>" data-verify="1" title="Approve">
                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                                    <div class="flex items-center justify-between">
                                        <?php echo esc_html(date('M j, Y', strtotime($u->user_registered))); ?>
                                        <span class="material-symbols-outlined text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                    </div>
                                </td>
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
        <?php elseif ($active_tab === 'bookings') : ?>
            <div class="mb-6 opacity-0 animate-fade-slide-up">
                <h2 class="text-2xl font-bold">Transaction History</h2>
                <p class="text-slate-500">Monitor all campus trades and rentals</p>
            </div>

            <?php
            $all_bookings = new WP_Query(array(
                'post_type'      => 'cm_booking',
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
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Item</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Owner / Renter</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Value</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Status</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Date</th>
                                <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($all_bookings->have_posts()) : while ($all_bookings->have_posts()) : $all_bookings->the_post();
                                $b_id = get_the_ID();
                                $b_listing_id = get_post_meta($b_id, '_cm_listing_id', true);
                                $b_owner_id = get_post_meta($b_id, '_cm_owner_id', true);
                                $b_renter_id = get_post_meta($b_id, '_cm_renter_id', true);
                                $b_status = get_post_meta($b_id, '_cm_status', true);
                                $b_price = get_post_meta($b_id, '_cm_total_price', true) ?: get_post_meta($b_listing_id, '_cm_price', true);
                                
                                $owner = get_userdata($b_owner_id);
                                $renter = get_userdata($b_renter_id);
                            ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-sm text-slate-900"><?php echo get_the_title($b_listing_id); ?></p>
                                    <p class="text-[10px] text-slate-400">ID: <?php echo $b_id; ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase w-10">Owner:</span>
                                            <span class="text-xs font-semibold text-slate-700"><?php echo $owner ? esc_html($owner->display_name) : 'Deleted'; ?></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase w-10">Renter:</span>
                                            <span class="text-xs font-semibold text-slate-700"><?php echo $renter ? esc_html($renter->display_name) : 'Deleted'; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo esc_html($b_price); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php
                                        echo $b_status === 'confirmed' ? 'bg-green-100 text-green-700' : ($b_status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700');
                                    ?>"><?php echo esc_html($b_status ?: 'pending'); ?></span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?php echo esc_html(get_the_date('M j, Y')); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <button class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-all" onclick="if(confirm('Delete this booking record?')){ window.location.href = '<?php echo esc_url(add_query_arg(['tab' => 'bookings', 'delete_booking' => $b_id])); ?>'; }">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; wp_reset_postdata(); else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">No transactions found</td></tr>
                            <?php endif; ?>
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
                                                <p class="text-sm font-semibold text-slate-700"><?php echo esc_html($l_author ? $l_author->display_name : 'Deleted'); ?></p>
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
        <?php elseif ($active_tab === 'feedback') : ?>
            <div class="space-y-8 animate-fade-slide-up">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">User Feedback</h1>
                    <p class="text-slate-500 mt-1">Found <span class="text-primary font-bold"><?php $f_count = wp_count_posts('cm_feedback'); echo $f_count->publish; ?></span> feedback submissions</p>
                </div>

                <?php
                $feedbacks = new WP_Query(array(
                    'post_type'      => 'cm_feedback',
                    'posts_per_page' => 20,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ));
                ?>

                <?php if ($feedbacks->have_posts()) : ?>
                    <div class="glass-panel rounded-3xl overflow-hidden border-none shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-50">
                                <tr>
                                    <th class="px-8 py-5">Student</th>
                                    <th class="px-8 py-5">Subject & Rating</th>
                                    <th class="px-8 py-5">Feedback Message</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php while ($feedbacks->have_posts()) : $feedbacks->the_post(); 
                                    $f_id = get_the_ID();
                                    $f_author_id = get_post_field('post_author', $f_id);
                                    $f_author = get_userdata($f_author_id);
                                    $f_rating = get_post_meta($f_id, '_cm_feedback_rating', true) ?: 5;
                                ?>
                                    <tr class="hover:bg-primary/[0.02] transition-colors group cursor-pointer cm-inspect-feedback" 
                                        data-feedback-id="<?php echo $f_id; ?>"
                                        data-feedback-json='<?php echo esc_attr(json_encode([
                                            'id' => $f_id,
                                            'author' => $f_author ? $f_author->display_name : 'Guest',
                                            'author_id' => $f_author_id,
                                            'subject' => get_the_title(),
                                            'rating' => $f_rating,
                                            'message' => get_the_content(),
                                            'date' => get_the_date('M j, Y'),
                                            'replied' => get_post_meta($f_id, '_cm_feedback_replied', true) ? 1 : 0
                                        ])); ?>'>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                                    <?php echo $f_author ? substr($f_author->display_name, 0, 1) : 'G'; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-slate-900 truncate"><?php echo $f_author ? esc_html($f_author->display_name) : 'Guest'; ?></p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight"><?php echo esc_html(get_the_date('M j, Y')); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="text-sm font-bold text-primary"><?php the_title(); ?></p>
                                                <span class="cm-replied-badge px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black uppercase rounded-full <?php echo get_post_meta($f_id, '_cm_feedback_replied', true) ? '' : 'hidden'; ?>">Replied</span>
                                            </div>
                                            <div class="flex gap-0.5">
                                                <?php for($i=1; $i<=5; $i++) : ?>
                                                    <span class="material-symbols-outlined text-[10px] <?php echo $i <= $f_rating ? 'text-amber-400' : 'text-slate-200'; ?>" style="font-variation-settings: 'FILL' 1;">star</span>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 max-w-md">
                                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2"><?php echo wp_trim_words(get_the_content(), 15); ?></p>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <button class="p-3 text-rose-500 hover:bg-rose-50 rounded-2xl transition-all active:scale-95 cm-delete-feedback" 
                                                    data-id="<?php echo $f_id; ?>">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="glass-panel rounded-3xl p-20 text-center">
                        <div class="size-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="material-symbols-outlined text-4xl">rate_review</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-2">No Feedback Yet</h3>
                        <p class="text-slate-500">Submissions from students will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($active_tab === 'reports') : ?>
            <div class="space-y-8 animate-fade-slide-up">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">User Reports</h1>
                    <p class="text-slate-500 mt-1">Reviewing flagged accounts and transaction misconduct</p>
                </div>

                <?php
                $reports = new WP_Query(array(
                    'post_type'      => 'cm_report',
                    'posts_per_page' => 20,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'post_status'    => 'publish'
                ));
                ?>

                <?php if ($reports->have_posts()) : ?>
                    <div class="glass-panel rounded-3xl overflow-hidden border-none shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-50">
                                <tr>
                                    <th class="px-8 py-5">Reported User</th>
                                    <th class="px-8 py-5">Reporter</th>
                                    <th class="px-8 py-5">Reason & Details</th>
                                    <th class="px-8 py-5">Status</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php while ($reports->have_posts()) : $reports->the_post(); 
                                    $r_id = get_the_ID();
                                    $reported_user_id = get_post_meta($r_id, '_cm_reported_user_id', true);
                                    $reported_user = get_userdata($reported_user_id);
                                    $reporter_id = get_post_field('post_author', $r_id);
                                    $reporter = get_userdata($reporter_id);
                                    $reason = get_post_meta($r_id, '_cm_report_reason', true);
                                    $status = get_post_meta($r_id, '_cm_report_status', true) ?: 'pending';
                                    $replied = get_post_meta($r_id, '_cm_report_replied', true);
                                    
                                    $report_data = array(
                                        'id' => $r_id,
                                        'reported_name' => $reported_user ? $reported_user->display_name : 'Deleted User',
                                        'reported_id' => $reported_user_id,
                                        'reporter_name' => $reporter ? $reporter->display_name : 'Guest/Deleted',
                                        'reason' => $reason,
                                        'message' => get_the_content(),
                                        'status' => $status,
                                        'replied' => $replied,
                                        'resolve_url' => esc_url(add_query_arg(['tab' => 'reports', 'resolve_report' => $r_id])),
                                        'delete_url' => esc_url(add_query_arg(['tab' => 'reports', 'delete_report' => $r_id]))
                                    );
                                ?>
                                    <tr class="hover:bg-primary/[0.02] transition-colors group cursor-pointer cm-inspect-report" data-report-json='<?php echo esc_attr(json_encode($report_data)); ?>'>
                                        <td class="px-8 py-6">
                                            <?php if ($reported_user) : ?>
                                                <div class="flex items-center gap-3">
                                                    <div class="size-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 font-bold text-xs">
                                                        <?php echo substr($reported_user->display_name, 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-900"><?php echo esc_html($reported_user->display_name); ?></p>
                                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">ID: <?php echo $reported_user_id; ?></p>
                                                    </div>
                                                </div>
                                            <?php else : ?>
                                                <span class="text-slate-400 italic text-xs">Deleted User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6">
                                            <?php if ($reporter) : ?>
                                                <div class="flex items-center gap-3">
                                                    <div class="size-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-[10px]">
                                                        <?php echo substr($reporter->display_name, 0, 1); ?>
                                                    </div>
                                                    <p class="text-xs font-bold text-slate-600"><?php echo esc_html($reporter->display_name); ?></p>
                                                </div>
                                            <?php else : ?>
                                                <span class="text-slate-400 italic text-[10px]">Guest/Deleted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6 max-w-xs">
                                            <div class="flex flex-wrap gap-2 mb-1">
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-black uppercase rounded border border-amber-100"><?php echo esc_html($reason); ?></span>
                                                <?php if ($replied) : ?>
                                                    <span class="px-2 py-0.5 bg-sky-50 text-sky-700 text-[10px] font-black uppercase rounded border border-sky-100 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[10px]">reply</span> REPLIED
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed"><?php echo get_the_content(); ?></p>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php
                                                echo $status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                                            ?>"><?php echo esc_html($status); ?></span>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-all cm-delete-report" onclick="event.stopPropagation(); if(confirm('Permanently delete this report?')){ window.location.href = '<?php echo esc_url(add_query_arg(['tab' => 'reports', 'delete_report' => $r_id])); ?>'; }">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
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
                        <div class="size-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="material-symbols-outlined text-4xl">flag</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-2">No Reports Yet</h3>
                        <p class="text-slate-500">The platform is clean! No user reports have been filed yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<!-- Feedback Detail Modal -->
<div id="cm-feedback-detail-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300 opacity-0 modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col translate-y-8 opacity-0 transition-all duration-500 modal-content scale-95">
            <div class="p-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-2">Detailed Feedback</p>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight" id="modal-feedback-subject">Subject</h2>
                    </div>
                    <button class="size-10 rounded-full hover:bg-slate-100 text-slate-400 transition-colors flex items-center justify-center close-modal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Student Info -->
                    <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="size-12 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold" id="modal-feedback-author-initial">S</div>
                        <div>
                            <p class="text-sm font-bold text-slate-900" id="modal-feedback-author">Student Name</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest" id="modal-feedback-date">Date</p>
                        </div>
                        <div class="ml-auto flex gap-0.5" id="modal-feedback-stars">
                            <!-- Stars injected here -->
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="p-6 bg-white border-2 border-slate-50 rounded-2xl">
                        <p class="text-sm text-slate-600 leading-relaxed italic" id="modal-feedback-message">"Feedback content..."</p>
                    </div>

                    <!-- Action Info -->
                    <div id="modal-reply-status" class="hidden p-4 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm font-bold">check_circle</span>
                        <span class="text-xs font-bold uppercase tracking-tight">Already Replied to this student</span>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4">
                    <button class="py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-sm hover:bg-rose-50 hover:text-rose-500 transition-all active:scale-95 cm-modal-delete-feedback">
                        DELETE
                    </button>
                    <button class="py-4 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all active:translate-y-0" id="cm-reply-feedback-btn">
                        REPLY (THANK YOU)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Detail Modal -->
<div id="cm-report-detail-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300 opacity-0 modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col translate-y-8 opacity-0 transition-all duration-500 modal-content scale-95 border border-slate-100">
            <div class="p-8 border-b border-slate-50 relative">
                <button class="absolute top-8 right-8 text-slate-400 hover:text-slate-600 transition-colors bg-slate-50 p-2 rounded-full close-modal">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
                <div class="flex items-center gap-4 mb-4">
                    <div class="size-14 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100">
                        <span class="material-symbols-outlined text-3xl">flag</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 leading-tight">Report Details</h2>
                        <p class="text-slate-500 text-sm font-medium">Reviewing misconduct submission</p>
                    </div>
                </div>
            </div>

            <div class="p-10 overflow-y-auto max-h-[60vh] space-y-8 scrollbar-hide">
                <div class="grid grid-cols-2 gap-8">
                    <div class="p-3 bg-slate-50/50 rounded-2xl border border-slate-100/50">
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2 px-2">Reported User</p>
                        <div id="report-modal-reported-user" class="text-sm font-bold text-slate-900 px-2 flex items-center gap-2">
                             <!-- Dynamic content -->
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50/50 rounded-2xl border border-slate-100/50">
                        <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2 px-2">Reporter</p>
                        <div id="report-modal-reporter" class="text-sm font-bold text-slate-600 px-2 flex items-center gap-2">
                            <!-- Dynamic content -->
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary-rgb),0.5)]"></span>
                        Reason for Report
                    </p>
                    <div id="report-modal-reason" class="text-sm font-black text-amber-600 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100 inline-block uppercase tracking-wider">
                        <!-- Dynamic content -->
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary-rgb),0.5)]"></span>
                        Detailed Message
                    </p>
                    <div id="report-modal-message" class="text-slate-600 leading-relaxed text-sm bg-slate-50 p-6 rounded-3xl border border-slate-100 relative italic">
                        <span class="material-symbols-outlined absolute -top-3 -left-1 text-slate-200 text-4xl -z-10 opacity-50">format_quote</span>
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button id="cm-report-reply-btn" class="flex items-center gap-2 px-6 py-3.5 bg-slate-900 text-white rounded-2xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-black/10 active:scale-95 group">
                        <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">reply</span>
                        REPLY (THANK YOU)
                    </button>
                    <button id="cm-report-resolve-btn" class="flex items-center gap-2 px-6 py-3.5 bg-emerald-600 text-white rounded-2xl font-bold text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 active:scale-95">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        RESOLVE
                    </button>
                </div>
                <button id="cm-report-delete-btn" class="p-3.5 text-rose-500 hover:bg-rose-50 rounded-2xl transition-all border border-transparent hover:border-rose-100 active:scale-95">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </div>
        </div>
    </div>
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
                    <div class="space-y-4">
                        <div class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-3" id="modal-profile-details">
                            <!-- Profile details -->
                        </div>
                        <div class="p-5 bg-primary/[0.03] dark:bg-primary/5 rounded-2xl border border-primary/10 space-y-3">
                            <p class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-1">Document Details</p>
                            <div class="space-y-3" id="modal-document-details">
                                <!-- Document details -->
                            </div>
                        </div>
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
#cm-verification-modal.active, #cm-feedback-detail-modal.active, #cm-report-detail-modal.active { display: block; }
#cm-verification-modal.active .modal-backdrop, #cm-feedback-detail-modal.active .modal-backdrop, #cm-report-detail-modal.active .modal-backdrop { opacity: 1; }
#cm-verification-modal.active .modal-content, #cm-feedback-detail-modal.active .modal-content, #cm-report-detail-modal.active .modal-content { 
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

<script>
(function($){
    $(document).on('click', '.cm-inspect-user', function(e){
        // Prevent trigger if clicking on action buttons
        if ($(e.target).closest('.cm-admin-verify').length) return;

        var data = $(this).data('user-json');
        var $modal = $('#cm-verification-modal');
        
        // Populate Name & Details
        $('#modal-user-name').text(data.name);
        
        var statusLabel = data.is_verified === '1' ? '<span class="text-green-500">Verified</span>' : (data.status === 'rejected' ? '<span class="text-rose-500">Rejected</span>' : '<span class="text-amber-500">Pending</span>');
        var docCount = (data.id_front ? 1 : 0) + (data.id_back ? 1 : 0) + (data.identity ? 1 : 0);
        
        var profileHtml = `
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Email</span><span class="text-sm font-bold text-slate-700 dark:text-slate-300 truncate ml-4">${data.email}</span></div>
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Phone</span><span class="text-sm font-bold text-slate-700 dark:text-slate-300">${data.phone}</span></div>
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Joined</span><span class="text-sm font-medium text-slate-500">${data.date}</span></div>
        `;
        $('#modal-profile-details').html(profileHtml);

        var docHtml = `
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Verification Status</span><span class="text-xs font-black uppercase">${statusLabel}</span></div>
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Department</span><span class="text-xs font-bold text-primary">${data.dept}</span></div>
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Student ID (SID)</span><span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase">${data.sid}</span></div>
            <div class="flex justify-between items-center"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Documents Uploaded</span><span class="text-xs font-bold text-slate-600">${docCount} / 3</span></div>
        `;
        if(data.remarks) {
            docHtml += `<div class="mt-4 pt-4 border-t border-rose-100/50">
                <p class="text-[10px] text-rose-400 font-black uppercase tracking-widest mb-1">Rejection Reason</p>
                <p class="text-xs text-rose-600 font-medium leading-relaxed">${data.remarks}</p>
            </div>`;
        }
        $('#modal-document-details').html(docHtml);
        $('.cm-admin-verify-modal').attr('data-user-id', data.id);

        // Update Modal Buttons based on status
        var $modalFooter = $('.cm-admin-verify-modal').parent();
        if(data.status === 'approved' || data.is_verified === '1') {
            $modalFooter.html(`
                <button class="col-span-2 py-4 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl font-black text-sm hover:bg-rose-600 hover:text-white transition-all cm-admin-verify-modal active:scale-95" data-user-id="${data.id}" data-verify="0">
                    REVOKE VERIFICATION
                </button>
            `);
        } else if(data.status === 'rejected') {
            $modalFooter.html(`
                <div class="col-span-2 py-4 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl font-black text-sm text-center">
                    PREVIOUSLY REJECTED
                </div>
                <button class="col-span-2 mt-2 py-3 bg-primary/10 text-primary rounded-xl font-bold text-xs hover:bg-primary hover:text-white transition-all cm-admin-verify-modal" data-user-id="${data.id}" data-verify="1">
                    APPROVE ANYWAY
                </button>
            `);
        } else {
            $modalFooter.html(`
                <button class="group py-4 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl font-black text-sm hover:bg-rose-600 hover:text-white transition-all cm-admin-verify-modal active:scale-95" data-user-id="${data.id}" data-verify="0">
                    CANCEL / REJECT
                </button>
                <button class="py-4 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all cm-admin-verify-modal active:translate-y-0" data-user-id="${data.id}" data-verify="1">
                    APPROVE
                </button>
            `);
        }

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

    // Unified Close Modal Logic
    $(document).on('click', '.close-modal, .modal-backdrop', function(){
        $('.fixed.inset-0.active').removeClass('active');
        setTimeout(function(){
            $('.fixed.inset-0:not(.active)').addClass('hidden');
        }, 500);
    });

    // Unified Verify Action (for both row buttons and modal buttons)
    $(document).on('click', '.cm-admin-verify, .cm-admin-verify-modal', function(e){
        e.stopPropagation(); 
        
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var verify = $btn.data('verify');
        var remarks = '';

        var actionLabel = verify == 1 ? 'APPROVE' : 'REJECT';
        var confirmMsg = verify == 1 ? 'Are you sure you want to approve this student?' : 'Are you sure you want to reject this student?';

        if(verify == 0) {
            remarks = prompt('Enter rejection reason (Required):');
            if (!remarks) return; 
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
                    var currentTab = new URLSearchParams(window.location.search).get('tab') || 'overview';
                    window.location.href = window.location.pathname + '?tab=' + currentTab;
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

    // Listing Approval Handler (Overview & Listing Tabs)
    $(document).on('click', '.cm-admin-approve', function(e){
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(this);
        var listingId = $btn.data('listing-id');
        var action = $btn.data('action'); 
        
        if(!confirm('Are you sure you want to ' + action + ' this listing?')) return;

        $btn.prop('disabled', true).addClass('opacity-50');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'cm_approve_listing',
                listing_id: listingId,
                approval_action: action,
                nonce: '<?php echo wp_create_nonce('cm_nonce'); ?>'
            },
            success: function(response){
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || response.data));
                    $btn.prop('disabled', false).removeClass('opacity-50');
                }
            },
            error: function(){
                alert('Connection error. Please try again.');
                $btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    });

    // Feedback Detail Modal Handler
    $(document).on('click', '.cm-inspect-feedback', function(e){
        if ($(e.target).closest('.cm-delete-feedback').length) return;

        var data = $(this).data('feedback-json');
        var $modal = $('#cm-feedback-detail-modal');

        $('#modal-feedback-subject').text(data.subject);
        $('#modal-feedback-author').text(data.author);
        $('#modal-feedback-date').text(data.date);
        $('#modal-feedback-message').text('"' + data.message + '"');
        $('#modal-feedback-author-initial').text(data.author.charAt(0));
        
        // Stars
        var starsHtml = '';
        for(var i=1; i<=5; i++) {
            starsHtml += `<span class="material-symbols-outlined text-xs ${i <= data.rating ? 'text-amber-400' : 'text-slate-200'}" style="font-variation-settings: 'FILL' 1;">star</span>`;
        }
        $('#modal-feedback-stars').html(starsHtml);

        // Reply Button State
        if (data.replied) {
            $('#modal-reply-status').removeClass('hidden');
            $('#cm-reply-feedback-btn').addClass('opacity-50 pointer-events-none').text('REPLIED ✓');
        } else {
            $('#modal-reply-status').addClass('hidden');
            $('#cm-reply-feedback-btn').removeClass('opacity-50 pointer-events-none').text('REPLY (THANK YOU)');
        }

        $('#cm-reply-feedback-btn').data('feedback-id', data.id);
        $('.cm-modal-delete-feedback').data('feedback-id', data.id);

        $modal.removeClass('hidden');
        setTimeout(() => $modal.addClass('active'), 10);
    });

    // Reply Action
    $(document).on('click', '#cm-reply-feedback-btn', function(){
        var $btn = $(this);
        var feedbackId = $btn.data('feedback-id');

        $btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-sm">sync</span> SENDING...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'cm_reply_feedback',
                feedback_id: feedbackId,
                nonce: '<?php echo wp_create_nonce('cm_nonce'); ?>'
            },
            success: function(response){
                if(response.success) {
                    window.cmToast('Thank you message sent to student!', 'success');
                    $btn.addClass('opacity-50 pointer-events-none').text('REPLIED ✓');
                    $('#modal-reply-status').removeClass('hidden');
                    // Update table row marker
                    $(`.cm-inspect-feedback[data-feedback-id="${feedbackId}"]`).find('.cm-replied-badge').removeClass('hidden');
                    // Reload for tab state
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Error: ' + response.data.message);
                    $btn.prop('disabled', false).text('REPLY (THANK YOU)');
                }
            }
        });
    });

    $(document).on('click', '.cm-delete-feedback, .cm-modal-delete-feedback', function(e){
        e.stopPropagation();
        var id = $(this).data('id') || $(this).data('feedback-id');
        if(confirm('Permanently delete this feedback?')){ 
            window.location.href = '<?php echo esc_url(add_query_arg(['tab' => 'feedback', 'delete_feedback' => ''])); ?>' + id; 
        }
    });


    // Report Detail Modal Handler
    $(document).on('click', '.cm-inspect-report', function(e){
        if ($(e.target).closest('.cm-delete-report').length) return;

        var data = $(this).data('report-json');
        var $modal = $('#cm-report-detail-modal');

        $('#report-modal-reported-user').html(`<div class="size-6 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-[10px] font-bold">${data.reported_name.charAt(0)}</div> ${data.reported_name} <span class="text-slate-300 font-normal">ID:${data.reported_id}</span>`);
        $('#report-modal-reporter').html(`<div class="size-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-[8px] font-bold">${data.reporter_name.charAt(0)}</div> ${data.reporter_name}`);
        $('#report-modal-reason').text(data.reason);
        $('#report-modal-message').text(data.message);

        // Reply Button State
        if (data.replied) {
            $('#cm-report-reply-btn').addClass('opacity-50 pointer-events-none').html('<span class="material-symbols-outlined text-lg">check_circle</span> ACKNOWLEDGED');
        } else {
            $('#cm-report-reply-btn').removeClass('opacity-50 pointer-events-none').html('<span class="material-symbols-outlined text-lg">reply</span> REPLY (THANK YOU)');
        }

        // Resolve Button State
        if (data.status === 'resolved') {
            $('#cm-report-resolve-btn').addClass('opacity-50 pointer-events-none').html('<span class="material-symbols-outlined text-lg">done_all</span> RESOLVED');
            $('#cm-report-reply-btn').addClass('hidden');
        } else {
            $('#cm-report-resolve-btn').removeClass('opacity-50 pointer-events-none').html('<span class="material-symbols-outlined text-lg">check_circle</span> RESOLVE');
            $('#cm-report-reply-btn').removeClass('hidden');
        }

        $('#cm-report-reply-btn').data('report-id', data.id);
        $('#cm-report-resolve-btn').data('resolve-url', data.resolve_url);
        $('#cm-report-delete-btn').data('delete-url', data.delete_url);

        $modal.removeClass('hidden');
        setTimeout(() => $modal.addClass('active'), 10);
    });

    $(document).on('click', '#cm-report-reply-btn', function(){
        var $btn = $(this);
        var reportId = $btn.data('report-id');

        $btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-sm">sync</span> SENDING...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'cm_reply_report',
                report_id: reportId,
                nonce: '<?php echo wp_create_nonce('cm_nonce'); ?>'
            },
            success: function(response){
                if(response.success) {
                    window.cmToast('Acknowledgement sent to reporter!', 'success');
                    $btn.addClass('opacity-50 pointer-events-none').html('<span class="material-symbols-outlined text-lg">check_circle</span> ACKNOWLEDGED');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + response.data.message);
                    $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-lg">reply</span> REPLY (THANK YOU)');
                }
            }
        });
    });

    $(document).on('click', '#cm-report-resolve-btn', function(){
        var url = $(this).data('resolve-url');
        if(confirm('Mark this report as resolved? This will notify the reporter.')) {
            window.location.href = url;
        }
    });

    $(document).on('click', '#cm-report-delete-btn', function(){
        var url = $(this).data('delete-url');
        if(confirm('Permanently delete this report?')) {
            window.location.href = url;
        }
    });

    // Real-time Queue Search
    $('#cm-search-queue').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#verification-queue-table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
})(jQuery);
</script>

<?php get_footer(); ?>
