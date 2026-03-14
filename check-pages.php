<?php
require_once('wp-load.php');

$pages = get_pages(array(
    'post_status' => 'publish,private,draft'
));

foreach ($pages as $page) {
    $template = get_post_meta($page->ID, '_wp_page_template', true);
    echo "ID: {$page->ID} | Slug: {$page->post_name} | Template: {$template} | Status: {$page->post_status}\n";
}

// Check if verification-pending exists
$exists = get_page_by_path('verification-pending');
if (!$exists) {
    echo "\nVerification Pending page does not exist. Creating it...\n";
    $page_id = wp_insert_post(array(
        'post_title'   => 'Verification Pending',
        'post_name'    => 'verification-pending',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ));
    if ($page_id) {
        update_post_meta($page_id, '_wp_page_template', 'page-templates/page-verification-pending.php');
        echo "Created page ID: $page_id with template page-templates/page-verification-pending.php\n";
    } else {
        echo "Failed to create page.\n";
    }
} else {
    echo "\nVerification Pending page exists (ID: {$exists->ID}). Updating template...\n";
    update_post_meta($exists->ID, '_wp_page_template', 'page-templates/page-verification-pending.php');
}
