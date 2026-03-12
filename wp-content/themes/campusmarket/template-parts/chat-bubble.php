<?php

/**
 * Chat Bubble Template Part
 *
 * @package CampusMarket
 */

if (! defined('ABSPATH')) {
    exit;
}

$sender_id = get_post_meta(get_the_ID(), '_cm_sender_id', true);
$is_mine   = (int) $sender_id === get_current_user_id();
$sender    = get_userdata($sender_id);
?>

<div class="cm-chat-bubble <?php echo $is_mine ? 'cm-chat-bubble--sent' : 'cm-chat-bubble--received'; ?>" data-message-id="<?php the_ID(); ?>">
    <?php if (! $is_mine) : ?>
        <div class="cm-chat-bubble__avatar">
            <?php echo get_avatar($sender_id, 32); ?>
        </div>
    <?php endif; ?>

    <div class="cm-chat-bubble__content">
        <?php if (! $is_mine) : ?>
            <span class="cm-chat-bubble__sender"><?php echo $sender ? esc_html($sender->display_name) : 'Unknown'; ?></span>
        <?php endif; ?>
        <div class="cm-chat-bubble__text">
            <?php the_content(); ?>
        </div>
        <span class="cm-chat-bubble__time"><?php echo esc_html(get_the_date('g:i a')); ?></span>
    </div>
</div>