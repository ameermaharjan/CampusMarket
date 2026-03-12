<?php

/**
 * Template Name: Admin Panel
 *
 * @package CampusMarket
 */

if (! is_user_logged_in() || ! current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

get_header();

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'listings';
?>

<div class="cm-section cm-dashboard cm-admin-panel">
    <div class="cm-container">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title">⚙️ Admin Panel</h1>
            <p class="cm-page-header__subtitle">Manage listings, bookings, and verify students</p>
        </header>

        <!-- Admin Stats -->
        <div class="cm-grid cm-grid--4 cm-dashboard__stats">
            <?php
            $pending_listings = new WP_Query(array(
                'post_type' => 'cm_listing',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'meta_query' => array(array('key' => '_cm_approval_status', 'value' => 'pending')),
            ));
            $total_listings = wp_count_posts('cm_listing')->publish;
            $total_bookings = wp_count_posts('cm_booking')->publish;
            $total_users = count_users();
            ?>
            <div class="cm-stat-card cm-stat-card--warning">
                <span class="cm-stat-card__icon">⏳</span>
                <span class="cm-stat-card__number"><?php echo esc_html($pending_listings->post_count); ?></span>
                <span class="cm-stat-card__label">Pending Listings</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">📦</span>
                <span class="cm-stat-card__number"><?php echo esc_html($total_listings); ?></span>
                <span class="cm-stat-card__label">Total Listings</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">📅</span>
                <span class="cm-stat-card__number"><?php echo esc_html($total_bookings); ?></span>
                <span class="cm-stat-card__label">Total Bookings</span>
            </div>
            <div class="cm-stat-card">
                <span class="cm-stat-card__icon">👥</span>
                <span class="cm-stat-card__number"><?php echo esc_html($total_users['total_users']); ?></span>
                <span class="cm-stat-card__label">Total Users</span>
            </div>
        </div>

        <!-- Tabs -->
        <div class="cm-dashboard__tabs">
            <a href="?tab=listings" class="cm-tab <?php echo 'listings' === $active_tab ? 'cm-tab--active' : ''; ?>">⏳ Pending Listings</a>
            <a href="?tab=users" class="cm-tab <?php echo 'users' === $active_tab ? 'cm-tab--active' : ''; ?>">👥 User Verification</a>
            <a href="?tab=bookings" class="cm-tab <?php echo 'bookings' === $active_tab ? 'cm-tab--active' : ''; ?>">📅 Recent Bookings</a>
        </div>

        <div class="cm-dashboard__content">
            <?php if ('listings' === $active_tab) : ?>
                <!-- PENDING LISTINGS -->
                <div class="cm-dashboard__panel">
                    <?php
                    $pending = new WP_Query(array(
                        'post_type'      => 'cm_listing',
                        'posts_per_page' => 20,
                        'meta_query'     => array(array('key' => '_cm_approval_status', 'value' => 'pending')),
                        'orderby'        => 'date',
                        'order'          => 'ASC',
                    ));

                    if ($pending->have_posts()) :
                    ?>
                        <div class="cm-table-responsive">
                            <table class="cm-table">
                                <thead>
                                    <tr>
                                        <th>Listing</th>
                                        <th>Author</th>
                                        <th>Price</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pending->have_posts()) : $pending->the_post();
                                        $author = get_userdata(get_the_author_meta('ID'));
                                    ?>
                                        <tr id="admin-listing-<?php the_ID(); ?>">
                                            <td>
                                                <div class="cm-table__listing">
                                                    <?php if (has_post_thumbnail()) the_post_thumbnail(array(48, 48)); ?>
                                                    <a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a>
                                                </div>
                                            </td>
                                            <td><?php echo esc_html($author->display_name); ?></td>
                                            <td><?php echo cm_get_price_display(); ?></td>
                                            <td><?php echo esc_html(ucfirst(get_post_meta(get_the_ID(), '_cm_listing_type', true) ?: 'item')); ?></td>
                                            <td><?php echo esc_html(get_the_date()); ?></td>
                                            <td class="cm-table__actions">
                                                <button class="cm-btn cm-btn--success cm-btn--xs cm-admin-approve" data-listing-id="<?php the_ID(); ?>" data-action="approved">✅ Approve</button>
                                                <button class="cm-btn cm-btn--danger cm-btn--xs cm-admin-approve" data-listing-id="<?php the_ID(); ?>" data-action="rejected">❌ Reject</button>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                    wp_reset_postdata(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">✅</div>
                            <h3>All caught up!</h3>
                            <p>No pending listings to review.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ('users' === $active_tab) : ?>
                <!-- USER VERIFICATION -->
                <div class="cm-dashboard__panel">
                    <?php
                    $students = get_users(array(
                        'role'    => 'student',
                        'orderby' => 'registered',
                        'order'   => 'DESC',
                        'number'  => 50,
                    ));

                    if (! empty($students)) :
                    ?>
                        <div class="cm-table-responsive">
                            <table class="cm-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Student ID</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student) :
                                        $s_verified   = cm_is_user_verified($student->ID);
                                        $s_student_id = get_user_meta($student->ID, '_cm_student_id', true);
                                        $s_department = get_user_meta($student->ID, '_cm_department', true);
                                    ?>
                                        <tr id="admin-user-<?php echo esc_attr($student->ID); ?>">
                                            <td>
                                                <div class="cm-table__listing">
                                                    <?php echo get_avatar($student->ID, 36); ?>
                                                    <?php echo esc_html($student->display_name); ?>
                                                </div>
                                            </td>
                                            <td><?php echo esc_html($student->user_email); ?></td>
                                            <td><?php echo esc_html($s_student_id ?: '—'); ?></td>
                                            <td><?php echo esc_html($s_department ?: '—'); ?></td>
                                            <td>
                                                <?php if ($s_verified) : ?>
                                                    <span class="cm-badge cm-badge--success">✅ Verified</span>
                                                <?php else : ?>
                                                    <span class="cm-badge cm-badge--warning">⏳ Unverified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($s_verified) : ?>
                                                    <button class="cm-btn cm-btn--danger cm-btn--xs cm-admin-verify" data-user-id="<?php echo esc_attr($student->ID); ?>" data-verify="0">Unverify</button>
                                                <?php else : ?>
                                                    <button class="cm-btn cm-btn--success cm-btn--xs cm-admin-verify" data-user-id="<?php echo esc_attr($student->ID); ?>" data-verify="1">✅ Verify</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">👥</div>
                            <h3>No students registered yet</h3>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ('bookings' === $active_tab) : ?>
                <!-- RECENT BOOKINGS -->
                <div class="cm-dashboard__panel">
                    <?php
                    $bookings = new WP_Query(array(
                        'post_type'      => 'cm_booking',
                        'posts_per_page' => 20,
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    ));

                    if ($bookings->have_posts()) :
                        while ($bookings->have_posts()) : $bookings->the_post();
                            get_template_part('template-parts/booking-card');
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">📅</div>
                            <h3>No bookings yet</h3>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
