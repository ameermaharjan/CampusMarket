<?php
/**
 * Single Listing Template — Premium Item Detail Page
 *
 * @package CampusMarket
 */

get_header();

while (have_posts()) : the_post();

    $listing_id   = get_the_ID();
    $price        = get_post_meta($listing_id, '_cm_price', true);
    $price_type   = get_post_meta($listing_id, '_cm_price_type', true);
    $condition    = get_post_meta($listing_id, '_cm_condition', true);
    $location     = get_post_meta($listing_id, '_cm_location', true);
    $listing_type = get_post_meta($listing_id, '_cm_listing_type', true) ?: 'item';
    $avail_start  = get_post_meta($listing_id, '_cm_availability_start', true);
    $avail_end    = get_post_meta($listing_id, '_cm_availability_end', true);
    $author_id    = get_post_field('post_author', $listing_id);
    $author       = get_userdata($author_id);
    $avg_rating   = cm_get_average_rating($listing_id);
    $review_count = cm_get_review_count($listing_id);
    $categories   = get_the_terms($listing_id, 'listing_category');
    $is_owner     = cm_is_listing_owner($listing_id);
    $listing_intent = get_post_meta($listing_id, '_cm_listing_intent', true);
    $is_rental    = ($listing_intent === 'rent' || $price_type === 'per_day' || $price_type === 'per_week' || $price_type === 'per_hour');
    $user_listings_count = cm_count_user_listings($author_id);
?>

