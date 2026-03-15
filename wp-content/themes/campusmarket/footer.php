<?php
/**
 * Footer template — Premium Redesign
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
</main><!-- #main-content -->

<footer class="bg-white border-t border-slate-200 mt-20">
    <div class="max-w-7xl mx-auto px-4 md:px-10 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-12">

            <!-- Brand Column -->
            <div class="col-span-1 md:col-span-1">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 text-primary mb-6">
                    <span class="material-symbols-outlined text-2xl font-bold">school</span>
                    <h2 class="text-slate-900 text-lg font-bold tracking-tight"><?php bloginfo('name'); ?></h2>
                </a>
                <p class="text-sm text-slate-500 leading-relaxed">
                    <?php echo esc_html(get_bloginfo('description') ?: 'The #1 platform for university students to buy, sell, and rent items within their campus community.'); ?>
                </p>
            </div>

            <!-- Marketplace Column -->
            <div>
                <h4 class="text-slate-900 font-bold text-sm mb-6">Marketplace</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">All Categories</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">Recent Listings</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/browse/?listing_type=rental')); ?>">Rental Items</a></li>
                    <?php if (is_user_logged_in()) : ?>
                        <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/dashboard/')); ?>">My Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Company Column -->
            <div>
                <h4 class="text-slate-900 font-bold text-sm mb-6">Company</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/about/')); ?>">About Us</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/safety/')); ?>">Campus Safety</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/privacy/')); ?>">Privacy Policy</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300" href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Support Column -->
            <div>
                <h4 class="text-slate-900 font-bold text-sm mb-6">Support</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li><a class="hover:text-primary transition-colors duration-300 flex items-center gap-2" href="mailto:maharjana200@gmail.com"><span class="material-symbols-outlined text-base">mail</span> Customer Care</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300 flex items-center gap-2" href="https://wa.me/9779767646896" target="_blank"><span class="material-symbols-outlined text-base">chat</span> Contact Us</a></li>
                    <li><a class="hover:text-primary transition-colors duration-300 flex items-center gap-2" href="https://ig.me/m/ameer_maharjan" target="_blank"><span class="material-symbols-outlined text-base">chat_bubble</span> Instagram DM</a></li>
                    <li>
                        <?php if (is_user_logged_in()) : ?>
                            <button id="cm-open-feedback" class="hover:text-primary transition-colors duration-300 flex items-center gap-2 outline-none"><span class="material-symbols-outlined text-base">rate_review</span> Give Feedback</button>
                        <?php else : ?>
                            <button onclick="window.cmToast('Please login to give feedback', 'info');" class="hover:text-primary transition-colors duration-300 flex items-center gap-2 outline-none"><span class="material-symbols-outlined text-base">rate_review</span> Give Feedback</button>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>

            <!-- Newsletter Column -->
            <div>
                <h4 class="text-slate-900 font-bold text-sm mb-6">Newsletter</h4>
                <p class="text-sm text-slate-500 mb-4">Get the best deals from your campus.</p>
                <form class="flex gap-2" onsubmit="event.preventDefault(); alert('Thanks for subscribing!');">
                    <input class="w-full px-3 py-2 bg-slate-100 border-none rounded-lg text-sm focus:ring-primary/50 transition-all duration-300" placeholder="Email" type="email" required>
                    <button type="submit" class="bg-primary text-white p-2 rounded-lg hover:bg-primary/90 transition-all duration-300 active:scale-95">
                        <span class="material-symbols-outlined text-sm">send</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-slate-200 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-500">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. Built for students, by students.</p>
            <div class="flex items-center gap-6">
                <button id="cm-footer-share" class="text-slate-400 hover:text-primary transition-all duration-300 hover:scale-110 flex items-center" title="Share this page">
                    <span class="material-symbols-outlined">share</span>
                </button>
                <a class="text-slate-400 hover:text-primary transition-all duration-300 hover:scale-110" href="#"><span class="material-symbols-outlined">language</span></a>
            </div>
        </div>
    </div>
</footer>

<!-- Feedback Modal -->
<div id="cm-feedback-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cm-modal-close"></div>
    <div class="glass-panel w-full max-w-md rounded-[2.5rem] overflow-hidden relative translate-y-8 transition-all duration-300">
        <form id="cm-feedback-form" class="p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-black text-slate-900">Your Feedback</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Help us improve CampusMarket</p>
                </div>
                <button type="button" class="size-12 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors cm-modal-close">
                    <span class="material-symbols-outlined text-slate-400">close</span>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Subject -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Subject</label>
                    <input type="text" name="subject" placeholder="e.g., Suggestion, Issue, Praise" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none">
                </div>

                <!-- Rating -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-2">Overall Rating</label>
                    <div id="cm-feedback-stars" class="flex gap-2 items-center justify-center py-2 bg-slate-50 rounded-2xl border-2 border-slate-100">
                        <button type="button" class="cm-star-btn text-3xl text-slate-200 transition-all hover:scale-110" data-rating="1">★</button>
                        <button type="button" class="cm-star-btn text-3xl text-slate-200 transition-all hover:scale-110" data-rating="2">★</button>
                        <button type="button" class="cm-star-btn text-3xl text-slate-200 transition-all hover:scale-110" data-rating="3">★</button>
                        <button type="button" class="cm-star-btn text-3xl text-slate-200 transition-all hover:scale-110" data-rating="4">★</button>
                        <button type="button" class="cm-star-btn text-3xl text-slate-200 transition-all hover:scale-110" data-rating="5">★</button>
                        <input type="hidden" name="rating" id="cm-feedback-rating-input" value="5">
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Your Message</label>
                    <textarea name="message" required placeholder="Tell us what's on your mind..." rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none resize-none"></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full py-4 bg-primary text-white font-black rounded-2xl shadow-xl shadow-primary/20 hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-3 active:scale-95">
                    <span>SEND FEEDBACK</span>
                    <span class="material-symbols-outlined text-xl">send</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Share Modal -->
<div id="cm-share-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cm-modal-close"></div>
    <div class="glass-panel w-full max-w-sm rounded-[2rem] overflow-hidden relative translate-y-8 transition-all duration-300">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-slate-900">Share</h3>
                <button class="size-10 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors cm-modal-close">
                    <span class="material-symbols-outlined text-slate-400">close</span>
                </button>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mb-8">
                <button class="cm-share-btn flex flex-col items-center gap-3 p-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100 transition-all group" data-platform="whatsapp">
                    <div class="size-12 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">chat</span>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700">WhatsApp</span>
                </button>
                <button class="cm-share-btn flex flex-col items-center gap-3 p-4 rounded-2xl bg-pink-50 hover:bg-pink-100 transition-all group" data-platform="instagram">
                    <div class="size-12 rounded-full bg-gradient-to-tr from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-rose-500/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">photo_camera</span>
                    </div>
                    <span class="text-[10px] font-bold text-rose-700">Instagram</span>
                </button>
                <button class="cm-share-btn flex flex-col items-center gap-3 p-4 rounded-2xl bg-blue-50 hover:bg-blue-100 transition-all group" data-platform="facebook">
                    <div class="size-12 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">public</span>
                    </div>
                    <span class="text-[10px] font-bold text-blue-700">Facebook</span>
                </button>
            </div>

            <div class="relative">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-2">Page Link</p>
                <div class="flex items-center gap-2 p-2 bg-slate-100 rounded-xl">
                    <input type="text" id="cm-share-url" readonly value="<?php echo esc_url(home_url(add_query_arg(array(), $wp->request))); ?>" class="flex-1 bg-transparent border-none text-xs text-slate-600 focus:ring-0 px-2 truncate">
                    <button id="cm-copy-url" class="px-4 py-2 bg-white text-primary text-[10px] font-black rounded-lg shadow-sm hover:shadow-md transition-all active:scale-95">COPY</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report User Modal -->
<div id="cm-report-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cm-modal-close"></div>
    <div class="glass-panel w-full max-w-md rounded-[2.5rem] overflow-hidden relative translate-y-8 transition-all duration-300">
        <form id="cm-report-form" class="p-8">
            <input type="hidden" name="reported_user_id" id="cm-report-user-id">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-black text-rose-600">Report Misconduct</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Reporting: <span id="cm-report-user-name" class="text-slate-600">User</span></p>
                </div>
                <button type="button" class="size-12 rounded-full hover:bg-rose-50 flex items-center justify-center transition-colors cm-modal-close">
                    <span class="material-symbols-outlined text-slate-400">close</span>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Reason -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Reason for Report</label>
                    <select name="reason" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-500/5 transition-all outline-none appearance-none cursor-pointer">
                        <option value="">Select a reason...</option>
                        <option value="scam">Potential Scam / Fraud</option>
                        <option value="harassment">Harassment / Abusive Behavior</option>
                        <option value="inappropriate_listing">Inappropriate Listing</option>
                        <option value="misleading">Misleading Information</option>
                        <option value="other">Other Issue</option>
                    </select>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Additional Details</label>
                    <textarea name="message" required placeholder="Please describe the issue in detail..." rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-500/5 transition-all outline-none resize-none"></textarea>
                </div>

                <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                    <p class="text-[10px] text-rose-600 leading-relaxed font-medium">
                        <strong>Security Note:</strong> Your report will be sent to the CampusMarket admin team for immediate audit. False reporting may result in account suspension.
                    </p>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full py-4 bg-rose-600 text-white font-black rounded-2xl shadow-xl shadow-rose-600/20 hover:shadow-2xl hover:shadow-rose-600/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-3 active:scale-95">
                    <span>SUBMIT REPORT</span>
                    <span class="material-symbols-outlined text-xl">flag</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php wp_footer(); ?>
</body>

</html>