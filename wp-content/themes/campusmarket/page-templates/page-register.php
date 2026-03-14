<?php
/**
 * Template Name: Register Page
 * Premium Student Sign Up Page (3-Step)
 *
 * @package CampusMarket
 */

$register_error = '';

if (isset($_POST['cm_register_nonce']) && wp_verify_nonce($_POST['cm_register_nonce'], 'cm_register_nonce')) {
    $user_email   = sanitize_email($_POST['user_email']);
    $department   = sanitize_text_field($_POST['department']);
    $user_pass    = $_POST['user_pass'];

    // Auto-generate username and display name from email
    $parts = explode('@', $user_email);
    $username_base = sanitize_user($parts[0], true);
    $user_login = $username_base;
    $suffix = 1;
    while (username_exists($user_login)) {
        $user_login = $username_base . $suffix;
        $suffix++;
    }
    
    // Default display name to capitalized email prefix
    $display_name = ucwords(str_replace(array('.', '_', '-'), ' ', $parts[0]));

    if (empty($user_email) || empty($user_pass)) {
        $register_error = 'All fields are required.';
    } elseif (strlen($user_pass) < 8) {
        $register_error = 'Password must be at least 8 characters.';
    } elseif (email_exists($user_email)) {
        $register_error = 'An account with that email already exists.';
    } else {
        $user_id = wp_insert_user(array(
            'user_login'   => $user_login,
            'user_email'   => $user_email,
            'user_pass'    => $user_pass,
            'display_name' => $display_name,
            'role'         => 'student',
        ));

        if (is_wp_error($user_id)) {
            $register_error = $user_id->get_error_message();
        } else {
            // Auto-login the new user immediately to allow media uploads
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);

            // Save core details
            update_user_meta($user_id, '_cm_department', $department);
            update_user_meta($user_id, '_cm_verification_status', 'pending');
            update_user_meta($user_id, '_cm_verified', '0');

            // Include WordPress media handling functions
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            // Process Profile Photo
            if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $attach_id = media_handle_upload('profile_photo', 0);
                if (!is_wp_error($attach_id)) {
                    update_user_meta($user_id, '_cm_profile_photo', $attach_id);
                }
            }

            // Process ID Front
            if (!empty($_FILES['id_front']) && $_FILES['id_front']['error'] === 0) {
                $attach_id = media_handle_upload('id_front', 0);
                if (!is_wp_error($attach_id)) {
                    update_user_meta($user_id, '_cm_id_card_front', $attach_id);
                }
            }

            // Process ID Back
            if (!empty($_FILES['id_back']) && $_FILES['id_back']['error'] === 0) {
                $attach_id = media_handle_upload('id_back', 0);
                if (!is_wp_error($attach_id)) {
                    update_user_meta($user_id, '_cm_id_card_back', $attach_id);
                }
            }
            
            wp_redirect(home_url('/verification-pending/'));
            exit;
        }
    }
}

if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

get_header();
?>

