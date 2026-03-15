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

$errors = array();

if (isset($_POST['cm_verify_nonce']) && wp_verify_nonce($_POST['cm_verify_nonce'], 'cm_verify_nonce')) {
    $user_id = get_current_user_id();
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    /**
     * Helper to process base64 image
     */
    function cm_handle_captured_image($base64_data, $filename) {
        if (!$base64_data) return false;
        $data = explode(',', $base64_data);
        if (count($data) < 2) return false;
        
        $decoded_data = base64_decode($data[1]);
        $upload = wp_upload_bits($filename, null, $decoded_data);
        
        if ($upload['error']) return new WP_Error('upload_error', $upload['error']);
        
        $file_path = $upload['file'];
        $file_type = wp_check_filetype(basename($file_path), null);
        
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name(basename($file_path)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment, $file_path);
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return $attach_id;
    }

    $uploaded_profile = 0;
    $uploaded_front = 0;
    $uploaded_back = 0;

    // Process Profile Photo (File or Captured)
    if (!empty($_POST['captured_profile'])) {
        $attach_id = cm_handle_captured_image($_POST['captured_profile'], 'profile_captured_' . $user_id . '.jpg');
        if (is_wp_error($attach_id)) {
            $errors[] = __('Profile Capture Error: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_profile_photo', $attach_id);
            update_user_meta($user_id, '_cm_verified_identity_photo', $attach_id); // Archive it
            $uploaded_profile = $attach_id;
        }
    } elseif (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $attach_id = media_handle_upload('profile_photo', 0);
        if (is_wp_error($attach_id)) {
            $errors[] = __('Profile Photo: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_profile_photo', $attach_id);
            update_user_meta($user_id, '_cm_verified_identity_photo', $attach_id); // Archive it
            $uploaded_profile = $attach_id;
        }
    } else if (empty($_FILES['profile_photo']['name']) && !get_user_meta($user_id, '_cm_profile_photo', true)) {
        $errors[] = __('Profile Photo is required.', 'campusmarket');
    }

    // Process ID Front (File or Captured)
    if (!empty($_POST['captured_front'])) {
        $attach_id = cm_handle_captured_image($_POST['captured_front'], 'id_front_captured_' . $user_id . '.jpg');
        if (is_wp_error($attach_id)) {
            $errors[] = __('ID Front Capture Error: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_id_card_front', $attach_id);
            $uploaded_front = $attach_id;
        }
    } elseif (!empty($_FILES['id_front']) && $_FILES['id_front']['error'] === 0) {
        $attach_id = media_handle_upload('id_front', 0);
        if (is_wp_error($attach_id)) {
            $errors[] = __('ID Front: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_id_card_front', $attach_id);
            $uploaded_front = $attach_id;
        }
    } else if (empty($_FILES['id_front']['name']) && !get_user_meta($user_id, '_cm_id_card_front', true)) {
        $errors[] = __('Student ID (Front) is required.', 'campusmarket');
    }

    // Process ID Back (File or Captured)
    if (!empty($_POST['captured_back'])) {
        $attach_id = cm_handle_captured_image($_POST['captured_back'], 'id_back_captured_' . $user_id . '.jpg');
        if (is_wp_error($attach_id)) {
            $errors[] = __('ID Back Capture Error: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_id_card_back', $attach_id);
            $uploaded_back = $attach_id;
        }
    } elseif (!empty($_FILES['id_back']) && $_FILES['id_back']['error'] === 0) {
        $attach_id = media_handle_upload('id_back', 0);
        if (is_wp_error($attach_id)) {
            $errors[] = __('ID Back: ', 'campusmarket') . $attach_id->get_error_message();
        } else {
            update_user_meta($user_id, '_cm_id_card_back', $attach_id);
            $uploaded_back = $attach_id;
        }
    } else if (empty($_FILES['id_back']['name']) && !get_user_meta($user_id, '_cm_id_card_back', true)) {
        $errors[] = __('Student ID (Back) is required.', 'campusmarket');
    }

    if (empty($errors)) {
        update_user_meta($user_id, '_cm_verification_status', 'pending');
        update_user_meta($user_id, '_cm_verified', '0');
        delete_user_meta($user_id, '_cm_verification_remarks');
        
        wp_redirect(home_url('/verification-pending/'));
        exit;
    }
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

        <?php if (!empty($errors)) : ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl animate-shake">
                <div class="flex gap-3 text-rose-600">
                    <span class="material-symbols-outlined shrink-0">error</span>
                    <div>
                        <p class="text-sm font-bold"><?php _e('Submission Failed', 'campusmarket'); ?></p>
                        <ul class="text-xs mt-1 list-disc list-inside opacity-80">
                            <?php foreach($errors as $err) echo "<li>" . esc_html($err) . "</li>"; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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

                    <?php 
                    $existing_profile = get_user_meta($current_user->ID, '_cm_profile_photo', true);
                    $existing_front = get_user_meta($current_user->ID, '_cm_id_card_front', true);
                    $existing_back = get_user_meta($current_user->ID, '_cm_id_card_back', true);
                    ?>

                    <div class="space-y-4">
                        <!-- Profile Photo -->
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10 <?php echo $existing_profile ? 'hidden' : ''; ?>" id="profile-photo-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">add_a_photo</span>
                                <p class="text-sm font-semibold text-slate-700">Profile Photo</p>
                                <button type="button" onclick="openCamera('profile')" class="text-[10px] text-primary font-bold mt-1 bg-primary/5 px-2 py-0.5 rounded border border-primary/20 hover:bg-primary/10 transition-colors uppercase">Click Live Photo</button>
                            </div>
                            <?php if ($existing_profile) : ?>
                                <img id="profile-preview" src="<?php echo esc_url(wp_get_attachment_image_url($existing_profile, 'large')); ?>" class="absolute inset-0 w-full h-full object-cover z-20" />
                            <?php else : ?>
                                <img id="profile-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <?php endif; ?>
                            <input type="file" name="profile_photo" id="profile_photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'profile-preview', 'profile-photo-content')" <?php echo $existing_profile ? '' : 'required'; ?> />
                            <input type="hidden" name="captured_profile" id="captured_profile">
                        </div>

                        <!-- ID Front -->
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10 <?php echo $existing_front ? 'hidden' : ''; ?>" id="id-front-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">id_card</span>
                                <p class="text-sm font-semibold text-slate-700">Front of ID</p>
                                <button type="button" onclick="openCamera('front')" class="text-[10px] text-primary font-bold mt-1 bg-primary/5 px-2 py-0.5 rounded border border-primary/20 hover:bg-primary/10 transition-colors uppercase">Take Snap</button>
                            </div>
                            <?php if ($existing_front) : ?>
                                <img id="id-front-preview" src="<?php echo esc_url(wp_get_attachment_image_url($existing_front, 'large')); ?>" class="absolute inset-0 w-full h-full object-cover z-20" />
                            <?php else : ?>
                                <img id="id-front-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <?php endif; ?>
                            <input type="file" name="id_front" id="id_front" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-front-preview', 'id-front-content')" <?php echo $existing_front ? '' : 'required'; ?> />
                            <input type="hidden" name="captured_front" id="captured_front">
                        </div>
                        
                        <!-- ID Back -->
                        <div class="relative rounded-xl overflow-hidden shadow-sm group border-2 border-dashed border-slate-300 hover:border-primary transition-colors bg-slate-50 h-32 flex items-center justify-center p-4">
                            <div class="text-center z-10 <?php echo $existing_back ? 'hidden' : ''; ?>" id="id-back-content">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary mb-2">flip</span>
                                <p class="text-sm font-semibold text-slate-700">Back of ID</p>
                                <button type="button" onclick="openCamera('back')" class="text-[10px] text-primary font-bold mt-1 bg-primary/5 px-2 py-0.5 rounded border border-primary/20 hover:bg-primary/10 transition-colors uppercase">Take Snap</button>
                            </div>
                            <?php if ($existing_back) : ?>
                                <img id="id-back-preview" src="<?php echo esc_url(wp_get_attachment_image_url($existing_back, 'large')); ?>" class="absolute inset-0 w-full h-full object-cover z-20" />
                            <?php else : ?>
                                <img id="id-back-preview" class="absolute inset-0 w-full h-full object-cover hidden z-20" />
                            <?php endif; ?>
                            <input type="file" name="id_back" id="id_back" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" accept="image/*" onchange="previewImage(this, 'id-back-preview', 'id-back-content')" <?php echo $existing_back ? '' : 'required'; ?> />
                            <input type="hidden" name="captured_back" id="captured_back">
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

<!-- Camera Modal -->
<div id="cm-camera-modal" class="fixed inset-0 z-[100] bg-slate-900/90 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl overflow-hidden w-full max-w-md shadow-2xl animate-fade-slide-up">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">photo_camera</span>
                Capture Identity
            </h3>
            <button type="button" onclick="closeCamera()" class="text-slate-400 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="relative bg-black aspect-square flex items-center justify-center">
            <video id="camera-stream" autoplay playsinline class="w-full h-full object-cover"></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
            
            <!-- Overlay Guide -->
            <div class="absolute inset-0 border-2 border-white/20 pointer-events-none">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div id="camera-guide" class="w-3/4 h-3/4 border-2 border-dashed border-primary/50 rounded-2xl"></div>
                </div>
            </div>
        </div>
        <div class="p-6 flex gap-4">
            <button type="button" onclick="closeCamera()" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button type="button" onclick="takeSnapshot()" class="flex-1 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">Capture Now</button>
        </div>
    </div>
</div>

<script>
    let currentCaptureType = '';
    let stream = null;

    async function openCamera(type) {
        currentCaptureType = type;
        const modal = document.getElementById('cm-camera-modal');
        const video = document.getElementById('camera-stream');
        const guide = document.getElementById('camera-guide');

        // Adjust guide based on type
        if (type === 'profile') {
            guide.style.borderRadius = '50%';
        } else {
            guide.style.borderRadius = '1rem';
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: "environment" }, 
                audio: false 
            });
            video.srcObject = stream;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } catch (err) {
            alert('Camera access denied or not available.');
            console.error(err);
        }
    }

    function closeCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('cm-camera-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function takeSnapshot() {
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('camera-canvas');
        const context = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        
        let previewId, contentId, hiddenId;
        if (currentCaptureType === 'profile') {
            previewId = 'profile-preview';
            contentId = 'profile-photo-content';
            hiddenId = 'captured_profile';
        } else if (currentCaptureType === 'front') {
            previewId = 'id-front-preview';
            contentId = 'id-front-content';
            hiddenId = 'captured_front';
        } else {
            previewId = 'id-back-preview';
            contentId = 'id-back-content';
            hiddenId = 'captured_back';
        }

        const preview = document.getElementById(previewId);
        preview.src = dataUrl;
        preview.classList.remove('hidden');
        
        const content = document.getElementById(contentId);
        if(content) content.classList.add('hidden');

        document.getElementById(hiddenId).value = dataUrl;
        
        // Remove 'required' from file input if we have a capture
        const fileInput = document.getElementById(currentCaptureType === 'profile' ? 'profile_photo' : (currentCaptureType === 'front' ? 'id_front' : 'id_back'));
        if(fileInput) fileInput.removeAttribute('required');

        closeCamera();
    }

    function previewImage(input, previewId, containerId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var preview = document.getElementById(previewId);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                var container = document.getElementById(containerId);
                if(container) container.classList.add('hidden');

                // Clear captured hidden input if file is chosen
                let hiddenId = '';
                if (previewId === 'profile-preview') hiddenId = 'captured_profile';
                else if (previewId === 'id-front-preview') hiddenId = 'captured_front';
                else if (previewId === 'id-back-preview') hiddenId = 'captured_back';
                if(hiddenId) document.getElementById(hiddenId).value = '';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php get_footer(); ?>
