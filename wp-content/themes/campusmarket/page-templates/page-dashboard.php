<?php

/**
 * Template Name: Student Dashboard
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(wp_login_url(home_url('/dashboard/')));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$active_tab   = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'listings';
$student_id   = get_user_meta($current_user->ID, '_cm_student_id', true);
$department   = get_user_meta($current_user->ID, '_cm_department', true);
$phone        = get_user_meta($current_user->ID, '_cm_phone', true);
$is_verified  = cm_is_user_verified($current_user->ID);
?>

<div class="cm-section cm-dashboard">
    <div class="cm-container">
        <!-- Dashboard Header -->
        <div class="cm-dashboard__header">
            <div class="cm-dashboard__user">
                <?php echo get_avatar($current_user->ID, 72, '', '', array('class' => 'cm-dashboard__avatar')); ?>
                <div>
                    <h1 class="cm-dashboard__name">
                        <?php echo esc_html($current_user->display_name); ?>
                        <?php if ($is_verified) : ?>
                            <span class="cm-verified-badge" title="Verified Student">✔</span>
                        <?php endif; ?>
                    </h1>
                    <p class="cm-dashboard__email"><?php echo esc_html($current_user->user_email); ?></p>
                </div>
            </div>
            <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--primary">
                + New Listing
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="cm-grid cm-grid--4 cm-dashboard__stats">
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">📦</span>
                <span class="cm-stat-card__number"><?php echo esc_html(cm_count_user_listings($current_user->ID)); ?></span>
                <span class="cm-stat-card__label">My Listings</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">📅</span>
                <span class="cm-stat-card__number"><?php echo esc_html(cm_count_user_bookings($current_user->ID)); ?></span>
                <span class="cm-stat-card__label">Bookings</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">💬</span>
                <span class="cm-stat-card__number"><?php echo esc_html(cm_get_total_unread($current_user->ID)); ?></span>
                <span class="cm-stat-card__label">Unread Messages</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon"><?php echo $is_verified ? '✅' : '⏳'; ?></span>
                <span class="cm-stat-card__number"><?php echo $is_verified ? 'Verified' : 'Pending'; ?></span>
                <span class="cm-stat-card__label">Account Status</span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="cm-dashboard__tabs">
            <a href="?tab=listings" class="cm-tab <?php echo 'listings' === $active_tab ? 'cm-tab--active' : ''; ?>">📦 My Listings</a>
            <a href="?tab=bookings" class="cm-tab <?php echo 'bookings' === $active_tab ? 'cm-tab--active' : ''; ?>">📅 My Bookings</a>
            <a href="?tab=requests" class="cm-tab <?php echo 'requests' === $active_tab ? 'cm-tab--active' : ''; ?>">📩 Rental Requests</a>
            <a href="?tab=profile" class="cm-tab <?php echo 'profile' === $active_tab ? 'cm-tab--active' : ''; ?>">👤 My Profile</a>
        </div>

        <!-- Tab Content -->
        <div class="cm-dashboard__content">
            <?php if ('listings' === $active_tab) : ?>
                <!-- MY LISTINGS TAB -->
                <div class="cm-dashboard__panel">
                    <?php
                    $my_listings = new WP_Query(array(
                        'post_type'      => 'cm_listing',
                        'author'         => $current_user->ID,
                        'posts_per_page' => -1,
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    ));

                    if ($my_listings->have_posts()) :
                    ?>
                        <div class="cm-table-responsive">
                            <table class="cm-table">
                                <thead>
                                    <tr>
                                        <th>Listing</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($my_listings->have_posts()) : $my_listings->the_post();
                                        $status = get_post_meta(get_the_ID(), '_cm_approval_status', true) ?: 'pending';
                                    ?>
                                        <tr id="listing-row-<?php the_ID(); ?>">
                                            <td>
                                                <div class="cm-table__listing">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <?php the_post_thumbnail(array(48, 48)); ?>
                                                    <?php endif; ?>
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </div>
                                            </td>
                                            <td><?php echo cm_get_price_display(); ?></td>
                                            <td><?php echo cm_get_status_badge($status); ?></td>
                                            <td><?php echo esc_html(get_the_date()); ?></td>
                                            <td>
                                                <a href="<?php the_permalink(); ?>" class="cm-btn cm-btn--ghost cm-btn--xs">View</a>
                                                <button class="cm-btn cm-btn--danger cm-btn--xs cm-delete-listing" data-listing-id="<?php the_ID(); ?>">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                    wp_reset_postdata(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">📦</div>
                            <h3>No listings yet</h3>
                            <p>Create your first listing to start sharing.</p>
                            <a href="<?php echo esc_url(home_url('/list-item/')); ?>" class="cm-btn cm-btn--primary">Create Listing</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ('bookings' === $active_tab) : ?>
                <!-- MY BOOKINGS TAB -->
                <div class="cm-dashboard__panel">
                    <?php
                    $my_bookings = cm_get_user_bookings($current_user->ID);
                    if ($my_bookings->have_posts()) :
                        while ($my_bookings->have_posts()) : $my_bookings->the_post();
                            get_template_part('template-parts/booking-card');
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">📅</div>
                            <h3>No bookings yet</h3>
                            <p>Browse the marketplace to find items to rent.</p>
                            <a href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>" class="cm-btn cm-btn--primary">Browse Marketplace</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ('requests' === $active_tab) : ?>
                <!-- RENTAL REQUESTS TAB -->
                <div class="cm-dashboard__panel">
                    <?php
                    $requests = cm_get_rental_requests($current_user->ID);
                    if ($requests->have_posts()) :
                        while ($requests->have_posts()) : $requests->the_post();
                            get_template_part('template-parts/booking-card');
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">📩</div>
                            <h3>No rental requests</h3>
                            <p>When someone books one of your items, it will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ('profile' === $active_tab) : ?>
                <!-- PROFILE TAB -->
                <div class="cm-dashboard__panel">
                    <div class="cm-form-card">
                        <form id="cm-profile-form" class="cm-form" method="post">
                            <div class="cm-form-group">
                                <label for="cm-profile-name" class="cm-form-label">Display Name</label>
                                <input type="text" id="cm-profile-name" name="display_name" class="cm-input" value="<?php echo esc_attr($current_user->display_name); ?>">
                            </div>
                            <div class="cm-form-group">
                                <label for="cm-profile-email" class="cm-form-label">Email</label>
                                <input type="email" id="cm-profile-email" class="cm-input" value="<?php echo esc_attr($current_user->user_email); ?>" disabled>
                            </div>
                            <div class="cm-form-group">
                                <label for="cm-profile-student-id" class="cm-form-label">Student ID</label>
                                <input type="text" id="cm-profile-student-id" name="student_id" class="cm-input" value="<?php echo esc_attr($student_id); ?>" placeholder="e.g., STU2025001">
                            </div>
                            <div class="cm-form-group">
                                <label for="cm-profile-department" class="cm-form-label">Department</label>
                                <input type="text" id="cm-profile-department" name="department" class="cm-input" value="<?php echo esc_attr($department); ?>" placeholder="e.g., Computer Science">
                            </div>
                            <div class="cm-form-group">
                                <label for="cm-profile-phone" class="cm-form-label">Phone Number</label>
                                <input type="tel" id="cm-profile-phone" name="phone" class="cm-input" value="<?php echo esc_attr($phone); ?>" placeholder="e.g., 9800000000">
                            </div>
                            <button type="submit" class="cm-btn cm-btn--primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
