/**
 * CampusMarket — Chat JS
 */
(function ($) {
    'use strict';

    const msgContainer = document.getElementById('cm-messages-container');
    const sendForm = document.getElementById('cm-send-message-form');
    if (!msgContainer || !sendForm) return;

    const convId = msgContainer.dataset.conversationId;
    const otherUser = msgContainer.dataset.otherUser;
    let lastMessageId = 0;
    let pollInterval;

    // Get last message ID from existing bubbles
    const existingBubbles = msgContainer.querySelectorAll('.cm-chat-bubble');
    if (existingBubbles.length > 0) {
        lastMessageId = parseInt(existingBubbles[existingBubbles.length - 1].dataset.messageId) || 0;
    }

    // Scroll to bottom
    function scrollToBottom() {
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }
    scrollToBottom();

    // ─── Send Message ───────────────────────────────────
    sendForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('cm-message-input');
        const message = input.value.trim();
        if (!message) return;

        const btn = document.getElementById('cm-send-btn');
        btn.disabled = true;

        $.post(cmData.ajaxUrl, {
            action: 'cm_send_message',
            nonce: cmData.nonce,
            conversation_id: convId,
            receiver_id: otherUser,
            message: message
        }, function (response) {
            if (response.success) {
                input.value = '';
                // Remove empty state
                var emptyEl = document.getElementById('cm-chat-empty');
                if (emptyEl) emptyEl.remove();
                fetchNewMessages();
            }
            btn.disabled = false;
        });
    });

    // ─── Fetch New Messages (Polling) ───────────────────
    function fetchNewMessages() {
        $.post(cmData.ajaxUrl, {
            action: 'cm_fetch_messages',
            nonce: cmData.nonce,
            conversation_id: convId,
            after_id: lastMessageId
        }, function (response) {
            if (response.success && response.data.messages.length > 0) {
                response.data.messages.forEach(function (msg) {
                    if (msg.id > lastMessageId) {
                        appendMessage(msg);
                        lastMessageId = msg.id;
                    }
                });
                scrollToBottom();
            }
        });
    }

    function appendMessage(msg) {
        let html = '';
        html += '<div class="flex w-full ' + (msg.is_mine ? 'justify-end' : 'justify-start') + ' mb-4 animate-fade-slide-up" data-message-id="' + msg.id + '">';
        html += '<div class="flex gap-3 max-w-[80%] ' + (msg.is_mine ? 'flex-row-reverse' : 'flex-row') + '">';
        
        // Avatar
        if (!msg.is_mine) {
            html += '<div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden ring-2 ring-white shadow-sm shrink-0 mt-auto mb-1">';
            html += '<img src="' + msg.avatar + '" class="w-full h-full object-cover" alt="avatar" />';
            html += '</div>';
        }

        // Bubble Container
        html += '<div class="flex flex-col ' + (msg.is_mine ? 'items-end' : 'items-start') + '">';
        if (!msg.is_mine) {
            html += '<span class="text-[10px] font-bold text-slate-500 mb-1 ml-1">' + msg.sender + '</span>';
        }
        
        let bubbleClass = msg.is_mine 
            ? 'bg-gradient-to-br from-primary to-blue-700 text-white rounded-2xl rounded-br-sm shadow-primary/20' 
            : 'bg-white text-slate-800 border border-slate-100 rounded-2xl rounded-bl-sm';
            
        html += '<div class="px-5 py-3 text-sm shadow-md ' + bubbleClass + '">';
        html += '<p class="leading-relaxed whitespace-pre-wrap m-0">' + msg.content + '</p>';
        html += '</div>';
        
        html += '<span class="text-[9px] font-semibold text-slate-400 mt-1 ' + (msg.is_mine ? 'mr-1' : 'ml-1') + '">' + msg.date + '</span>';
        
        html += '</div></div></div>';
        
        msgContainer.insertAdjacentHTML('beforeend', html);
    }

    // ─── Enter to Send ──────────────────────────────────
    var msgInput = document.getElementById('cm-message-input');
    if (msgInput) {
        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Start polling every 5 seconds
    pollInterval = setInterval(fetchNewMessages, 5000);

    // Clean up on page leave
    window.addEventListener('beforeunload', function () {
        clearInterval(pollInterval);
    });

})(jQuery);
