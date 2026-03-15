<?php
/**
 * Template Name: Feedback Page
 *
 * @package CampusMarket
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

get_header();
?>

<main class="min-h-screen bg-slate-50 pt-32 pb-20">
    <div class="max-w-7xl mx-auto px-4 md:px-10">
        
        <!-- Header -->
        <div class="max-w-3xl mx-auto text-center mb-16 animate-fade-slide-up">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-xs font-black uppercase tracking-widest mb-6">
                <span class="material-symbols-outlined text-sm">rate_review</span>
                Share Your Thoughts
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Help us make CampusMarket <span class="text-primary">Better</span> for everyone</h1>
            <p class="text-lg text-slate-500 leading-relaxed">Your feedback is the foundation of our community. Tell us what you love, what needs fixing, or what features you'd like to see next.</p>
        </div>

        <!-- Feedback Form Card -->
        <div class="max-w-2xl mx-auto animate-fade-slide-up stagger-1">
            <div class="glass-panel rounded-[2.5rem] overflow-hidden shadow-2xl shadow-primary/5 border-none">
                <form id="cm-standalone-feedback-form" class="p-8 md:p-12 space-y-8">
                    
                    <!-- Subject -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-2">What's this about?</label>
                        <input type="text" name="subject" placeholder="e.g., Feature Suggestion, Bug Report, General Praise" 
                               class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-slate-900 focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none" required>
                    </div>

                    <!-- Rating -->
                    <div class="space-y-4">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-2 text-center">Your Rating</label>
                        <div id="cm-standalone-stars" class="flex gap-4 items-center justify-center py-6 bg-slate-50 rounded-2xl border-2 border-slate-100">
                            <button type="button" class="cm-standalone-star-btn text-4xl text-slate-200 transition-all hover:scale-110 active:scale-95" data-rating="1">★</button>
                            <button type="button" class="cm-standalone-star-btn text-4xl text-slate-200 transition-all hover:scale-110 active:scale-95" data-rating="2">★</button>
                            <button type="button" class="cm-standalone-star-btn text-4xl text-slate-200 transition-all hover:scale-110 active:scale-95" data-rating="3">★</button>
                            <button type="button" class="cm-standalone-star-btn text-4xl text-slate-200 transition-all hover:scale-110 active:scale-95" data-rating="4">★</button>
                            <button type="button" class="cm-standalone-star-btn text-4xl text-slate-200 transition-all hover:scale-110 active:scale-95" data-rating="5">★</button>
                            <input type="hidden" name="rating" id="cm-standalone-rating-input" value="5">
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-2">Detailed Feedback</label>
                        <textarea name="message" required placeholder="Describe your experience or suggestion in detail..." rows="6" 
                                class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-slate-900 focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none resize-none"></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-5 bg-primary text-white font-black rounded-2xl shadow-xl shadow-primary/20 hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-3 active:scale-95">
                            <span>SUBMIT FEEDBACK</span>
                            <span class="material-symbols-outlined text-xl">send</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-12">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-primary transition-colors group">
                    <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">arrow_back</span>
                    Back to Marketplace
                </a>
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    const starBtns = $('.cm-standalone-star-btn');
    const ratingInput = $('#cm-standalone-rating-input');
    const form = $('#cm-standalone-feedback-form');

    // Initial stars
    updateStars(5);

    starBtns.on('click', function() {
        const rating = $(this).data('rating');
        ratingInput.val(rating);
        updateStars(rating);
    });

    function updateStars(rating) {
        starBtns.each(function() {
            const btnRating = $(this).data('rating');
            if (btnRating <= rating) {
                $(this).removeClass('text-slate-200').addClass('text-amber-400');
            } else {
                $(this).removeClass('text-amber-400').addClass('text-slate-200');
            }
        });
    }

    form.on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalContent = submitBtn.html();
        
        submitBtn.prop('disabled', true).addClass('opacity-70');
        submitBtn.html('<span class="material-symbols-outlined animate-spin">sync</span> SUBMITTING...');

        const formData = new FormData(this);
        formData.append('action', 'cm_submit_feedback');
        formData.append('nonce', '<?php echo wp_create_nonce('cm_nonce'); ?>');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.cmToast(response.data.message, 'success');
                    form[0].reset();
                    updateStars(5);
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                    }, 2000);
                } else {
                    window.cmToast(response.data.message || 'Something went wrong', 'error');
                }
            },
            error: function() {
                window.cmToast('Failed to send feedback. Please try again.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).removeClass('opacity-70');
                submitBtn.html(originalContent);
            }
        });
    });
});
</script>

<?php get_footer(); ?>
