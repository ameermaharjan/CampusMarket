<?php

/**
 * Template Name: Register
 *
 * Custom registration page with password, student ID, department, phone fields.
 *
 * @package CampusMarket
 */

// If already logged in, redirect to dashboard
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

// Process registration
$errors  = array();
$success = false;

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['cm_register_nonce'])) {
    // Verify nonce
    if (! wp_verify_nonce($_POST['cm_register_nonce'], 'cm_register_action')) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        $username    = sanitize_user(wp_unslash($_POST['username'] ?? ''));
        $email       = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $password    = $_POST['password'] ?? '';
        $password2   = $_POST['password_confirm'] ?? '';
        $full_name   = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
        $student_id  = sanitize_text_field(wp_unslash($_POST['student_id'] ?? ''));
        $department  = sanitize_text_field(wp_unslash($_POST['department'] ?? ''));
        $phone       = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));

        // Validate
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        } elseif (username_exists($username)) {
            $errors[] = 'This username is already taken.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (! is_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (email_exists($email)) {
            $errors[] = 'An account with this email already exists.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } elseif ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        }

        // Create user if no errors
        if (empty($errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $errors[] = $user_id->get_error_message();
            } else {
                // Set display name
                wp_update_user(array(
                    'ID'           => $user_id,
                    'display_name' => $full_name,
                    'first_name'   => $full_name,
                ));

                // Set role to student
                $user = new WP_User($user_id);
                $user->set_role('student');

                // Save student meta
                if (! empty($student_id)) {
                    update_user_meta($user_id, '_cm_student_id', $student_id);
                }
                if (! empty($department)) {
                    update_user_meta($user_id, '_cm_department', $department);
                }
                if (! empty($phone)) {
                    update_user_meta($user_id, '_cm_phone', $phone);
                }
                update_user_meta($user_id, '_cm_verified', '0');

                // Auto-login after registration
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id, true);
                wp_redirect(home_url('/dashboard/'));
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
                <div class="cm-auth-card__icon">🎓</div>
                <h1 class="cm-auth-card__title">Join CampusMarket</h1>
                <p class="cm-auth-card__subtitle">Create your student account to start renting, sharing, and connecting.</p>
            </div>

            <?php if (! empty($errors)) : ?>
                <div class="cm-alert cm-alert--error">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="cm-form" id="cm-register-form" autocomplete="off">
                <?php wp_nonce_field('cm_register_action', 'cm_register_nonce'); ?>

                <!-- Full Name -->
                <div class="cm-form-group">
                    <label for="cm-reg-fullname" class="cm-form-label">Full Name <span class="cm-required">*</span></label>
                    <input type="text" id="cm-reg-fullname" name="full_name" class="cm-input"
                        value="<?php echo esc_attr($_POST['full_name'] ?? ''); ?>"
                        placeholder="e.g., Ameer Maharjan" required>
                </div>

                <!-- Username -->
                <div class="cm-form-group">
                    <label for="cm-reg-username" class="cm-form-label">Username <span class="cm-required">*</span></label>
                    <input type="text" id="cm-reg-username" name="username" class="cm-input"
                        value="<?php echo esc_attr($_POST['username'] ?? ''); ?>"
                        placeholder="Choose a username" required minlength="3">
                </div>

                <!-- Email -->
                <div class="cm-form-group">
                    <label for="cm-reg-email" class="cm-form-label">Email Address <span class="cm-required">*</span></label>
                    <input type="email" id="cm-reg-email" name="email" class="cm-input"
                        value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                        placeholder="your.email@college.edu" required>
                </div>

                <!-- Password -->
                <div class="cm-form-row">
                    <div class="cm-form-group">
                        <label for="cm-reg-password" class="cm-form-label">Password <span class="cm-required">*</span></label>
                        <input type="password" id="cm-reg-password" name="password" class="cm-input"
                            placeholder="Min. 6 characters" required minlength="6">
                    </div>
                    <div class="cm-form-group">
                        <label for="cm-reg-password2" class="cm-form-label">Confirm Password <span class="cm-required">*</span></label>
                        <input type="password" id="cm-reg-password2" name="password_confirm" class="cm-input"
                            placeholder="Re-enter password" required minlength="6">
                    </div>
                </div>

                <hr class="cm-form-divider">
                <p class="cm-form-section-label">Student Information <span class="cm-text-muted">(optional — can add later)</span></p>

                <!-- Student ID -->
                <div class="cm-form-group">
                    <label for="cm-reg-student-id" class="cm-form-label">Student ID</label>
                    <input type="text" id="cm-reg-student-id" name="student_id" class="cm-input"
                        value="<?php echo esc_attr($_POST['student_id'] ?? ''); ?>"
                        placeholder="e.g., STU2025001">
                </div>

                <!-- Department & Phone -->
                <div class="cm-form-row">
                    <div class="cm-form-group">
                        <label for="cm-reg-department" class="cm-form-label">Department</label>
                        <input type="text" id="cm-reg-department" name="department" class="cm-input"
                            value="<?php echo esc_attr($_POST['department'] ?? ''); ?>"
                            placeholder="e.g., Computer Science">
                    </div>
                    <div class="cm-form-group">
                        <label for="cm-reg-phone" class="cm-form-label">Phone Number</label>
                        <input type="tel" id="cm-reg-phone" name="phone" class="cm-input"
                            value="<?php echo esc_attr($_POST['phone'] ?? ''); ?>"
                            placeholder="e.g., 9800000000">
                    </div>
                </div>

                <!-- Submit -->
                <div class="cm-form-group cm-form-group--submit">
                    <button type="submit" class="cm-btn cm-btn--primary cm-btn--lg cm-btn--block">
                        🎓 Create Account
                    </button>
                </div>

                <p class="cm-auth-card__footer-text">
                    Already have an account?
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="cm-link">Log In</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php
get_footer();
