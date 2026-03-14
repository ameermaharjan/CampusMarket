<?php
/**
 * Template Name: Edit Profile Page
 *
 * @package CampusMarket
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$active_tab = $_GET['tab'] ?? 'settings';
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cm_edit_profile_nonce']) && wp_verify_nonce($_POST['cm_edit_profile_nonce'], 'cm_edit_profile_action')) {
    
    // Update basic info
    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $department = sanitize_text_field($_POST['department'] ?? '');
    $year_of_study = sanitize_text_field($_POST['year_of_study'] ?? '');
    $bio = sanitize_textarea_field($_POST['bio'] ?? '');

    // Update display name
    if (!empty($full_name)) {
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $full_name,
            'description' => $bio
        ));
    } else {
        wp_update_user(array(
            'ID' => $user_id,
            'description' => $bio
        ));
    }

    // Update Meta
    update_user_meta($user_id, '_cm_phone', $phone);
    update_user_meta($user_id, '_cm_department', $department);
    update_user_meta($user_id, '_cm_year_of_study', $year_of_study);

    // Profile Photo Upload Component
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attach_id = media_handle_upload('profile_photo', 0);
        if (!is_wp_error($attach_id)) {
            update_user_meta($user_id, '_cm_profile_photo', $attach_id);
        } else {
            $error = $attach_id->get_error_message();
        }
    }

    if (empty($error)) {
        $success = true;
        // Refresh user object to show updated data
        $current_user = wp_get_current_user();
    }
}

$is_verified = cm_is_user_verified($user_id);
// Default fallback
$avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($current_user->display_name) . '&background=1152d4&color=fff&size=150';

// Check for custom uploaded photo
$custom_avatar_id = get_user_meta($user_id, '_cm_profile_photo', true);
if ($custom_avatar_id) {
    $custom_avatar_url = wp_get_attachment_image_url($custom_avatar_id, 'thumbnail');
    if ($custom_avatar_url) {
        $avatar_url = $custom_avatar_url;
    }
} else {
    // Try Gravatar if no custom photo
    $gravatar = get_avatar_url($user_id, array('size' => 150, 'default' => '404'));
    if ($gravatar && strpos($gravatar, 'd=404') === false) {
        $avatar_url = $gravatar;
    }
}

// User data retrieval
$name = $current_user->display_name;
$email = $current_user->user_email;
$phone = get_user_meta($user_id, '_cm_phone', true);
$department = get_user_meta($user_id, '_cm_department', true);
$year_of_study = get_user_meta($user_id, '_cm_year_of_study', true);
$student_id = get_user_meta($user_id, '_cm_student_id', true) ?: 'Pending'; // Placeholder if unknown
$bio = $current_user->description;

$departments = ['Computer Science', 'Engineering', 'Business Administration', 'Arts & Literature', 'Medicine', 'Other'];
$years = ['Freshman (Year 1)', 'Sophomore (Year 2)', 'Junior (Year 3)', 'Senior (Year 4)', 'Graduate Student', 'Other'];

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Edit Profile - <?php bloginfo('name'); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1152d4",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar-glass {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            border-right: 1px solid rgba(17, 82, 212, 0.1);
        }
    </style>
    <?php wp_head(); ?>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="w-72 sidebar-glass flex flex-col h-full shrink-0">
            <div class="p-6 flex items-center gap-3">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3">
                    <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-primary">CampusMarket</h2>
                </a>
            </div>
            <nav class="flex-1 px-4 py-4 space-y-1">
                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-primary/5 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-primary/5 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">storefront</span>
                    <span class="text-sm font-medium">Marketplace</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'listings', home_url('/dashboard/'))); ?>" class="flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-primary/5 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">package_2</span>
                    <span class="text-sm font-medium">My Listings</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'settings', home_url('/edit-profile/'))); ?>" class="flex items-center gap-3 px-4 py-3 <?php echo $active_tab === 'settings' ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/5'; ?> rounded-xl transition-colors">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="text-sm font-medium">Account Settings</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'messages', home_url('/edit-profile/'))); ?>" class="flex items-center gap-3 px-4 py-3 <?php echo $active_tab === 'messages' ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/5'; ?> rounded-xl transition-colors">
                    <span class="material-symbols-outlined">chat_bubble</span>
                    <span class="text-sm font-medium">Messages</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'notifications', home_url('/edit-profile/'))); ?>" class="flex items-center gap-3 px-4 py-3 <?php echo $active_tab === 'notifications' ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/5'; ?> rounded-xl transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="text-sm font-medium">Notifications</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3 p-2">
                    <div class="size-10 rounded-full bg-primary/20 flex items-center justify-center overflow-hidden">
                        <img class="w-full h-full object-cover" alt="Avatar" src="<?php echo esc_url($avatar_url); ?>"/>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-semibold truncate"><?php echo esc_html($name); ?></p>
                        <p class="text-xs text-slate-500 truncate"><?php echo esc_html($department ?: 'Student'); ?></p>
                    </div>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="text-slate-400 hover:text-red-500 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent">
            
            <?php if ($success) : ?>
            <div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 animate-bounce" id="success-notification">
                <div class="glass-card flex items-center gap-4 px-6 py-4 rounded-2xl shadow-2xl border-green-500/20">
                    <div class="size-10 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined font-bold">check_circle</span>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-100">Profile Updated Successfully</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Your changes have been saved.</p>
                    </div>
                    <button class="ml-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" onclick="document.getElementById('success-notification').remove()">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)) : ?>
            <div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 animate-bounce" id="error-notification">
                <div class="glass-card flex items-center gap-4 px-6 py-4 rounded-2xl shadow-2xl border-red-500/20">
                    <div class="size-10 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined font-bold">error</span>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-100">Update Failed</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html($error); ?></p>
                    </div>
                    <button class="ml-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" onclick="document.getElementById('error-notification').remove()">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <header class="h-16 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/40 backdrop-blur-md sticky top-0 z-10">
                <h1 class="text-lg font-semibold">Settings / <?php 
                    echo $active_tab === 'notifications' ? 'Notifications' : ($active_tab === 'messages' ? 'Messages' : 'Edit Profile'); 
                ?></h1>
                <div class="flex items-center gap-4">
                    <button class="size-10 flex items-center justify-center rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                    <button class="size-10 flex items-center justify-center rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600" onclick="document.documentElement.classList.toggle('dark')">
                        <span class="material-symbols-outlined">dark_mode</span>
                    </button>
                </div>
            </header>

            <div class="max-w-4xl mx-auto p-8">
                <?php if ($active_tab === 'settings') : ?>
                    <form method="POST" enctype="multipart/form-data">
                        <?php wp_nonce_field('cm_edit_profile_action', 'cm_edit_profile_nonce'); ?>
                        
                        <div class="glass-card rounded-3xl p-8 shadow-xl shadow-primary/5">
                            <div class="flex flex-col md:flex-row gap-10">
                                <!-- Profile Photo Section -->
                                <div class="flex flex-col items-center gap-4 shrink-0">
                                    <div class="relative group">
                                        <div class="size-36 rounded-full border-4 border-white shadow-lg overflow-hidden ring-4 ring-primary/10">
                                            <img id="avatar-preview" class="w-full h-full object-cover" data-alt="Circular profile photo preview" src="<?php echo esc_url($avatar_url); ?>"/>
                                        </div>
                                        <label class="absolute bottom-1 right-1 size-10 bg-primary text-white rounded-full flex items-center justify-center cursor-pointer hover:scale-105 transition-transform shadow-lg border-2 border-white">
                                            <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                                            <input class="hidden" type="file" name="profile_photo" accept="image/*" onchange="previewAvatar(this)" />
                                        </label>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200"><?php echo esc_html($name); ?></h3>
                                        <p class="text-sm text-primary font-medium">Student Account</p>
                                    </div>
                                </div>

                                <!-- Form Section -->
                                <div class="flex-1 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Full Name</label>
                                            <input name="full_name" value="<?php echo esc_attr($name); ?>" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Alex Johnson" type="text" required/>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Phone Number</label>
                                            <input name="phone" value="<?php echo esc_attr($phone); ?>" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="+1 (555) 000-0000" type="tel"/>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">University Email</label>
                                            <div class="relative">
                                                <input class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 text-slate-500 cursor-not-allowed outline-none transition-all" readonly type="email" value="<?php echo esc_attr($email); ?>"/>
                                                <?php if ($is_verified) : ?>
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                                    <span class="material-symbols-outlined text-[14px]">verified</span> Verified
                                                </div>
                                                <?php else: ?>
                                                <a href="<?php echo esc_url(home_url('/verify/')); ?>" class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1 text-rose-500 hover:text-rose-600 font-bold uppercase tracking-wider text-[10px]">
                                                    Verify
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Student ID</label>
                                            <div class="relative">
                                                <input class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 text-slate-500 cursor-not-allowed outline-none transition-all" readonly type="text" value="<?php echo esc_attr($student_id); ?>"/>
                                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Department</label>
                                            <select name="department" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all appearance-none">
                                                <?php foreach($departments as $dept) : ?>
                                                    <option value="<?php echo esc_attr($dept); ?>" <?php selected($department, $dept); ?>><?php echo esc_html($dept); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Year of Study</label>
                                            <select name="year_of_study" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all appearance-none">
                                                <?php foreach($years as $yr) : ?>
                                                    <option value="<?php echo esc_attr($yr); ?>" <?php selected($year_of_study, $yr); ?>><?php echo esc_html($yr); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Short Bio</label>
                                        <textarea name="bio" class="w-full p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none" placeholder="Tell other students about yourself, what you're selling, or what you're looking for..." rows="4"><?php echo esc_textarea($bio); ?></textarea>
                                    </div>

                                    <!-- Verification Documents Display Box -->
                                    <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Verification Documents</label>
                                            <?php if (!$is_verified) : ?>
                                                <a href="<?php echo esc_url(home_url('/verify/')); ?>" class="text-[10px] bg-primary/10 text-primary font-bold px-3 py-1 rounded-full hover:bg-primary hover:text-white transition-colors uppercase tracking-widest">Update Documents</a>
                                            <?php else: ?>
                                                <span class="text-[10px] bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full uppercase tracking-widest flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">lock</span> Read Only</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <?php 
                                            $id_front = wp_get_attachment_image_url(get_user_meta($user_id, '_cm_id_card_front', true) ?: get_user_meta($user_id, '_cm_id_url', true), 'medium');
                                            $id_back = wp_get_attachment_image_url(get_user_meta($user_id, '_cm_id_card_back', true), 'medium');
                                            ?>
                                            <!-- ID Front -->
                                            <div class="relative rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 h-28 border border-slate-200 dark:border-slate-700 flex items-center justify-center group">
                                                <?php if ($id_front): ?>
                                                    <img src="<?php echo esc_url($id_front); ?>" class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end p-2">
                                                        <span class="text-[10px] text-white font-bold uppercase tracking-widest">Front ID</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center text-slate-400">
                                                        <span class="material-symbols-outlined mb-1">id_card</span>
                                                        <p class="text-[10px] font-bold uppercase tracking-wider">No File</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <!-- ID Back -->
                                            <div class="relative rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 h-28 border border-slate-200 dark:border-slate-700 flex items-center justify-center group">
                                                <?php if ($id_back): ?>
                                                    <img src="<?php echo esc_url($id_back); ?>" class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end p-2">
                                                        <span class="text-[10px] text-white font-bold uppercase tracking-widest">Back ID</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center text-slate-400">
                                                        <span class="material-symbols-outlined mb-1">flip</span>
                                                        <p class="text-[10px] font-bold uppercase tracking-wider">No File</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-6 flex flex-col sm:flex-row items-center gap-4">
                                        <button type="submit" class="w-full sm:w-auto px-8 h-12 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-[0.98]">
                                            Save Changes
                                        </button>
                                        <button type="button" onclick="window.location.href='<?php echo esc_url(home_url('/dashboard/')); ?>'" class="w-full sm:w-auto px-8 h-12 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all active:scale-[0.98]">
                                            Cancel
                                        </button>
                                        <div class="flex-1"></div>
                                        <?php 
                                            if ($success) {
                                                update_user_meta($user_id, '_cm_profile_last_updated', current_time('mysql'));
                                            }
                                            $last_update_meta = get_user_meta($user_id, '_cm_profile_last_updated', true);
                                            if ($last_update_meta) {
                                                echo '<p class="text-xs text-slate-400 italic">Last updated: ' . date('M j, Y', strtotime($last_update_meta)) . '</p>';
                                            } else {
                                                echo '<p class="text-xs text-slate-400 italic">Never updated</p>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Notification Preferences Section (Keeping it here as it belongs to settings) -->
                    <div class="mt-8 glass-card rounded-3xl p-6 border-dashed border-primary/20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="size-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined">security</span>
                                </div>
                                <div>
                                    <h4 class="font-bold">Two-Factor Authentication</h4>
                                    <p class="text-sm text-slate-500">Keep your student account extra secure.</p>
                                </div>
                            </div>
                            <button class="text-primary font-bold text-sm hover:underline">Enable Now</button>
                        </div>
                    </div>
                <?php elseif ($active_tab === 'notifications') : ?>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Notifications</h2>
                            <button class="text-sm text-primary font-bold hover:underline">Mark all as read</button>
                        </div>

                        <div class="glass-card rounded-3xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                            <?php 
                            $notifications = get_posts([
                                'post_type' => 'cm_notification',
                                'posts_per_page' => 20,
                                'meta_query' => [
                                    [
                                        'key' => '_cm_recipient_id',
                                        'value' => get_current_user_id()
                                    ]
                                ]
                            ]);

                            if ($notifications) :
                                foreach ($notifications as $notification) :
                                    $is_read = get_post_meta($notification->ID, '_cm_read', true);
                                    $type = get_post_meta($notification->ID, '_cm_type', true);
                                    ?>
                                    <div class="p-6 flex gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors <?php echo !$is_read ? 'bg-primary/5' : ''; ?>">
                                        <div class="size-12 rounded-2xl flex items-center justify-center shrink-0 <?php 
                                            echo $type === 'success' ? 'bg-green-100 text-green-600' : 
                                                ($type === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-primary/10 text-primary'); 
                                        ?>">
                                            <span class="material-symbols-outlined">
                                                <?php 
                                                echo $type === 'booking' ? 'calendar_today' : 
                                                    ($type === 'message' ? 'chat' : 
                                                    ($type === 'system' ? 'settings' : 'notifications')); 
                                                ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-bold text-slate-800 dark:text-slate-200"><?php echo esc_html($notification->post_title); ?></h4>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?php echo human_time_diff(get_the_time('U', $notification), current_time('U')); ?> ago</span>
                                            </div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo wp_kses_post($notification->post_content); ?></p>
                                        </div>
                                        <?php if (!$is_read) : ?>
                                            <div class="size-2 bg-primary rounded-full mt-2"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <div class="p-12 text-center">
                                    <div class="size-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                                        <span class="material-symbols-outlined text-[40px]">notifications_off</span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-slate-200">No Notifications Yet</h3>
                                    <p class="text-slate-500 dark:text-slate-400 max-w-xs mx-auto text-sm">We'll let you know when something important happens on CampusMarket.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($active_tab === 'messages') : ?>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Messages</h2>
                            <a href="<?php echo esc_url(home_url('/chat/')); ?>" class="text-sm text-primary font-bold hover:underline">Open Full Chat</a>
                        </div>

                        <div class="glass-card rounded-3xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                            <?php 
                            $conversations = cm_get_user_conversations();
                            if ($conversations) :
                                foreach ($conversations as $conv) :
                                    ?>
                                    <a href="<?php echo esc_url(home_url('/chat/?with=' . $conv['other_user_id'])); ?>" class="p-6 flex gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                        <div class="size-14 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden shrink-0 relative">
                                            <img src="<?php echo esc_url($conv['other_user_avatar']); ?>" class="w-full h-full object-cover" />
                                            <?php if ($conv['unread_count'] > 0) : ?>
                                                <div class="absolute -top-1 -right-1 size-5 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900">
                                                    <?php echo $conv['unread_count']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-bold text-slate-800 dark:text-slate-200 truncate"><?php echo esc_html($conv['other_user_name']); ?></h4>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider shrink-0"><?php echo human_time_diff(strtotime($conv['last_date']), current_time('U')); ?> ago</span>
                                            </div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400 truncate pr-4"><?php echo esc_html($conv['last_message']); ?></p>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                                        </div>
                                    </a>
                                <?php endforeach;
                            else : ?>
                                <div class="p-12 text-center">
                                    <div class="size-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                                        <span class="material-symbols-outlined text-[40px]">chat_off</span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-slate-200">No Messages Yet</h3>
                                    <p class="text-slate-500 dark:text-slate-400 max-w-xs mx-auto text-sm">When you message sellers or they message you, your conversations will appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            </div>
        </main>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <?php wp_footer(); ?>
</body>
</html>
