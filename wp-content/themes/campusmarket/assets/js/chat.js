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
        const cls = msg.is_mine ? 'cm-chat-bubble--sent' : 'cm-chat-bubble--received';
        let html = '<div class="cm-chat-bubble ' + cls + '" data-message-id="' + msg.id + '">';
        if (!msg.is_mine) {
            html += '<div class="cm-chat-bubble__avatar"><img src="' + msg.avatar + '" width="32" height="32" style="border-radius:50%"></div>';
        }
        html += '<div class="cm-chat-bubble__content">';
        if (!msg.is_mine) {
            html += '<span class="cm-chat-bubble__sender">' + msg.sender + '</span>';
        }
        html += '<div class="cm-chat-bubble__text"><p>' + msg.content + '</p></div>';
        html += '<span class="cm-chat-bubble__time">' + msg.date + '</span>';
        html += '</div></div>';
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
