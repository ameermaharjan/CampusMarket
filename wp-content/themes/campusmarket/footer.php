<?php

/**
 * Footer template
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
</main><!-- #main-content -->

<footer class="cm-footer">
    <div class="cm-container">

        <div class="cm-footer__grid">
            <!-- Brand Column -->
            <div class="cm-footer__col">
                <div class="cm-footer__brand">
                    <span class="cm-footer__logo-icon">🎓</span>
                    <span class="cm-header__logo-text"><?php bloginfo('name'); ?></span>
                </div>
                <p class="cm-footer__tagline"><?php bloginfo('description'); ?></p>
                <div class="cm-footer__social">
                    <a href="#" class="cm-footer__social-link">🐦</a>
                    <a href="#" class="cm-footer__social-link">📷</a>
                </div>
            </div>

            <!-- Company Column -->
            <div class="cm-footer__col">
                <h4 class="cm-footer__heading">Company</h4>
                <ul class="cm-footer__links">
                    <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About Us</a></li>
                    <li><a href="<?php echo esc_url(home_url('/blog/')); ?>">Latest News</a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a></li>
                </ul>
            </div>

            <!-- Support Column -->
            <div class="cm-footer__col">
                <h4 class="cm-footer__heading">Support</h4>
                <ul class="cm-footer__links">
                    <li><a href="<?php echo esc_url(home_url('/help/')); ?>">Help Center</a></li>
                    <li><a href="<?php echo esc_url(home_url('/safety/')); ?>">Safety Guidelines</a></li>
                    <li><a href="<?php echo esc_url(home_url('/faq/')); ?>">FAQs</a></li>
                </ul>
            </div>

            <!-- Legal Column -->
            <div class="cm-footer__col">
                <h4 class="cm-footer__heading">Legal</h4>
                <ul class="cm-footer__links">
                    <li><a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Service</a></li>
                    <li><a href="<?php echo esc_url(home_url('/privacy/')); ?>">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="cm-footer__bottom">
            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. Built for students, by students.</p>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>