<div class="max-w-7xl mx-auto w-full px-4 md:px-10 py-8">

    <!-- Breadcrumb -->
    <nav class="flex text-sm text-slate-500 mb-6 gap-2 items-center">
        <a class="hover:text-primary transition-colors" href="<?php echo esc_url(home_url()); ?>">Home</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">Marketplace</a>
        <?php if (! empty($categories) && ! is_wp_error($categories)) : ?>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
        <?php endif; ?>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-slate-900 font-medium"><?php the_title(); ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Image + Description -->
        <div class="lg:col-span-8 flex flex-col gap-6 opacity-0 animate-fade-slide-up stagger-1">

            <!-- Main Image -->
            <div class="rounded-xl overflow-hidden bg-white border border-slate-200 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="aspect-[16/9] w-full relative group">
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="w-full h-full object-cover" id="cm-main-image" src="<?php echo esc_url(get_the_post_thumbnail_url($listing_id, 'large')); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <div class="w-full h-full bg-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-6xl text-slate-300">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <button class="bg-white/90 backdrop-blur p-2 rounded-lg shadow-lg hover:bg-white transition-all active:scale-90">
                            <span class="material-symbols-outlined">zoom_in</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900"><?php the_title(); ?></h1>
                        <p class="text-slate-500 mt-1">
                            <?php if (! empty($categories) && ! is_wp_error($categories)) echo esc_html($categories[0]->name) . ' • '; ?>
                            Listing #CM-<?php echo esc_html($listing_id); ?>
                        </p>
                    </div>
                    <?php 
                    $item_status = get_post_meta($listing_id, '_cm_item_status', true) ?: 'active';
                    if ($item_status === 'sold') : ?>
                        <div class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-sm font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">block</span> Sold
                        </div>
                    <?php elseif ($item_status === 'rented') : 
                        $rent_until = get_post_meta($listing_id, '_cm_rented_until', true);
                        $rent_label = $rent_until ? 'Rented until ' . date('j M', strtotime($rent_until)) : 'Currently Rented';
                    ?>
                        <div class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">event_busy</span> <?php echo esc_html($rent_label); ?>
                        </div>
                    <?php else : ?>
                        <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">check_circle</span> Available
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <?php if ($avg_rating > 0) : ?>
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="font-bold"><?php echo esc_html($avg_rating); ?></span>
                            <span class="text-slate-500 text-sm">(<?php echo esc_html($review_count); ?> reviews)</span>
                        </div>
                        <div class="h-4 w-[1px] bg-slate-200"></div>
                    <?php endif; ?>
                    <?php if (cm_is_user_verified($author_id)) : ?>
                        <div class="flex items-center gap-1 text-primary">
                            <span class="material-symbols-outlined text-sm">verified</span>
                            <span class="text-sm font-semibold">Student Listing</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-bold mb-2">Description</h3>
                        <div class="text-slate-600 leading-relaxed">
                            <?php the_content(); ?>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <?php if ($condition) : ?>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white transition-colors cursor-default">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Condition</p>
                            <p class="font-medium"><?php echo esc_html(cm_get_condition_label($condition)); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($location) : ?>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white transition-colors cursor-default">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Pick up location</p>
                            <p class="font-medium"><?php echo esc_html($location); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($avail_start && $avail_end) : ?>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white transition-colors cursor-default col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Available Dates & Time</p>
                            <p class="font-medium">
                                <?php 
                                $start_fmt = date('j M, g:i a', strtotime($avail_start));
                                $end_fmt = date('j M, g:i a', strtotime($avail_end));
                                echo esc_html($start_fmt) . ' — ' . esc_html($end_fmt); 
                                ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Seller Info Card -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm transition-all duration-300 hover:shadow-md">
                <h3 class="text-lg font-bold mb-4">Seller Information</h3>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-slate-200 overflow-hidden ring-4 ring-primary/10 hover:ring-primary/30 transition-all duration-300 cursor-pointer">
                            <?php echo get_avatar($author_id, 64, '', '', array('class' => 'w-full h-full object-cover')); ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-xl font-bold hover:text-primary transition-colors cursor-pointer"><?php echo esc_html($author->display_name); ?></p>
                                <?php if (cm_is_user_verified($author_id)) : ?>
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-slate-500"><?php echo esc_html($user_listings_count); ?> listings</p>
                        </div>
                    </div>
                    <?php if (is_user_logged_in() && ! $is_owner) : ?>
                        <div class="flex gap-3">
                            <?php if (cm_is_user_verified(get_current_user_id())) : ?>
                                <a href="<?php echo esc_url(home_url('/chat/?with=' . $author_id)); ?>" class="btn-premium flex-1 md:flex-none px-6 py-2 bg-primary/10 text-primary font-bold rounded-lg hover:bg-primary/20 transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">chat_bubble</span> Message
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(home_url('/verification-pending/')); ?>" class="btn-premium flex-1 md:flex-none px-6 py-2 bg-primary/10 text-primary font-bold rounded-lg hover:bg-primary/20 transition-colors flex items-center justify-center gap-2" title="Verification required to message sellers">
                                    <span class="material-symbols-outlined">lock</span> Message
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm" id="cm-reviews-section">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Reviews <?php if ($review_count > 0) : ?><span class="text-slate-400 font-normal">(<?php echo esc_html($review_count); ?>)</span><?php endif; ?></h3>
                    <?php if ($avg_rating > 0) : ?>
                        <div class="flex items-center gap-1">
                            <?php echo cm_get_star_html($avg_rating); ?>
                            <span class="text-sm text-slate-500 ml-1"><?php echo esc_html($avg_rating); ?>/5</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                $reviews = cm_get_listing_reviews($listing_id);
                if ($reviews->have_posts()) :
                    while ($reviews->have_posts()) : $reviews->the_post();
                        $review_rating = get_post_meta(get_the_ID(), '_cm_review_rating', true);
                        $reviewer_id = get_post_field('post_author', get_the_ID());
                ?>
                    <div class="p-4 border border-slate-100 rounded-lg bg-slate-50/50 transition-all hover:bg-white hover:shadow-md mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex text-amber-400">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' <?php echo $i <= $review_rating ? '1' : '0'; ?>;">star</span>
                                <?php endfor; ?>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo esc_html(cm_time_ago(get_the_date('Y-m-d H:i:s'))); ?></span>
                        </div>
                        <p class="text-sm text-slate-600 mb-2"><?php echo esc_html(get_the_content()); ?></p>
                        <div class="flex items-center gap-2">
                            <?php echo get_avatar($reviewer_id, 20, '', '', array('class' => 'w-5 h-5 rounded-full')); ?>
                            <span class="text-xs font-semibold"><?php echo esc_html(get_the_author()); ?></span>
                        </div>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <p class="text-sm text-slate-500">No reviews yet. Be the first to review!</p>
                <?php endif; ?>

                <!-- Review Form -->
                <?php if (is_user_logged_in() && ! $is_owner) : ?>
                    <div class="mt-6 pt-6 border-t border-slate-100" id="cm-review-form">
                        <h4 class="font-bold mb-4">Leave a Review</h4>
                        <form id="cm-submit-review-form" class="space-y-4">
                            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 block mb-2">Rating</label>
                                <div class="cm-star-input flex gap-1" id="cm-star-input">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <span class="cursor-pointer text-2xl text-slate-300 hover:text-amber-400 transition-colors" data-rating="<?php echo $i; ?>">☆</span>
                                    <?php endfor; ?>
                                    <input type="hidden" name="rating" id="cm-rating-input" value="5">
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700 block mb-2" for="cm-review-comment">Your Review</label>
                                <textarea id="cm-review-comment" name="comment" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Share your experience..." required></textarea>
                            </div>
                            <button type="submit" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Submit Review</button>
                        </form>
                        <div class="cm-review-form__message mt-4" id="cm-review-message"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Booking Sidebar -->
        <div class="lg:col-span-4 opacity-0 animate-fade-slide-up stagger-2">
            <div class="sticky top-24 space-y-6">

                <!-- Price/Booking Card -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden hover-glow transition-shadow duration-500">
                    <div class="p-6 bg-primary text-white overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                        <p class="text-white/80 text-sm font-medium relative z-10"><?php echo $is_rental ? 'Rental Price' : 'Sale Price'; ?></p>
                        <div class="flex items-baseline gap-1 mt-1 relative z-10">
                            <span class="text-3xl font-black">Rs. <?php echo esc_html($price); ?></span>
                            <?php if ($is_rental) : ?>
                                <span class="text-white/80">/ <?php echo $price_type === 'per_day' ? 'day' : 'week'; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <?php 
                        $item_status = get_post_meta($listing_id, '_cm_item_status', true) ?: 'active';
                        if (is_user_logged_in() && ! $is_owner) : 
                            if ($item_status === 'active') : ?>
                            <!-- Booking Form -->
                            <form id="cm-submit-booking-form" class="space-y-4">
                                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                                <input type="hidden" name="price" value="<?php echo esc_attr($price); ?>">
                                <input type="hidden" name="price_type" value="<?php echo esc_attr($price_type); ?>">

                                <?php if ($is_rental) : ?>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rental Dates & Time</label>
                                        <div class="flex flex-col gap-2">
                                            <div class="relative group">
                                                <input class="w-full bg-slate-50 border border-slate-200 rounded-lg px-10 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300 group-hover:border-primary/40" type="datetime-local" name="start_date" id="cm-start-date" required min="<?php echo esc_attr(date('Y-m-d\TH:i')); ?>">
                                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_today</span>
                                            </div>
                                            <div class="relative group">
                                                <input class="w-full bg-slate-50 border border-slate-200 rounded-lg px-10 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300 group-hover:border-primary/40" type="datetime-local" name="end_date" id="cm-end-date" required min="<?php echo esc_attr(date('Y-m-d\TH:i')); ?>">
                                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">event</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="py-4 border-t border-b border-slate-100 space-y-3" id="cm-booking-total" style="<?php echo $is_rental ? 'display:none;' : ''; ?>">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Price</span>
                                        <span class="font-medium">Rs. <?php echo esc_html($price); ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Service Fee</span>
                                        <span class="font-medium">Rs. <?php echo esc_html(round($price * 0.05, 2)); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 font-bold text-lg">
                                        <span>Total</span>
                                        <span class="text-primary" id="cm-total-price">Rs. <?php echo esc_html(round($price * 1.05, 2)); ?></span>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2 py-4">
                                    <input type="checkbox" id="cm-booking-confirm" class="mt-1 rounded text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                                    <label for="cm-booking-confirm" class="text-xs text-slate-500 cursor-pointer select-none">
                                        I confirm that the details provided above are correct and I wish to proceed with this <?php echo $is_rental ? 'booking' : 'purchase'; ?>.
                                    </label>
                                </div>

                                <button type="submit" id="cm-submit-booking-btn" disabled class="btn-premium w-full py-4 bg-slate-200 text-slate-400 font-bold rounded-xl shadow-lg transition-all cursor-not-allowed">
                                    <?php echo $is_rental ? 'Request Booking' : 'Buy Request'; ?>
                                </button>
                                <p class="text-center text-xs text-slate-400">You won't be charged yet</p>
                            </form>
                            <div class="cm-booking-form__message" id="cm-booking-message"></div>
                            <?php else : ?>
                                <div class="text-center py-6 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">error_outline</span>
                                    <p class="text-slate-600 font-bold">Not Available for <?php echo $is_rental ? 'Rent' : 'Sale'; ?></p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <?php 
                                        if ($item_status === 'sold') echo 'This item has already been sold.';
                                        if ($item_status === 'rented') echo 'This item is currently rented. Please check back later.';
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                        <?php elseif (! is_user_logged_in()) : ?>
                            <div class="text-center py-4">
                                <p class="text-slate-500 mb-4">Log in to book this item or contact the seller.</p>
                                <a href="<?php echo esc_url(home_url('/login/')); ?>" class="btn-premium w-full py-4 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all block text-center">
                                    Log In to Continue
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-4">
                                <p class="text-slate-500">This is your listing.</p>
                                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="text-primary font-bold hover:underline">Manage in Dashboard →</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Campus Protection Notice -->
                <div class="bg-primary/5 rounded-xl p-4 border border-primary/20 hover:bg-primary/10 transition-colors duration-300 group">
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-primary group-hover:rotate-12 transition-transform">info</span>
                        <p class="text-sm text-slate-700">
                            <strong>Campus Protection:</strong> Your transaction is covered by our student safety guidelines.
                            <a class="text-primary underline hover:text-primary/80 transition-colors" href="<?php echo esc_url(home_url('/safety/')); ?>">Learn more</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Listings -->
    <?php
    $related_args = array(
        'post_type'      => 'cm_listing',
        'posts_per_page' => 4,
        'post__not_in'   => array($listing_id),
        'meta_query'     => array(
            array('key' => '_cm_approval_status', 'value' => 'approved'),
        ),
        'orderby' => 'rand',
    );
    if (! empty($categories) && ! is_wp_error($categories)) {
        $related_args['tax_query'] = array(
            array('taxonomy' => 'listing_category', 'field' => 'term_id', 'terms' => $categories[0]->term_id),
        );
    }
    $related = new WP_Query($related_args);
    if ($related->have_posts()) :
    ?>
    <section class="mt-16 opacity-0 animate-fade-slide-up stagger-3">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-2xl font-bold">More for your projects</h2>
                <p class="text-slate-500">Other items from students in your area</p>
            </div>
            <a class="text-primary font-bold hover:underline flex items-center gap-1 group" href="<?php echo esc_url(get_post_type_archive_link('cm_listing')); ?>">
                Browse All
                <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while ($related->have_posts()) : $related->the_post();
                $r_id = get_the_ID();
                $r_price = get_post_meta($r_id, '_cm_price', true);
                $r_price_type = get_post_meta($r_id, '_cm_price_type', true);
                $r_is_rental = ($r_price_type === 'per_day' || $r_price_type === 'per_week');
                $r_location = get_post_meta($r_id, '_cm_location', true);
            ?>
            <a href="<?php the_permalink(); ?>" class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-xl hover-glow transition-all duration-500 group cursor-pointer hover:-translate-y-1">
                <div class="aspect-square relative overflow-hidden">
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out" src="<?php echo esc_url(get_the_post_thumbnail_url($r_id, 'cm-listing-card')); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <div class="w-full h-full bg-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-3 right-3 p-1.5 bg-white/90 rounded-full shadow text-slate-500 hover:text-red-500 transition-colors active:scale-90">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs font-bold <?php echo $r_is_rental ? 'text-primary' : 'text-orange-500'; ?> uppercase"><?php echo $r_is_rental ? 'Rental' : 'For Sale'; ?></p>
                    <h4 class="font-bold mt-1 truncate group-hover:text-primary transition-colors"><?php the_title(); ?></h4>
                    <p class="text-lg font-black mt-2">Rs. <?php echo esc_html($r_price); ?> <?php if ($r_is_rental) : ?><span class="text-sm font-normal text-slate-500">/ <?php echo $r_price_type === 'per_day' ? 'day' : 'week'; ?></span><?php endif; ?></p>
                    <?php if ($r_location) : ?>
                        <div class="flex items-center gap-1 mt-2 text-xs text-slate-500">
                            <span class="material-symbols-outlined text-xs">location_on</span>
                            <?php echo esc_html($r_location); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php
endwhile;
get_footer();
