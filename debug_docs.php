<?php
require_once('wp-load.php');

echo "<h2>Verification Document Debugger</h2>";

$username = 'Sachadhi0';
$user = get_user_by('login', $username);

if (!$user) {
    echo "❌ User '$username' not found.<br>";
    exit;
}

$user_id = $user->ID;
echo "User ID: $user_id<br>";
echo "Roles: " . implode(', ', $user->roles) . "<br>";

if ($user->has_cap('upload_files')) {
    echo "✅ User HAS 'upload_files' capability.<br>";
} else {
    echo "❌ User DOES NOT HAVE 'upload_files' capability.<br>";
}

$keys = ['_cm_profile_photo', '_cm_id_card_front', '_cm_id_card_back', '_cm_id_url', '_cm_verification_status'];

foreach ($keys as $key) {
    $val = get_user_meta($user_id, $key, true);
    echo "<b>$key:</b> ";
    if (!$val) {
        echo "<i>Empty</i><br>";
        continue;
    }
    
    echo "$val ";
    if (is_numeric($val)) {
        $url = wp_get_attachment_image_url($val, 'full');
        if ($url) {
            echo "- URL: <a href='$url' target='_blank'>$url</a>";
            $file = get_attached_file($val);
            echo " - File: $file";
            if (file_exists($file)) {
                echo " ✅ Found";
            } else {
                echo " ❌ NOT FOUND ON DISK";
            }
        } else {
            echo "- ❌ No URL found for attachment ID $val";
        }
    }
    echo "<br>";
}

echo "<h3>Upload Directory Check:</h3>";
$upload_dir = wp_upload_dir();
echo "Basedir: " . $upload_dir['basedir'] . "<br>";
echo "Baseurl: " . $upload_dir['baseurl'] . "<br>";

if (is_writable($upload_dir['basedir'])) {
    echo "✅ Upload directory is writable.<br>";
} else {
    echo "❌ Upload directory is NOT writable.<br>";
}

echo "<br><b>Debug complete.</b>";
?>
