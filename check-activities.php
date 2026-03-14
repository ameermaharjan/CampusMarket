<?php
require 'wp-load.php';
$user_id = wp_get_current_user()->ID;

echo "User ID: " . $user_id . "\n\n";

$args = array(
    'post_type' => 'cm_booking',
    'posts_per_page' => 10,
    'meta_query' => array(
        'relation' => 'OR',
        array('key' => '_cm_renter_id', 'value' => strval($user_id)),
        array('key' => '_cm_owner_id', 'value' => strval($user_id))
    )
);
$q = new WP_Query($args);

echo "Query SQL: " . $q->request . "\n\n";
echo "Found Posts: " . $q->found_posts . "\n\n";

if ($q->have_posts()) {
    while($q->have_posts()) {
        $q->the_post();
        echo "ID: " . get_the_ID() . "\n";
        echo "Status: " . get_post_meta(get_the_ID(), '_cm_status', true) . "\n";
        echo "Owner: " . get_post_meta(get_the_ID(), '_cm_owner_id', true) . "\n";
        echo "Renter: " . get_post_meta(get_the_ID(), '_cm_renter_id', true) . "\n";
        echo "-------------------\n";
    }
} else {
    echo "No posts returned by meta query. Let's try grabbing ANY cm_booking to see if they exist:\n";
    $q2 = new WP_Query(['post_type'=>'cm_booking','posts_per_page'=>3]);
    echo "Total ANY bookings found: " . $q2->found_posts . "\n";
    while($q2->have_posts()) {
        $q2->the_post();
        echo "ID: " . get_the_ID() . "\n";
        echo "Owner: '" . get_post_meta(get_the_ID(), '_cm_owner_id', true) . "'\n";
        echo "Renter: '" . get_post_meta(get_the_ID(), '_cm_renter_id', true) . "'\n";
    }
}
