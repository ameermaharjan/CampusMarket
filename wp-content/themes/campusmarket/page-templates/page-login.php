<?php

/**
 * Template Name: Login
 *
 * Custom login page matching theme design.
 *
 * @package CampusMarket
 */

// If already logged in, redirect to dashboard
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

// Process login
$errors   = array();
$redirect = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/dashboard/');

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['cm_login_nonce'])) {
    if (! wp_verify_nonce($_POST['cm_login_nonce'], 'cm_login_action')) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        $username = sanitize_user(wp_unslash($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errors[] = 'Username and password are required.';
        } else {
            $user = wp_signon(array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => ! empty($_POST['remember']),
            ));

            if (is_wp_error($user)) {
                $errors[] = 'Invalid username or password.';
            } else {
                wp_redirect($redirect);
                exit;
            }
        }
    }
}

get_header();
?>

<div class="cm-section">
    <div class="cm-container cm-container--narrow">
        <div class="cm-auth-card">
            <div class="cm-auth-card__header">
                <div class="cm-auth-card__icon">🔐</div>
                <h1 class="cm-auth-card__title">Welcome Back</h1>
                <p class="cm-auth-card__subtitle">Log in to your CampusMarket account</p>
            </div>

            <?php if (! empty($errors)) : ?>
                <div class="cm-alert cm-alert--error">
                    <?php foreach ($errors as $error) : ?>
                        <p><?php echo esc_html($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])) : ?>
                <div class="cm-alert cm-alert--success">
                    <p>Account created successfully! Please log in.</p>
                </div>
            <?php endif; ?>

            <form method="post" class="cm-form" id="cm-login-form">
                <?php wp_nonce_field('cm_login_action', 'cm_login_nonce'); ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect); ?>">

                <div class="cm-form-group">
                    <label for="cm-login-username" class="cm-form-label">Username or Email</label>
                    <input type="text" id="cm-login-username" name="username" class="cm-input"
                        value="<?php echo esc_attr($_POST['username'] ?? ''); ?>"
                        placeholder="Enter your username" required autofocus>
                </div>

                <div class="cm-form-group">
                    <label for="cm-login-password" class="cm-form-label">Password</label>
                    <input type="password" id="cm-login-password" name="password" class="cm-input"
                        placeholder="Enter your password" required>
                </div>

                <div class="cm-form-group cm-flex cm-flex--between">
                    <label class="cm-checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="cm-link cm-text-sm">Forgot Password?</a>
                </div>

                <div class="cm-form-group cm-form-group--submit">
                    <button type="submit" class="cm-btn cm-btn--primary cm-btn--lg cm-btn--block">
                        Log In
                    </button>
                </div>

                <p class="cm-auth-card__footer-text">
                    Don't have an account?
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="cm-link">Sign Up</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php
get_footer();
