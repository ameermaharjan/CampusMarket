<?php
require_once('wp-load.php');

$user = get_user_by('email', 'alex.final@university.edu');
if ($user) {
    update_user_meta($user->ID, '_cm_verification_status', 'pending');
    update_user_meta($user->ID, '_cm_verified', '0');
    echo "User {$user->user_email} (ID: {$user->ID}) status set to pending.\n";
} else {
    echo "User not found.\n";
}
