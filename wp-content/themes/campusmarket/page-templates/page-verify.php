<?php
/**
 * Template Name: Student Verification
 * Premium Student Verification Page
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

if (isset($_POST['cm_verify_nonce']) && wp_verify_nonce($_POST['cm_verify_nonce'], 'cm_verify_nonce')) {
    $user_id = get_current_user_id();
    
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

    // Reset verification status
    update_user_meta($user_id, '_cm_verification_status', 'pending');
    update_user_meta($user_id, '_cm_verified', '0');
    delete_user_meta($user_id, '_cm_verification_remarks');
    
    wp_redirect(home_url('/verification-pending/'));
    exit;
}

get_header();
$current_user = wp_get_current_user();
$is_verified = cm_is_user_verified($current_user->ID);
?>

<div class="min-h-[85vh] bg-pattern flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md mx-auto opacity-0 animate-fade-slide-up">
        <div class="text-center mb-8">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary mb-6">
                <span class="material-symbols-outlined text-3xl font-bold">school</span>
                <h2 class="text-2xl font-bold text-slate-900"><?php bloginfo('name'); ?></h2>
            </a>
            <h1 class="text-3xl font-bold mb-2">Student Verification</h1>
            <p class="text-slate-500 text-sm">Boost your trust score by verifying your student identity.</p>
        </div>

        <!-- Progress Bar -->
        <div class="flex items-center gap-4 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold">
                    <span class="material-symbols-outlined text-sm">check</span>
                </div>
                <span class="text-xs font-bold text-green-600">Account</span>
            </div>
            <div class="flex-1 h-[2px] bg-primary"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-primary/40">2</div>
                <span class="text-xs font-bold text-primary">Verify</span>
            </div>
        </div>

        <?php if ($is_verified) : ?>
            <div class="glass-panel rounded-2xl p-8 shadow-lg text-center !transform-none">
                <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-4xl text-green-500">verified</span>
                </div>
                <h2 class="text-2xl font-bold mb-2">Already Verified!</h2>
                <p class="text-slate-500 mb-6">Your student identity has been verified. You have full access to all CampusMarket features.</p>
                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn-premium px-8 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20">Go to Dashboard</a>
            </div>
        <?php else : ?>
            <div class="glass-panel rounded-2xl p-8 shadow-lg !transform-none">
                <form id="cm-verify-form" method="post" enctype="multipart/form-data" class="space-y-6">
                    <?php wp_nonce_field('cm_verify_nonce', 'cm_verify_nonce'); ?>

                    <div class="space-y-4">
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 cursor-pointer h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10" id="profile-photo-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">add_a_photo</span>
                                <p class="text-sm font-semibold text-slate-700">Profile Photo</p>
                                <p class="text-xs text-slate-500 mt-1">Clear face, neutral background</p>
                            </div>
                            <img id="profile-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <input type="file" name="profile_photo" id="profile_photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'profile-preview', 'profile-photo-content')" required />
                        </div>

                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 cursor-pointer h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10" id="id-front-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">id_card</span>
                                <p class="text-sm font-semibold text-slate-700">Front of Student ID</p>
                                <p class="text-xs text-slate-500 mt-1">PNG, JPG up to 5MB</p>
                            </div>
                            <img id="id-front-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <input type="file" name="id_front" id="id_front" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-front-preview', 'id-front-content')" required />
                        </div>
                        
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 cursor-pointer h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10" id="id-back-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">flip</span>
                                <p class="text-sm font-semibold text-slate-700">Back of Student ID</p>
                                <p class="text-xs text-slate-500 mt-1">Ensure all details are readable</p>
                            </div>
                            <img id="id-back-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <input type="file" name="id_back" id="id_back" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-back-preview', 'id-back-content')" required />
                        </div>
                    </div>

                    <div class="bg-primary/5 rounded-lg p-3 flex gap-3">
                        <span class="material-symbols-outlined text-primary text-lg shrink-0">lock</span>
                        <p class="text-xs text-slate-600">Your ID is encrypted and only used for verification. It will be securely deleted after approval.</p>
                    </div>

                    <button type="submit" class="btn-premium w-full py-3.5 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:shadow-primary/40 transition-all text-sm">
                        Submit Documents
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <p class="text-center text-sm text-slate-500 mt-6">
            <a class="text-primary font-medium hover:underline" href="<?php echo esc_url(home_url('/dashboard/')); ?>">← Back to Dashboard</a>
        </p>
    </div>
</div>

<script>
    function previewImage(input, previewId, containerId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var preview = document.getElementById(previewId);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                var container = document.getElementById(containerId);
                if(container) {
                    container.classList.add('hidden');
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php get_footer(); ?>
