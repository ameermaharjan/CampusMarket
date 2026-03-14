<?php
/**
 * Template Name: List an Item
 * Premium Listing Form
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

// Check for edit mode
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$listing = null;
$is_edit = false;

if ($edit_id) {
    $listing = get_post($edit_id);
    if ($listing && $listing->post_type === 'cm_listing') {
        // Verify ownership or admin
        if ((int)$listing->post_author === get_current_user_id() || current_user_can('manage_options')) {
            $is_edit = true;
        } else {
            wp_redirect(home_url('/dashboard/'));
            exit;
        }
    }
}

get_header();

$categories = get_terms(array(
    'taxonomy'   => 'listing_category',
    'hide_empty' => false,
));

// Pre-fill values if editing
$title          = $is_edit ? $listing->post_title : '';
$description    = $is_edit ? $listing->post_content : '';
$intent         = $is_edit ? get_post_meta($edit_id, '_cm_listing_intent', true) : 'sale';
$type           = $is_edit ? get_post_meta($edit_id, '_cm_listing_type', true) : 'item';
$price          = $is_edit ? get_post_meta($edit_id, '_cm_price', true) : '';
$price_type     = $is_edit ? get_post_meta($edit_id, '_cm_price_type', true) : 'fixed';
$condition      = $is_edit ? get_post_meta($edit_id, '_cm_condition', true) : 'good';
$location       = $is_edit ? get_post_meta($edit_id, '_cm_location', true) : '';
$avail_start    = $is_edit ? get_post_meta($edit_id, '_cm_availability_start', true) : '';
$avail_end      = $is_edit ? get_post_meta($edit_id, '_cm_availability_end', true) : '';

// Format for datetime-local
if ($avail_start) $avail_start = date('Y-m-d\TH:i', strtotime($avail_start));
if ($avail_end) $avail_end = date('Y-m-d\TH:i', strtotime($avail_end));
$current_status = $is_edit ? (get_post_meta($edit_id, '_cm_item_status', true) ?: 'active') : 'active';
$image_url      = $is_edit ? get_the_post_thumbnail_url($edit_id, 'medium') : '';

$listing_cats   = $is_edit ? wp_get_object_terms($edit_id, 'listing_category', array('fields' => 'ids')) : array();
$active_cat     = !empty($listing_cats) ? $listing_cats[0] : 0;
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <div class="opacity-0 animate-fade-slide-up">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2"><?php echo $is_edit ? 'Edit Your Listing' : 'List an Item or Service'; ?></h1>
            <p class="text-slate-500"><?php echo $is_edit ? 'Update the details of your listing below.' : 'Fill out the form below. It will be reviewed by an admin before going live.'; ?></p>
        </div>

        <div class="glass-panel rounded-2xl p-8 shadow-sm !transform-none">
            <form id="cm-listing-form" class="space-y-6" enctype="multipart/form-data">
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($edit_id); ?>">
                    
                    <!-- Listing Status (Only for Edit Mode) -->
                    <div class="bg-primary/5 rounded-xl p-6 border border-primary/10">
                        <label class="block text-xs font-bold text-primary uppercase tracking-wider mb-3">Item Availability Status</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="item_status" value="active" <?php checked($current_status, 'active'); ?> class="rounded-full text-primary focus:ring-primary h-4 w-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors">Available</span>
                                    <span class="text-[10px] text-slate-500">Visible in marketplace</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="item_status" value="sold" <?php checked($current_status, 'sold'); ?> class="rounded-full text-primary focus:ring-primary h-4 w-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors">Mark as Sold</span>
                                    <span class="text-[10px] text-slate-500">Item is no longer available</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="item_status" value="rented" <?php checked($current_status, 'rented'); ?> class="rounded-full text-primary focus:ring-primary h-4 w-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors">Mark as Rented</span>
                                    <span class="text-[10px] text-slate-500">Currently being rented by another student</span>
                                </div>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Transaction Intent -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Listing Intent <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="listing_intent" value="sale" <?php checked($intent, 'sale'); ?> class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary peer-checked:bg-primary/5 transition-all text-center hover:bg-slate-50 flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-2xl">shopping_basket</span>
                                <span class="text-sm font-bold">For Sale</span>
                                <p class="text-[10px] text-slate-400">Fixed price payment</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="listing_intent" value="rent" <?php checked($intent, 'rent'); ?> class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary peer-checked:bg-primary/5 transition-all text-center hover:bg-slate-50 flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-2xl">calendar_today</span>
                                <span class="text-sm font-bold">For Rent</span>
                                <p class="text-[10px] text-slate-400">Daily/Weekly rental</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Listing Type (Hidden/Background) -->
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase">Listing Type</p>
                        <p class="text-[10px] text-slate-400">Is this a physical item or a service?</p>
                    </div>
                    <div class="flex gap-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="listing_type" value="item" <?php checked($type, 'item'); ?> class="rounded-full text-primary focus:ring-primary h-4 w-4">
                            <span class="text-sm font-medium">Item</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="listing_type" value="service" <?php checked($type, 'service'); ?> class="rounded-full text-primary focus:ring-primary h-4 w-4">
                            <span class="text-sm font-medium">Service</span>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="cm-listing-title" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="cm-listing-title" name="title" required maxlength="100" placeholder="e.g., Physics Textbook (Halliday 10th Edition)" value="<?php echo esc_attr($title); ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    <p class="text-xs text-slate-400 mt-1">Keep it clear and descriptive</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="cm-listing-desc" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description <span class="text-red-500">*</span></label>
                    <textarea id="cm-listing-desc" name="description" rows="5" required placeholder="Describe your item or service in detail..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none"><?php echo esc_textarea($description); ?></textarea>
                </div>

                <!-- Category -->
                <div>
                    <label for="cm-listing-category" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category <span class="text-red-500">*</span></label>
                    <select id="cm-listing-category" name="category" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                        <option value="">Select a category</option>
                        <?php if (! is_wp_error($categories)) : foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($active_cat, $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Price Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="cm-listing-price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Price (Rs.)</label>
                        <input type="number" id="cm-listing-price" name="price" min="0" step="0.01" placeholder="0.00" value="<?php echo esc_attr($price); ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                        <p class="text-xs text-slate-400 mt-1">Leave 0 for free</p>
                    </div>
                    <div>
                        <label for="cm-listing-price-type" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Price Type</label>
                        <select id="cm-listing-price-type" name="price_type" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                            <option value="fixed" data-intent="sale" <?php selected($price_type, 'fixed'); ?>>Fixed Price</option>
                            <option value="per_day" data-intent="rent" <?php selected($price_type, 'per_day'); ?>>Per Day</option>
                            <option value="per_week" data-intent="rent" <?php selected($price_type, 'per_week'); ?>>Per Week</option>
                            <option value="per_hour" data-intent="rent" <?php selected($price_type, 'per_hour'); ?>>Per Hour</option>
                        </select>
                    </div>
                </div>

                <!-- Condition -->
                <div id="cm-condition-group">
                    <label for="cm-listing-condition" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Condition</label>
                    <select id="cm-listing-condition" name="condition" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                        <option value="new" <?php selected($condition, 'new'); ?>>New</option>
                        <option value="like_new" <?php selected($condition, 'like_new'); ?>>Like New</option>
                        <option value="good" <?php selected($condition, 'good'); ?>>Good</option>
                        <option value="fair" <?php selected($condition, 'fair'); ?>>Fair</option>
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="cm-listing-location" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pickup Location</label>
                    <div class="relative">
                        <input type="text" id="cm-listing-location" name="location" placeholder="e.g., Library, Block A, Canteen" value="<?php echo esc_attr($location); ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all pl-10">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">location_on</span>
                    </div>
                </div>

                <!-- Availability -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="cm-listing-avail-start" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Available From</label>
                        <input type="datetime-local" id="cm-listing-avail-start" name="availability_start" value="<?php echo esc_attr($avail_start); ?>" min="<?php echo esc_attr(date('Y-m-d\TH:i')); ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label for="cm-listing-avail-end" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Available Until</label>
                        <input type="datetime-local" id="cm-listing-avail-end" name="availability_end" value="<?php echo esc_attr($avail_end); ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Photo</label>
                    <div class="border-2 border-dashed border-slate-300 hover:border-primary rounded-xl p-8 text-center cursor-pointer transition-all duration-300 hover:bg-primary/5 relative" id="cm-upload-area">
                        <input type="file" id="cm-listing-image" name="listing_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div id="cm-upload-content" style="<?php echo $image_url ? 'display:none;' : ''; ?>">
                            <span class="material-symbols-outlined text-4xl text-slate-400 mb-2 block">add_photo_alternate</span>
                            <p class="text-sm font-medium text-slate-700">Click or drag to upload an image</p>
                            <p class="text-xs text-slate-400 mt-1">JPG, PNG, GIF — Max 2MB</p>
                        </div>
                        <img id="cm-image-preview" class="max-h-48 mx-auto rounded-lg <?php echo $image_url ? '' : 'hidden'; ?>" src="<?php echo esc_url($image_url); ?>" alt="Preview">
                    </div>
                </div>

                <!-- Submit -->
                <div class="pt-4">
                    <button type="submit" id="cm-listing-submit" class="btn-premium w-full py-4 bg-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:shadow-primary/40 transition-all text-sm">
                        <?php echo $is_edit ? 'Update Listing Details' : 'Submit Listing for Review'; ?>
                    </button>
                    <p class="text-xs text-slate-400 text-center mt-3">Your listing will be reviewed by an admin before being published.</p>
                </div>

                <div id="cm-listing-message"></div>
            </form>
        </div>
    </div>
</div>

<?php get_footer(); ?>
