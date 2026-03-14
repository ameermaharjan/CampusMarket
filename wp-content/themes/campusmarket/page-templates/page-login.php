<?php
/**
 * Template Name: Login Page
 * Premium Student Login Page
 *
 * @package CampusMarket
 */

// Handle login form submission
$login_error = '';
if (isset($_POST['cm_login']) && isset($_POST['cm_login_nonce'])) {
    if (wp_verify_nonce($_POST['cm_login_nonce'], 'cm_login_nonce')) {
        $creds = array(
            'user_login'    => sanitize_text_field($_POST['user_login']),
            'user_password' => $_POST['user_pass'],
            'remember'      => isset($_POST['remember']),
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            $login_error = $user->get_error_message();
        } else {
            wp_set_current_user($user->ID);
            wp_redirect(home_url('/dashboard/'));
            exit;
        }
    } else {
        $login_error = 'Security check failed. Please try again.';
    }
}

if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

get_header();
?>

<div class="min-h-[85vh] bg-pattern flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md mx-auto opacity-0 animate-fade-slide-up">
        <div class="text-center mb-8">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary mb-6">
                <span class="material-symbols-outlined text-3xl font-bold">school</span>
                <h2 class="text-2xl font-bold text-slate-900"><?php bloginfo('name'); ?></h2>
            </a>
            <h1 class="text-3xl font-bold mb-2">Welcome Back</h1>
            <p class="text-slate-500 text-sm">Sign in to access your campus marketplace.</p>
        </div>

        <div class="glass-panel rounded-2xl p-8 shadow-lg !transform-none">
            <form id="cm-login-form" method="post" action="<?php echo esc_url(home_url('/login/')); ?>" class="space-y-5">
                <?php wp_nonce_field('cm_login_nonce', 'cm_login_nonce'); ?>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username or Email</label>
                    <div class="relative group">
                        <input class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300 group-hover:border-primary/40 pl-10" type="text" name="user_login" required placeholder="Enter username or email">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">person</span>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Password</label>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-xs text-primary hover:underline font-medium">Forgot Password?</a>
                    </div>
                    <div class="relative group">
                        <input class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300 group-hover:border-primary/40 pl-10" type="password" name="user_pass" required placeholder="Enter your password">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="remember" class="text-sm text-slate-500">Remember me</label>
                </div>

                <button type="submit" name="cm_login" class="btn-premium w-full py-3.5 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:shadow-primary/40 transition-all text-sm">
                    Sign In
                </button>

                <div id="cm-login-message" class="text-center">
                    <?php if (! empty($login_error)) : ?>
                        <p class="text-red-500 text-sm"><?php echo esc_html($login_error); ?></p>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            Don't have an account? <a class="text-primary font-bold hover:underline" href="<?php echo esc_url(home_url('/register/')); ?>">Sign Up Free</a>
        </p>
    </div>
</div>

<?php get_footer(); ?>
