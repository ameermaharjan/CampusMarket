<!DOCTYPE html>
<html>
<head>
    <title><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body>

<header style="background:#0073aa; padding:15px; color:white;">
    <div class="container">
        <h2>Campus Market</h2>
        <a href="<?php echo home_url('/listings'); ?>" style="color:white;">Browse Listings</a>
    </div>
</header>

<div class="container">