<div class="relative flex min-h-[85vh] w-full flex-col bg-pattern">
    <main class="flex-1 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-[580px] glass-card rounded-2xl p-8 shadow-2xl shadow-primary/10">
            
            <?php if (!empty($register_error)) : ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <p class="text-sm font-medium"><?php echo esc_html($register_error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-primary font-bold text-sm tracking-wider uppercase" id="step-indicator">Step 1 of 3</span>
                    <span class="text-slate-500 text-sm font-medium" id="progress-percent">33% Complete</span>
                </div>
                <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500" id="progress-bar" style="width: 33.33%;"></div>
                </div>
            </div>

            <form id="signup-form" method="post" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('cm_register_nonce', 'cm_register_nonce'); ?>

                <!-- Step 1: Basic Details -->
                <div class="step-content active space-y-6" id="step-1">
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Account Details</h1>
                        <p class="text-slate-600 dark:text-slate-400">Let's get started with your university credentials.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">University Email</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">school</span>
                                <input name="user_email" type="email" class="w-full pl-12 pr-4 py-3.5 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-slate-900 dark:text-white" placeholder="alex.j@university.edu" required="" value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Department</label>
                                <div class="relative group">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">business_center</span>
                                    <select name="department" class="w-full pl-12 pr-10 py-3.5 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none appearance-none text-slate-900 dark:text-white" required>
                                        <option value="">Select Dept</option>
                                        <option value="Computer Science">Computer Science</option>
                                        <option value="Business">Business</option>
                                        <option value="Arts">Arts</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Science">Science</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Password</label>
                                <div class="relative group">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">lock</span>
                                    <input name="user_pass" type="password" class="w-full pl-12 pr-4 py-3.5 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-slate-900 dark:text-white" placeholder="Min. 8 chars" required="" minlength="8" />
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 pt-2">
                            <input type="checkbox" id="terms" required="" class="mt-1 size-5 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer"/>
                            <label class="text-sm text-slate-600 dark:text-slate-400" for="terms">
                                I agree to the <a class="text-primary font-semibold hover:underline" href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Service</a> and <a class="text-primary font-semibold hover:underline" href="<?php echo esc_url(home_url('/privacy/')); ?>">Privacy Policy</a>.
                            </label>
                        </div>
                    </div>
                    
                    <button type="button" onclick="nextStep(2)" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2 mt-6">
                        Continue to Photo
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                    
                    <p class="text-center text-sm text-slate-500 mt-6 font-medium">
                        Already have an account? <a href="<?php echo esc_url(home_url('/login/')); ?>" class="text-primary font-bold hover:underline">Log In</a>
                    </p>
                </div>

                <!-- Step 2: Profile Photo -->
                <div class="step-content space-y-6" id="step-2">
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Profile Photo</h1>
                        <p class="text-slate-600 dark:text-slate-400">Help other students recognize you during transactions.</p>
                    </div>
                    <div class="flex flex-col items-center gap-6">
                        <div class="relative group cursor-pointer w-32 h-32" id="profile-photo-container">
                            <div class="absolute inset-0 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-primary group-hover:bg-primary/5">
                                <span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary z-10" id="profile-icon">add_a_photo</span>
                                <img id="profile-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20 rounded-full" />
                            </div>
                            <input type="file" name="profile_photo" id="profile_photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'profile-preview', 'profile-icon')" />
                        </div>
                        <div class="w-full bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Guidelines</h4>
                            <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                                    Clear face, eyes visible
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                                    Neutral background
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                                    Good lighting (no harsh shadows)
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button type="button" onclick="nextStep(1)" class="w-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-4 rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Back
                        </button>
                        <button type="button" onclick="nextStep(3)" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                            Next: ID Verify
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: ID Verification -->
                <div class="step-content space-y-6" id="step-3">
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-2">ID Verification</h1>
                        <p class="text-slate-600 dark:text-slate-400">Secure the marketplace. Upload your institutional ID.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-primary transition-colors bg-white/40 dark:bg-slate-900/40 cursor-pointer h-32 flex items-center justify-center" id="id-front-container">
                            <div class="text-center z-10" id="id-front-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">id_card</span>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Front of Student ID</p>
                                <p class="text-xs text-slate-500 mt-1">PNG, JPG up to 5MB</p>
                            </div>
                            <img id="id-front-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <input type="file" name="id_front" id="id_front" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-front-preview', 'id-front-content')" required />
                        </div>
                        
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-primary transition-colors bg-white/40 dark:bg-slate-900/40 cursor-pointer h-32 flex items-center justify-center" id="id-back-container">
                            <div class="text-center z-10" id="id-back-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">flip</span>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Back of Student ID</p>
                                <p class="text-xs text-slate-500 mt-1">Ensure all details are readable</p>
                            </div>
                            <img id="id-back-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <input type="file" name="id_back" id="id_back" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-back-preview', 'id-back-content')" required />
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-primary/5 rounded-xl border border-primary/10">
                        <span class="material-symbols-outlined text-primary">verified_user</span>
                        <p class="text-xs text-slate-600 dark:text-slate-400">Your data is encrypted and only used for manual identity verification purposes.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button type="button" onclick="nextStep(2)" class="w-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-4 rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Back
                        </button>
                        <button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                            Complete Setup
                            <span class="material-symbols-outlined">verified</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .dark .glass-card {
        background: rgba(15, 23, 42, 0.7);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .bg-pattern {
        background-color: #f6f6f8;
        background-image: radial-gradient(at 0% 0%, rgba(17, 82, 212, 0.15) 0px, transparent 50%),
                          radial-gradient(at 100% 100%, rgba(17, 82, 212, 0.1) 0px, transparent 50%);
    }
    .dark .bg-pattern {
        background-color: #101622;
        background-image: radial-gradient(at 0% 0%, rgba(17, 82, 212, 0.15) 0px, transparent 50%),
                          radial-gradient(at 100% 100%, rgba(17, 82, 212, 0.1) 0px, transparent 50%);
    }
    .step-content { display: none; }
    .step-content.active { display: block; animation: fadeInUp 0.4s ease-out forwards; }
    
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function nextStep(step) {
        // Validate Step 1 before moving to 2
        if (step === 2) {
            const form = document.getElementById('signup-form');
            const email = form.user_email.value;
            const dept = form.department.value;
            const pass = form.user_pass.value;
            const terms = document.getElementById('terms').checked;
            
            if (!email || !dept || !pass || !terms) {
                // Since we added novalidate, we need to show some feedback here
                alert('Please fill in all required fields and accept the Terms of Service.');
                
                // Trigger native-like reporting for those that are still there but visible
                form.reportValidity();
                return;
            }
            
            // Basic email validation
            if (!email.includes('@')) {
                alert('Please enter a valid university email.');
                return;
            }
        }
        
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
        
        // Show current step
        document.getElementById('step-' + step).classList.add('active');
        
        // Update progress UI
        const indicator = document.getElementById('step-indicator');
        const bar = document.getElementById('progress-bar');
        const percent = document.getElementById('progress-percent');

        indicator.innerText = `Step ${step} of 3`;
        
        const percentage = (step / 3 * 100);
        bar.style.width = percentage + '%';
        percent.innerText = Math.round(percentage) + '% Complete';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function previewImage(input, previewId, iconContainerId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var preview = document.getElementById(previewId);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                var iconContainer = document.getElementById(iconContainerId);
                if(iconContainer) {
                    iconContainer.classList.add('hidden');
                }
            }
            
            reader.readAsDataURL(input.files[0]); // convert to base64 string
        }
    }

    // Explicit validation for Step 3 because hidden required fields block browser balloon notices
    document.getElementById('signup-form').addEventListener('submit', function(e) {
        const step3 = document.getElementById('step-3');
        if (step3.classList.contains('active')) {
            const idFront = document.getElementById('id_front').files.length;
            const idBack = document.getElementById('id_back').files.length;
            
            if (idFront === 0 || idBack === 0) {
                e.preventDefault();
                alert('Please upload both Front and Back images of your Student ID to complete registration.');
                
                // Highlight the containers
                if(idFront === 0) document.getElementById('id-front-container').classList.add('border-red-500', 'bg-red-50');
                if(idBack === 0) document.getElementById('id-back-container').classList.add('border-red-500', 'bg-red-50');
            }
        }
    });

    // Reset red borders when files selected
    document.getElementById('id_front').addEventListener('change', function() {
        document.getElementById('id-front-container').classList.remove('border-red-500', 'bg-red-50');
    });
    document.getElementById('id_back').addEventListener('change', function() {
        document.getElementById('id-back-container').classList.remove('border-red-500', 'bg-red-50');
    });
</script>

<?php get_footer(); ?>
