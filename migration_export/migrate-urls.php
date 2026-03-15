<?php
/**
 * CampusMarket Domain Migration Tool
 * Updates all database instances of localhost/campusmarket to campusmarket.lovestoblog.com
 */

require_once('wp-load.php');

global $wpdb;

$old_url = 'http://localhost/campusmarket';
$new_url = 'http://campusmarket.lovestoblog.com';

echo "<h2>CampusMarket Migration Tool</h2>";
echo "Migrating from: <b>$old_url</b> to: <b>$new_url</b><br><br>";

// Tables to update
$tables = [
    $wpdb->options => ['option_value'],
    $wpdb->posts => ['post_content', 'guid', 'post_excerpt'],
    $wpdb->postmeta => ['meta_value'],
    $wpdb->comments => ['comment_content', 'comment_author_url'],
    $wpdb->links => ['link_url', 'link_image'],
    $wpdb->termmeta => ['meta_value'],
    $wpdb->usermeta => ['meta_value'],
];

foreach ($tables as $table => $columns) {
    foreach ($columns as $column) {
        $query = $wpdb->prepare(
            "UPDATE $table SET $column = REPLACE($column, %s, %s) WHERE $column LIKE %s",
            $old_url,
            $new_url,
            '%' . $wpdb->esc_like($old_url) . '%'
        );
        $rows = $wpdb->query($query);
        echo "Updated Table: <b>$table</b>, Column: <b>$column</b>. Rows affected: $rows<br>";
    }
}

// Special case: wp_options siteurl and home
$wpdb->query($wpdb->prepare("UPDATE $wpdb->options SET option_value = %s WHERE option_name = 'siteurl'", $new_url));
$wpdb->query($wpdb->prepare("UPDATE $wpdb->options SET option_value = %s WHERE option_name = 'home'", $new_url));

echo "<br><b>Migration complete!</b> Please delete this file for security.";
