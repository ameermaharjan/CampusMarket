<?php
/**
 * Template Name: Verification Pending Page
 *
 * @package CampusMarket
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

$user = wp_get_current_user();
$status = get_user_meta($user->ID, '_cm_verification_status', true);

// If already approved, no need to be here
if ($status === 'approved' || get_user_meta($user->ID, '_cm_verified', true) === '1') {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

get_header();
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden bg-background-light dark:bg-background-dark">
<div class="layout-container flex h-full grow flex-col">
<main class="flex flex-1 justify-center py-10 px-6 md:px-20">
<div class="layout-content-container flex flex-col max-w-[800px] flex-1">
<?php if ($status === 'rejected') : 
    $remarks = get_user_meta($user->ID, '_cm_verification_remarks', true);
?>
    <div class="flex flex-col gap-4 text-center mb-10 animate-fade-in-up">
        <div class="inline-flex items-center self-center gap-2 px-4 py-1.5 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 text-sm font-semibold">
            <span class="material-symbols-outlined text-[18px]">cancel</span>
            Verification Rejected
        </div>
        <h1 class="text-slate-900 dark:text-slate-100 text-4xl md:text-5xl font-black leading-tight tracking-tight">Your ID wasn't approved</h1>
        <p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl mx-auto">
            Unfortunately, we couldn't verify your identity with the provided documents. Please review the reason below and submit new documents.
        </p>
    </div>
    
    <div class="bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800 rounded-2xl p-8 mb-10 animate-fade-in-up stagger-1">
        <h3 class="font-bold text-rose-900 dark:text-rose-100 text-lg mb-2">Reason for rejection:</h3>
        <p class="text-rose-700 dark:text-rose-300"><?php echo esc_html($remarks ?: 'Documents were blurry or did not meet requirements.'); ?></p>
    </div>

    <div class="w-full sm:w-auto text-center mt-4 mb-20 animate-fade-in-up stagger-2">
        <p class="text-slate-500 mb-6">You can resubmit your documents to get verified and access the marketplace.</p>
        <a href="<?php echo esc_url(home_url('/verify/')); ?>" class="inline-block px-10 py-4 bg-primary text-white rounded-xl font-bold text-lg hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-1 active:scale-95 premium-transition">
            Resubmit Documents
        </a>
    </div>
<?php else : ?>
<div class="flex flex-col gap-4 text-center mb-10 animate-fade-in-up">
<div class="inline-flex items-center self-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-sm font-semibold">
<span class="material-symbols-outlined text-[18px] animate-pulse">schedule</span>
                        Verification Pending
                    </div>
<h1 class="text-slate-900 dark:text-slate-100 text-4xl md:text-5xl font-black leading-tight tracking-tight">Hang Tight, We're Verifying Your ID</h1>
<p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl mx-auto">
                        Our team is reviewing your documents to keep CampusMarket safe for everyone. This usually takes 24-48 hours.
                    </p>
</div>
<div class="mb-10 animate-fade-in-up stagger-1">
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 shadow-xl shadow-primary/5 border border-slate-200 dark:border-slate-800 relative overflow-hidden premium-transition hover:shadow-primary/10 hover:border-primary/30 group">
<div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 premium-transition">
<span class="material-symbols-outlined text-8xl text-primary">verified_user</span>
</div>
<div class="relative z-10 flex flex-col md:flex-row gap-8 items-center">
<div class="flex-1 flex flex-col gap-4">
<div>
<p class="text-primary font-semibold text-sm uppercase tracking-wider mb-1">Current Status</p>
<p class="text-slate-900 dark:text-slate-100 text-2xl font-bold">Review in Progress</p>
</div>
<div class="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden">
<div class="bg-primary h-full w-[65%] transition-all duration-1000 ease-out" style="box-shadow: 0 0 10px rgba(17, 82, 212, 0.4);"></div>
</div>
<div class="flex justify-between text-sm text-slate-500">
<span>Submission Received</span>
<span>Manual Review</span>
<span class="opacity-40">Access Granted</span>
</div>
<button onclick="document.getElementById('submitted-docs').classList.toggle('hidden')" class="mt-4 flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-medium hover:bg-slate-200 dark:hover:bg-slate-700 hover:scale-105 active:scale-95 premium-transition w-fit">
<span class="material-symbols-outlined text-[20px]">visibility</span>
                                    View Submitted Documents
                                </button>
</div>
<div class="w-full md:w-64 h-40 bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 premium-transition hover:border-primary/50 group/preview">
    <?php 
    $id_front = get_user_meta($user->ID, '_cm_id_card_front', true);
    if ($id_front) : ?>
        <img src="<?php echo wp_get_attachment_image_url($id_front, 'thumbnail'); ?>" class="w-full h-full object-cover rounded-lg" />
    <?php else : ?>
        <span class="material-symbols-outlined text-4xl mb-2 group-hover/preview:scale-110 premium-transition">badge</span>
        <span class="text-xs font-medium">Student ID Preview</span>
    <?php endif; ?>
</div>
</div>

<div id="submitted-docs" class="hidden mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in-up">
    <?php 
    $docs = array(
        'Profile Photo' => get_user_meta($user->ID, '_cm_profile_photo', true),
        'ID Front' => get_user_meta($user->ID, '_cm_id_card_front', true),
        'ID Back' => get_user_meta($user->ID, '_cm_id_card_back', true),
    );
    foreach ($docs as $label => $id) : if ($id) : ?>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase mb-2"><?php echo esc_html($label); ?></p>
            <img src="<?php echo wp_get_attachment_image_url($id, 'large'); ?>" class="w-full rounded-xl shadow-lg border border-slate-200 dark:border-slate-700" />
        </div>
    <?php endif; endforeach; ?>
</div>

</div>
</div>
<div class="mb-12">
<h2 class="text-slate-900 dark:text-slate-100 text-2xl font-bold mb-6 animate-fade-in-up stagger-2">What to expect next</h2>
<div class="space-y-0 relative">
<div class="absolute left-6 top-4 bottom-4 w-0.5 bg-slate-200 dark:bg-slate-800"></div>
<div class="flex gap-6 items-start relative pb-8 animate-fade-in-up stagger-3">
<div class="z-10 flex items-center justify-center size-12 rounded-full bg-primary text-white shadow-lg shadow-primary/30 premium-transition hover:scale-110">
<span class="material-symbols-outlined">search</span>
</div>
<div class="flex-1 pt-2">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold">Manual Review</h3>
<p class="text-slate-600 dark:text-slate-400">Our administrators are carefully checking your student ID and profile photo for authenticity.</p>
</div>
</div>
<div class="flex gap-6 items-start relative pb-8 animate-fade-in-up stagger-4">
<div class="z-10 flex items-center justify-center size-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 premium-transition hover:border-primary/40">
<span class="material-symbols-outlined">mail</span>
</div>
<div class="flex-1 pt-2">
<h3 class="text-slate-400 dark:text-slate-500 text-lg font-bold">Email Confirmation</h3>
<p class="text-slate-500 dark:text-slate-600">Once the review is complete, you'll receive an automated notification at your university email address.</p>
</div>
</div>
<div class="flex gap-6 items-start relative animate-fade-in-up stagger-5">
<div class="z-10 flex items-center justify-center size-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 premium-transition hover:border-primary/40">
<span class="material-symbols-outlined">rocket_launch</span>
</div>
<div class="flex-1 pt-2">
<h3 class="text-slate-400 dark:text-slate-500 text-lg font-bold">Full Access Granted</h3>
<p class="text-slate-500 dark:text-slate-600">Start listing items, messaging sellers, and making deals with verified students across campus.</p>
</div>
</div>
</div>
</div>
<div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-4 mb-12 animate-fade-in-up stagger-6">
<a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="w-full sm:w-auto px-10 py-4 bg-primary text-center text-white rounded-xl font-bold text-lg hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-1 active:scale-95 premium-transition">
                        Browse Marketplace
                    </a>
<a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="w-full sm:w-auto px-10 py-4 bg-white dark:bg-slate-900 text-center border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-lg hover:bg-slate-50 dark:hover:bg-slate-800 hover:-translate-y-1 hover:shadow-lg active:scale-95 premium-transition">
                        Back to Profile
                    </a>
</div>
<div class="text-center pt-8 border-t border-slate-200 dark:border-slate-800 animate-fade-in-up stagger-6">
<p class="text-slate-500 dark:text-slate-400 mb-2">Have questions about verification?</p>
<a class="inline-flex items-center gap-1 text-primary font-semibold hover:gap-2 hover:underline premium-transition" href="<?php echo esc_url(home_url('/contact/')); ?>">
<span class="material-symbols-outlined text-[18px]">support_agent</span>
                        Contact Support Team
                    </a>
</div>
<?php endif; ?>
</div>
</main>
</div>
</div>

<style>
    .premium-transition {
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-1 { animation-delay: 0.1s; opacity: 0; }
    .stagger-2 { animation-delay: 0.2s; opacity: 0; }
    .stagger-3 { animation-delay: 0.3s; opacity: 0; }
    .stagger-4 { animation-delay: 0.4s; opacity: 0; }
    .stagger-5 { animation-delay: 0.5s; opacity: 0; }
    .stagger-6 { animation-delay: 0.6s; opacity: 0; }
</style>

<?php get_footer(); ?>
