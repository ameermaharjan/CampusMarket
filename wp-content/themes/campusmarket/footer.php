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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">

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
                <a class="text-slate-400 hover:text-primary transition-all duration-300 hover:scale-110" href="#"><span class="material-symbols-outlined">share</span></a>
                <a class="text-slate-400 hover:text-primary transition-all duration-300 hover:scale-110" href="#"><span class="material-symbols-outlined">language</span></a>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>