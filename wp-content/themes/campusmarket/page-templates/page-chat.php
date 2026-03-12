<?php

/**
 * Template Name: Chat
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(wp_login_url(home_url('/chat/')));
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$conversations   = cm_get_user_conversations($current_user_id);
$active_conv     = isset($_GET['conv']) ? sanitize_text_field($_GET['conv']) : '';
$with_user       = isset($_GET['with']) ? intval($_GET['with']) : 0;

// If opening chat with a specific user, generate conversation ID
if ($with_user > 0 && empty($active_conv)) {
    $active_conv = cm_generate_conversation_id($current_user_id, $with_user);
}

// If no active conversation, default to first
if (empty($active_conv) && ! empty($conversations)) {
    $active_conv = $conversations[0]['conversation_id'];
}
?>

<div class="cm-section cm-chat-section">
    <div class="cm-container">
        <header class="cm-page-header">
            <h1 class="cm-page-header__title">💬 Messages</h1>
        </header>

        <div class="cm-chat-layout" id="cm-chat-layout">
            <!-- Conversation List -->
            <div class="cm-chat-sidebar" id="cm-chat-sidebar">
                <div class="cm-chat-sidebar__header">
                    <h3>Conversations</h3>
                </div>
                <div class="cm-chat-sidebar__list" id="cm-conversation-list">
                    <?php if (! empty($conversations)) : ?>
                        <?php foreach ($conversations as $conv) : ?>
                            <a href="?conv=<?php echo esc_attr($conv['conversation_id']); ?>"
                                class="cm-conv-item <?php echo $active_conv === $conv['conversation_id'] ? 'cm-conv-item--active' : ''; ?>"
                                data-conv-id="<?php echo esc_attr($conv['conversation_id']); ?>"
                                data-other-user="<?php echo esc_attr($conv['other_user_id']); ?>">
                                <img src="<?php echo esc_url($conv['other_user_avatar']); ?>" class="cm-conv-item__avatar" alt="">
                                <div class="cm-conv-item__info">
                                    <span class="cm-conv-item__name"><?php echo esc_html($conv['other_user_name']); ?></span>
                                    <span class="cm-conv-item__preview"><?php echo esc_html($conv['last_message']); ?></span>
                                </div>
                                <?php if ($conv['unread_count'] > 0) : ?>
                                    <span class="cm-conv-item__badge"><?php echo esc_html($conv['unread_count']); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="cm-chat-sidebar__empty">
                            <p>No conversations yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="cm-chat-window" id="cm-chat-window">
                <?php if (! empty($active_conv)) :
                    $other_id = $with_user ?: cm_get_other_user_in_conversation($active_conv, $current_user_id);
                    $other_user = get_userdata($other_id);
                ?>
                    <div class="cm-chat-window__header">
                        <div class="cm-chat-window__user">
                            <?php echo get_avatar($other_id, 36); ?>
                            <span><?php echo $other_user ? esc_html($other_user->display_name) : 'Unknown'; ?></span>
                            <?php if (cm_is_user_verified($other_id)) : ?>
                                <span class="cm-verified-badge">✔</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="cm-chat-window__messages" id="cm-messages-container"
                        data-conversation-id="<?php echo esc_attr($active_conv); ?>"
                        data-other-user="<?php echo esc_attr($other_id); ?>">
                        <?php
                        $messages = cm_get_conversation($active_conv);
                        cm_mark_as_read($active_conv, $current_user_id);
                        if ($messages->have_posts()) :
                            while ($messages->have_posts()) : $messages->the_post();
                                get_template_part('template-parts/chat-bubble');
                            endwhile;
                            wp_reset_postdata();
                        else :
                        ?>
                            <div class="cm-chat-window__empty" id="cm-chat-empty">
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="cm-chat-window__input">
                        <form id="cm-send-message-form" class="cm-chat-input-form">
                            <input type="hidden" name="conversation_id" value="<?php echo esc_attr($active_conv); ?>">
                            <input type="hidden" name="receiver_id" value="<?php echo esc_attr($other_id); ?>">
                            <textarea id="cm-message-input" name="message" class="cm-chat-input" placeholder="Type a message..." rows="1" required></textarea>
                            <button type="submit" class="cm-btn cm-btn--primary cm-chat-send-btn" id="cm-send-btn">
                                Send
                            </button>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="cm-chat-window__placeholder">
                        <div class="cm-empty-state">
                            <div class="cm-empty-state__icon">💬</div>
                            <h3>Select a conversation</h3>
                            <p>Choose a conversation from the sidebar, or start a new one by contacting a seller.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
