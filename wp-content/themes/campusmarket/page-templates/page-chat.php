<?php
/**
 * Template Name: Chat Page
 * Premium Chat & Messaging Interface
 *
 * @package CampusMarket
 */

if (! is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$chat_with = isset($_GET['with']) ? absint($_GET['with']) : 0;
$conversations = cm_get_user_conversations($current_user->ID);
?>

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">
    <div class="glass-panel rounded-2xl overflow-hidden shadow-lg border border-slate-200 h-[calc(100vh-160px)] flex">

        <!-- Contacts Sidebar -->
        <div class="w-80 border-r border-slate-200 flex flex-col bg-white/60 opacity-0 animate-fade-slide-up stagger-1">
            <!-- Search Contacts -->
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 mb-3">Messages</h3>
                <div class="relative">
                    <input class="w-full pl-10 pr-4 py-2.5 bg-slate-100 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/30 placeholder-slate-400 transition-all" placeholder="Search conversations..." type="text" id="cm-chat-search">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                </div>
            </div>

            <!-- Conversation List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar" id="cm-conversation-list">
                <?php if (!empty($conversations)) : ?>
                    <?php foreach ($conversations as $conv) :
                        $other_user = get_userdata($conv['other_user_id']);
                        if (!$other_user) continue;
                        $is_active = ($chat_with == $conv['other_user_id']);
                    ?>
                        <a href="<?php echo esc_url(home_url('/chat/?with=' . $conv['other_user_id'])); ?>"
                           class="flex items-center gap-3 p-4 cursor-pointer transition-all duration-300 border-b border-slate-50 <?php echo $is_active ? 'bg-primary/10 border-l-4 border-l-primary' : 'hover:bg-slate-50'; ?>">
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-full bg-slate-200 overflow-hidden ring-2 ring-white">
                                    <?php echo get_avatar($conv['other_user_id'], 48, '', '', array('class' => 'w-full h-full object-cover')); ?>
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-semibold text-sm text-slate-900 truncate"><?php echo esc_html($other_user->display_name); ?></span>
                                    <span class="text-[10px] text-slate-400 shrink-0"><?php echo !empty($conv['last_date']) ? esc_html(cm_time_ago($conv['last_date'])) : ''; ?></span>
                                </div>
                                <p class="text-xs text-slate-500 truncate"><?php echo isset($conv['last_message']) ? esc_html($conv['last_message']) : 'Start a conversation'; ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="flex flex-col items-center justify-center h-full p-6 text-center">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-3">forum</span>
                        <p class="text-sm text-slate-500 font-medium">No conversations yet</p>
                        <p class="text-xs text-slate-400 mt-1">Start by messaging a seller!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-slate-50/30 opacity-0 animate-fade-slide-up stagger-2">
            <?php if ($chat_with && ($chat_partner = get_userdata($chat_with))) : ?>
                <!-- Chat Header -->
                <div class="p-4 border-b border-slate-200 bg-white/80 backdrop-blur-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden ring-2 ring-primary/10">
                            <?php echo get_avatar($chat_with, 40, '', '', array('class' => 'w-full h-full object-cover')); ?>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm flex items-center gap-1">
                                <?php echo esc_html($chat_partner->display_name); ?>
                                <?php if (cm_is_user_verified($chat_with)) : ?>
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                                <?php endif; ?>
                            </h4>
                            <p class="text-xs text-green-500 font-medium">Online</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-primary transition-all" title="Search">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                        <button class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-primary transition-all" title="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <!-- Messages -->
                <?php $conversation_id = cm_generate_conversation_id($current_user->ID, $chat_with); ?>
                <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar" id="cm-messages-container" data-conversation-id="<?php echo esc_attr($conversation_id); ?>" data-other-user="<?php echo esc_attr($chat_with); ?>">
                    <?php
                    $messages_query = cm_get_conversation($conversation_id);
                    if ($messages_query->have_posts()) :
                        while ($messages_query->have_posts()) : $messages_query->the_post();
                            $is_mine = (get_the_author_meta('ID') == $current_user->ID);
                    ?>
                        <div class="flex w-full <?php echo $is_mine ? 'justify-end' : 'justify-start'; ?> mb-4 animate-fade-slide-up" data-message-id="<?php echo get_the_ID(); ?>">
                            <div class="flex gap-3 max-w-[80%] <?php echo $is_mine ? 'flex-row-reverse' : 'flex-row'; ?>">
                                
                                <!-- Avatar -->
                                <?php if (!$is_mine) : ?>
                                    <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden ring-2 ring-white shadow-sm shrink-0 mt-auto mb-1">
                                        <?php echo get_avatar($chat_with, 32, '', '', array('class' => 'w-full h-full object-cover')); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Bubble -->
                                <div class="flex flex-col <?php echo $is_mine ? 'items-end' : 'items-start'; ?>">
                                    <?php if (!$is_mine) : ?>
                                        <span class="text-[10px] font-bold text-slate-500 mb-1 ml-1"><?php echo esc_html($chat_partner->display_name); ?></span>
                                    <?php endif; ?>
                                    
                                    <div class="px-5 py-3 text-sm shadow-md <?php echo $is_mine ? 'bg-gradient-to-br from-primary to-blue-700 text-white rounded-2xl rounded-br-sm shadow-primary/20' : 'bg-white text-slate-800 border border-slate-100 rounded-2xl rounded-bl-sm'; ?>">
                                        <p class="leading-relaxed whitespace-pre-wrap m-0"><?php echo esc_html(get_the_content()); ?></p>
                                    </div>
                                    
                                    <span class="text-[9px] font-semibold text-slate-400 mt-1 <?php echo $is_mine ? 'mr-1' : 'ml-1'; ?>"><?php echo esc_html(get_the_date('M j, g:i a')); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <div class="flex flex-col items-center justify-center h-full text-center" id="cm-chat-empty">
                            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-3xl text-primary">waving_hand</span>
                            </div>
                            <h3 class="font-bold text-lg mb-1">Start the Conversation</h3>
                            <p class="text-sm text-slate-500 max-w-xs">Say hello to <?php echo esc_html($chat_partner->display_name); ?>!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="p-4 border-t border-slate-200 bg-white/80 backdrop-blur-sm">
                    <form id="cm-send-message-form" class="flex items-center gap-2">
                        <input type="hidden" name="recipient_id" value="<?php echo esc_attr($chat_with); ?>">
                        <div class="flex-1 relative">
                            <input class="w-full pl-4 pr-12 py-3 bg-slate-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/30 placeholder-slate-400 transition-all" id="cm-message-input" name="message" placeholder="Type your message..." autocomplete="off">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined">mood</span>
                            </button>
                        </div>
                        <button type="submit" class="action-button bg-primary text-white p-3 rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20" id="cm-send-btn">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                    <p class="text-center text-[10px] text-slate-400 mt-2 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[10px]">lock</span>
                        Messages are private between you and the recipient
                    </p>
                </div>

            <?php else : ?>
                <!-- No chat selected -->
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 opacity-0 animate-fade-slide-up">
                    <div class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center mb-6 animate-float">
                        <span class="material-symbols-outlined text-5xl text-primary">chat_bubble</span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Your Messages</h2>
                    <p class="text-slate-500 max-w-md">Select a conversation from the sidebar or start a new one by messaging a seller from their listing.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto scroll to bottom of chat
(function(){
    var msgs = document.getElementById('cm-messages-container');
    if(msgs) msgs.scrollTop = msgs.scrollHeight;
})();
</script>

<?php get_footer(); ?>
