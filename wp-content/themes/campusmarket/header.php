<?php
/**
 * Header template — Premium Redesign
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script>
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
                        "DEFAULT": "1rem",
                        "lg": "1.5rem",
                        "xl": "2rem",
                        "full": "9999px"
                    },
                    transitionTimingFunction: {
                        'premium': 'cubic-bezier(0.23, 1, 0.32, 1)',
                    },
                    keyframes: {
                        fadeSlideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
                            '100%': { transform: 'translateY(0px)' },
                        }
                    },
                    animation: {
                        'fade-slide-up': 'fadeSlideUp 0.8s cubic-bezier(0.23, 1, 0.32, 1) forwards',
                        'float': 'float 4s ease-in-out infinite',
                    }
                },
            },
        }
    </script>

    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .glass-card:hover {
            transform: translateY(-4px) scale(1.02);
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 40px -15px rgba(17, 82, 212, 0.15);
            backdrop-filter: blur(16px);
            border-color: rgba(17, 82, 212, 0.3);
        }
        .listing-card {
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .listing-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 40px -15px rgba(17, 82, 212, 0.15);
            border-color: rgba(17, 82, 212, 0.3);
        }
        .mesh-gradient {
            background-color: #f6f6f8;
            background-image:
                radial-gradient(at 0% 0%, rgba(17, 82, 212, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(17, 82, 212, 0.1) 0px, transparent 50%);
        }
        .hover-glow:hover {
            box-shadow: 0 0 20px -5px rgba(17, 82, 212, 0.3);
        }
        .btn-premium {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-premium:hover {
            transform: translateY(-1px);
            filter: brightness(110%);
        }
        .btn-premium:active {
            transform: translateY(0) scale(0.98);
        }
        .action-button {
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .action-button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(17, 82, 212, 0.3);
        }
        .action-button:active {
            transform: scale(0.95);
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .animate-fade-slide-up {
            animation: fadeSlideUp 0.8s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }
        .stagger-6 { animation-delay: 0.6s; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(17, 82, 212, 0.2); border-radius: 10px; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .bg-pattern {
            background-color: #f6f6f8;
            background-image: radial-gradient(at 0% 0%, rgba(17, 82, 212, 0.15) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(17, 82, 212, 0.1) 0px, transparent 50%);
        }
    </style>

    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-background-light font-display text-slate-900 min-h-screen'); ?>>
    <?php wp_body_open(); ?>

    <a class="screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'campusmarket'); ?></a>

    <header class="sticky top-0 z-50 glass-effect border-b border-slate-200 px-4 md:px-10 py-3" id="cm-header">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">

            <!-- Logo -->
            <div class="flex items-center gap-8">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 text-primary group">
                    <span class="material-symbols-outlined text-3xl font-bold transition-transform duration-500 group-hover:rotate-12">school</span>
                    <h2 class="text-slate-900 text-xl font-bold tracking-tight"><?php bloginfo('name'); ?></h2>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center gap-6" aria-label="<?php esc_attr_e('Primary Navigation', 'campusmarket'); ?>">
                    <a class="text-sm font-medium hover:text-primary transition-colors duration-300" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">Browse</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/browse/?listing_intent=sale')); ?>">Buy</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/browse/?listing_intent=rent')); ?>">Rentals</a>
                    <?php if (is_user_logged_in()) : ?>
                        <a class="text-sm font-medium hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/list-item/')); ?>">Sell</a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-md">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 group-focus-within:text-primary transition-colors duration-300">search</span>
                    </div>
                    <input
                        class="block w-full pl-10 pr-3 py-2 border-none bg-slate-100 rounded-lg focus:ring-2 focus:ring-primary/50 text-sm placeholder-slate-500 transition-all duration-300"
                        type="search"
                        name="s"
                        placeholder="Search textbooks, tech, furniture..."
                        value="<?php echo get_search_query(); ?>"
                    >
                    <input type="hidden" name="post_type" value="cm_listing">
                </form>
            </div>

            <!-- User Actions -->
            <div class="flex items-center gap-3">
                <?php if (is_user_logged_in()) : ?>
                    <?php $current_user = wp_get_current_user(); ?>

                    <!-- Notifications -->
                    <?php
                    $unread_notifications_query = cm_get_unread_notifications($current_user->ID);
                    $unread_count = $unread_notifications_query->found_posts;
                    ?>
                    <div class="relative" id="cm-notification-menu">
                        <button class="relative p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-primary/10 hover:text-primary transition-all duration-300" id="cm-notification-toggle">
                            <span class="material-symbols-outlined">notifications</span>
                            <?php if ($unread_count > 0) : ?>
                                <span class="absolute top-1 right-1 flex h-3 w-3 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm shadow-red-500/50" id="cm-notification-badge"><?php echo $unread_count > 9 ? '9+' : esc_html($unread_count); ?></span>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div class="absolute top-full right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl hidden z-50 overflow-hidden" id="cm-notification-dropdown">
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                                <?php if ($unread_count > 0) : ?>
                                    <button class="text-xs text-primary font-medium hover:underline" id="cm-mark-all-read">Mark all as read</button>
                                <?php endif; ?>
                            </div>
                            <div class="max-h-[300px] overflow-y-auto custom-scrollbar" id="cm-notifications-list">
                                <?php if ($unread_count > 0) : ?>
                                    <?php while ($unread_notifications_query->have_posts()) : $unread_notifications_query->the_post(); 
                                        $n_link = get_post_meta(get_the_ID(), '_cm_notification_link', true);
                                    ?>
                                        <a href="<?php echo $n_link ? esc_url($n_link) : '#'; ?>" class="block px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cm-notification-item" data-id="<?php echo get_the_ID(); ?>">
                                            <p class="text-sm text-slate-800 font-medium line-clamp-2"><?php echo esc_html(get_the_content()); ?></p>
                                            <p class="text-xs text-slate-500 mt-1"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> ago</p>
                                        </a>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                <?php else : ?>
                                    <div class="px-4 py-8 text-center" id="cm-no-notifications">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">notifications_off</span>
                                        <p class="text-sm text-slate-500 font-medium">No new notifications</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="block w-full text-center px-4 py-2 border-t border-slate-100 text-xs font-bold text-primary hover:bg-primary/5 transition-colors">
                                View Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Messages -->
                    <a href="<?php echo esc_url(home_url('/chat/')); ?>" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-primary/10 hover:text-primary transition-all duration-300">
                        <span class="material-symbols-outlined">chat_bubble</span>
                    </a>

                    <!-- List Item Button -->
                    <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="hidden md:inline-flex items-center gap-1 px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 transition-all duration-300 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">add</span> List Item
                    </a>

                    <!-- User Avatar Dropdown -->
                    <div class="relative" id="cm-user-menu">
                        <button class="h-10 w-10 rounded-full bg-primary/20 border-2 border-primary/30 overflow-hidden cursor-pointer hover:scale-105 transition-transform duration-300" id="cm-user-toggle" aria-expanded="false">
                            <?php echo get_avatar($current_user->ID, 40, '', '', array('class' => 'w-full h-full object-cover')); ?>
                        </button>
                        <div class="absolute top-full right-0 mt-2 min-w-[200px] bg-white border border-slate-200 rounded-xl shadow-xl p-2 hidden z-50" id="cm-user-dropdown">
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-sm font-bold text-slate-900"><?php echo esc_html($current_user->display_name); ?></p>
                                <p class="text-xs text-slate-500"><?php echo esc_html($current_user->user_email); ?></p>
                            </div>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                <span class="material-symbols-outlined text-lg">dashboard</span> Dashboard
                            </a>
                            <a href="<?php echo esc_url(home_url('/chat/')); ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                <span class="material-symbols-outlined text-lg">chat</span> Messages
                            </a>
                            <?php if (current_user_can('manage_options')) : ?>
                                <a href="<?php echo esc_url(home_url('/admin-panel/')); ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                    <span class="material-symbols-outlined text-lg">admin_panel_settings</span> Admin Panel
                                </a>
                            <?php endif; ?>
                            <hr class="my-1 border-slate-100">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                                <span class="material-symbols-outlined text-lg">logout</span> Sign Out
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="text-sm font-medium hover:text-primary transition-colors">Log In</a>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 duration-300">
                        Join Now
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button class="lg:hidden p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-primary/10 hover:text-primary transition-all duration-300" id="cm-hamburger" aria-label="<?php esc_attr_e('Toggle Menu', 'campusmarket'); ?>">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
    </header>

    <main id="main-content">
<script>
// User dropdown toggle
(function(){
    var toggle = document.getElementById('cm-user-toggle');
    var dropdown = document.getElementById('cm-user-dropdown');
    var notifToggle = document.getElementById('cm-notification-toggle');
    var notifDropdown = document.getElementById('cm-notification-dropdown');

    if(toggle && dropdown) {
        toggle.addEventListener('click', function(e){
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            if(notifDropdown && !notifDropdown.classList.contains('hidden')) {
                notifDropdown.classList.add('hidden');
            }
        });
    }

    if(notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', function(e){
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
            if(dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });
    }

    document.addEventListener('click', function(){
        if(dropdown) dropdown.classList.add('hidden');
        if(notifDropdown) notifDropdown.classList.add('hidden');
    });
})();
</script>
