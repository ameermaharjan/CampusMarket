<?php

// Register Custom Post Type
function campus_market_post_type() {

    register_post_type('listing', array(
        'labels' => array(
            'name' => 'Listings',
            'singular_name' => 'Listing'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'listings'),
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    ));

}
add_action('init', 'campus_market_post_type');


// Register Taxonomy (Sell, Rent, Exchange, Service)
function campus_market_taxonomy() {

    register_taxonomy('listing_type', 'listing', array(
        'label' => 'Listing Type',
        'rewrite' => array('slug' => 'listing-type'),
        'hierarchical' => true,
        'show_in_rest' => true,
    ));

}
add_action('init', 'campus_market_taxonomy');


// Add Custom Fields (Price + Condition)
function campus_market_add_meta_box() {
    add_meta_box(
        'listing_details',
        'Listing Details',
        'campus_market_meta_box_callback',
        'listing'
    );
}
add_action('add_meta_boxes', 'campus_market_add_meta_box');


function campus_market_meta_box_callback($post) {
    $price = get_post_meta($post->ID, '_price', true);
    $condition = get_post_meta($post->ID, '_condition', true);
    ?>

    <label>Price:</label>
    <input type="text" name="price" value="<?php echo $price; ?>" />

    <br><br>

    <label>Condition:</label>
    <select name="condition">
        <option value="New" <?php selected($condition, 'New'); ?>>New</option>
        <option value="Used" <?php selected($condition, 'Used'); ?>>Used</option>
    </select>

    <?php
}


// Save Meta Box Data
function campus_market_save_meta($post_id) {

    if (array_key_exists('price', $_POST)) {
        update_post_meta($post_id, '_price', $_POST['price']);
    }

    if (array_key_exists('condition', $_POST)) {
        update_post_meta($post_id, '_condition', $_POST['condition']);
    }

}
add_action('save_post', 'campus_market_save_meta');



// Frontend Listing Submission Shortcode
function campus_market_frontend_form() {

    if (!is_user_logged_in()) {
        return "<p>You must be logged in to submit a listing.</p>";
    }

    ob_start();

    if (isset($_POST['submit_listing'])) {

        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = sanitize_text_field($_POST['price']);
        $condition = sanitize_text_field($_POST['condition']);
        $type = intval($_POST['listing_type']);

        $new_post = array(
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => 'pending',
            'post_type'    => 'listing',
            'post_author'  => get_current_user_id()
        );

        $post_id = wp_insert_post($new_post);

        if ($post_id) {

            update_post_meta($post_id, '_price', $price);
            update_post_meta($post_id, '_condition', $condition);

            wp_set_object_terms($post_id, array($type), 'listing_type');

            echo "<p>Listing submitted successfully! Waiting for admin approval.</p>";
        }
    }

    ?>

    <form method="post">

        <p>
            <label>Title</label><br>
            <input type="text" name="title" required>
        </p>

        <p>
            <label>Description</label><br>
            <textarea name="description" required></textarea>
        </p>

        <p>
            <label>Price</label><br>
            <input type="text" name="price" required>
        </p>

        <p>
            <label>Condition</label><br>
            <select name="condition">
                <option value="New">New</option>
                <option value="Used">Used</option>
            </select>
        </p>

        <p>
            <label>Listing Type</label><br>
            <?php
            $terms = get_terms(array('taxonomy' => 'listing_type', 'hide_empty' => false));
            foreach ($terms as $term) {
                echo '<input type="radio" name="listing_type" value="'.$term->term_id.'" required> '.$term->name.'<br>';
            }
            ?>
        </p>

        <p>
            <input type="submit" name="submit_listing" value="Submit Listing" class="button">
        </p>

    </form>

    <?php

    return ob_get_clean();
}

add_shortcode('submit_listing_form', 'campus_market_frontend_form');
?>