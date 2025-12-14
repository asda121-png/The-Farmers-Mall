<!-- RETAILER MESSAGING WIDGET - Include this in all retailer pages -->
<style>
    .retailer-msg-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    }

    .retailer-msg-fab:hover {
        transform: scale(1.1);
    }

    .retailer-msg-widget {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 400px;
        height: 600px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        z-index: 9998;
    }

    .retailer-msg-widget.open {
        display: flex;
    }

    .retailer-msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 18px;
        font-size: 14px;
        word-break: break-word;
        margin-bottom: 8px;
    }

    .retailer-msg-bubble-received {
        background-color: #f0f2f5;
        border-bottom-left-radius: 4px;
    }

    .retailer-msg-bubble-sent {
        background-color: #2E7D32;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .retailer-chat-list-item:hover {
        background-color: #f3f4f6;
    }

    .retailer-msg-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #EF4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        border: 2px solid white;
    }

    .retailer-unread-indicator {
        background-color: #EF4444;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: bold;
        margin-left: auto;
    }
</style>

<!-- CHAT FLOATING BUTTON -->
<button id="retailerMsgFab" class="retailer-msg-fab w-16 h-16 bg-green-600 rounded-full flex items-center justify-center text-white hover:bg-green-700 transition-colors">
    <i class="fas fa-comment-dots text-2xl"></i>
    <span id="retailerMsgBadge" class="retailer-msg-badge hidden">0</span>
</button>

<!-- CHAT WIDGET -->
<div id="retailerMsgWidget" class="retailer-msg-widget">

    <!-- CHAT LIST VIEW -->
    <div id="retailerChatList" class="flex flex-col h-full">
        <div class="p-4 bg-green-600 text-white font-bold flex justify-between items-center">
            <div>
                <h3 class="text-lg">Customer Messages</h3>
                <p class="text-xs opacity-90 font-normal">Chat with your customers</p>
            </div>
            <button id="retailerCloseChat" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-3 border-b">
            <input type="text" id="retailerSearchChat" placeholder="Search conversations..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="retailerChatListContainer">
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Loading conversations...</p>
            </div>
        </div>
    </div>

    <!-- CHAT CONVERSATION VIEW -->
    <div id="retailerChatConversation" class="hidden flex-col h-full">
        <div class="p-4 border-b flex items-center gap-3 bg-white">
            <button id="retailerBackToList" class="text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
            <img id="retailerChatHeaderImg" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            <div class="flex-1">
                <h4 id="retailerChatHeaderName" class="font-bold text-sm"></h4>
                <p class="text-xs text-gray-500" id="retailerChatHeaderStatus">Online</p>
            </div>
        </div>

        <div id="retailerChatMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-2"></div>

        <div class="p-4 border-t bg-white flex gap-2">
            <input id="retailerMessageInput" type="text"
                class="flex-1 bg-gray-100 rounded-full px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="Type a message...">
            <button id="retailerSendMessageBtn"
                class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center hover:bg-green-700 transition-colors">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
    // Retailer Messaging Widget JavaScript
    (function() {
        let retailerCurrentChatPartnerId = null;
        let retailerCurrentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        let retailerMessageCheckInterval = null;
        let retailerChatListInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            const msgFab = document.getElementById('retailerMsgFab');
            const msgWidget = document.getElementById('retailerMsgWidget');
            const closeChat = document.getElementById('retailerCloseChat');
            const sendBtn = document.getElementById('retailerSendMessageBtn');
            const msgInput = document.getElementById('retailerMessageInput');
            const backToList = document.getElementById('retailerBackToList');
            const searchInput = document.getElementById('retailerSearchChat');

            // Toggle widget
            msgFab.addEventListener('click', () => {
                const isOpen = msgWidget.classList.contains('open');
                if (!isOpen) {
                    msgWidget.classList.add('open');
                    retailerLoadChatList();
                    // Start polling for new messages
                    retailerChatListInterval = setInterval(retailerLoadChatList, 5000);
                } else {
                    msgWidget.classList.remove('open');
                    clearInterval(retailerChatListInterval);
                    clearInterval(retailerMessageCheckInterval);
                }
            });

            closeChat.addEventListener('click', () => {
                msgWidget.classList.remove('open');
                clearInterval(retailerChatListInterval);
                clearInterval(retailerMessageCheckInterval);
            });

            sendBtn.addEventListener('click', retailerSendMessage);
            msgInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    retailerSendMessage();
                }
            });

            backToList.addEventListener('click', retailerShowChatList);

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const chatItems = document.querySelectorAll('.retailer-chat-list-item');
                chatItems.forEach(item => {
                    const name = item.dataset.partnerName.toLowerCase();
                    const message = item.querySelector('.retailer-last-msg')?.textContent.toLowerCase() || '';
                    if (name.includes(searchTerm) || message.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Load unread count on page load
            retailerUpdateUnreadBadge();
            setInterval(retailerUpdateUnreadBadge, 10000); // Check every 10 seconds
        });

        async function retailerLoadChatList() {
            const container = document.getElementById('retailerChatListContainer');
            try {
                const res = await fetch('../api/get-chat-list.php');

                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const data = await res.json();
                console.log('Chat list response:', data);

                if (data.success && data.chats) {
                    const container = document.getElementById('retailerChatListContainer');

                    if (data.chats.length === 0) {
                        container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No conversations yet</p>
                        </div>
                    `;
                        return;
                    }

                    container.innerHTML = data.chats.map(chat => {
                        const imgSrc = chat.profile_img || '../images/default-avatar.svg';
                        return `
                    <div class="retailer-chat-list-item p-3 rounded-lg cursor-pointer flex items-center gap-3 transition-colors"
                         data-partner-id="${chat.partner_id}"
                         data-partner-name="${chat.partner_name}"
                         data-partner-img="${imgSrc}">
                        <div class="relative">
                            <img src="${imgSrc}" 
                                 onerror="this.src='../images/default-avatar.svg'" 
                                 class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 bg-gray-100">
                            ${chat.is_online ? '<span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>' : ''}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-1">
                                <h4 class="font-bold text-sm truncate">${chat.partner_name}</h4>
                                <span class="text-xs text-gray-400">${retailerFormatTime(chat.last_message_time)}</span>
                            </div>
                            <p class="text-xs text-gray-600 truncate retailer-last-msg">
                                ${chat.last_message_sender ? chat.last_message_sender + ': ' : ''}${chat.last_message}
                            </p>
                        </div>
                        ${chat.unread_count > 0 ? `<span class="retailer-unread-indicator">${chat.unread_count}</span>` : ''}
                    </div>
                `;
                    }).join('');

                    // Add click handlers
                    container.querySelectorAll('.retailer-chat-list-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const partnerId = item.dataset.partnerId;
                            const partnerName = item.dataset.partnerName;
                            const partnerImg = item.dataset.partnerImg;
                            retailerOpenConversation(partnerId, partnerName, partnerImg);
                        });
                    });
                } else {
                    container.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p class="font-bold">Error loading chats</p>
                        <p class="text-sm mt-2">${data.error || 'Unknown error'}</p>
                    </div>
                `;
                }
            } catch (err) {
                console.error('Error loading chat list:', err);
                container.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p class="font-bold">Connection Error</p>
                    <p class="text-sm mt-2">${err.message}</p>
                    <button onclick="retailerLoadChatList()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                        Retry
                    </button>
                </div>
            `;
            }
        }

        async function retailerUpdateUnreadBadge() {
            try {
                const res = await fetch('../api/get-chat-list.php');
                const data = await res.json();

                if (data.success && data.chats) {
                    const totalUnread = data.chats.reduce((sum, chat) => sum + chat.unread_count, 0);
                    const badge = document.getElementById('retailerMsgBadge');

                    if (totalUnread > 0) {
                        badge.textContent = totalUnread > 99 ? '99+' : totalUnread;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('Error updating badge:', err);
            }
        }

        function retailerShowChatList() {
            document.getElementById('retailerChatList').classList.remove('hidden');
            document.getElementById('retailerChatConversation').classList.add('hidden');
            retailerCurrentChatPartnerId = null;
            document.getElementById('retailerMessageInput').value = '';
            clearInterval(retailerMessageCheckInterval);
            retailerLoadChatList();
        }

        function retailerOpenConversation(partnerId, partnerName, partnerImg) {
            retailerCurrentChatPartnerId = partnerId;
            document.getElementById('retailerChatList').classList.add('hidden');
            document.getElementById('retailerChatConversation').classList.remove('hidden');
            document.getElementById('retailerChatHeaderName').textContent = partnerName;

            const imgElement = document.getElementById('retailerChatHeaderImg');
            imgElement.src = partnerImg || '../images/default-avatar.svg';
            imgElement.onerror = function() {
                this.src = '../images/default-avatar.svg';
            };

            retailerLoadMessages();
            clearInterval(retailerMessageCheckInterval);
            retailerMessageCheckInterval = setInterval(retailerLoadMessages, 3000);
        }

        async function retailerSendMessage() {
            const msgInput = document.getElementById('retailerMessageInput');
            const message = msgInput.value.trim();

            if (!message || !retailerCurrentChatPartnerId) return;

            try {
                const res = await fetch('../api/send-message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: retailerCurrentChatPartnerId,
                        message: message
                    })
                });

                const data = await res.json();

                if (data.success) {
                    msgInput.value = '';
                    retailerLoadMessages();
                } else {
                    alert('Failed to send message: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error sending message:', err);
                alert('Error sending message');
            }
        }

        async function retailerLoadMessages() {
            if (!retailerCurrentChatPartnerId) return;

            try {
                const res = await fetch(`../api/get-messages.php?partner_id=${retailerCurrentChatPartnerId}`);
                const data = await res.json();

                if (data.success && data.messages) {
                    const container = document.getElementById('retailerChatMessages');
                    const shouldScroll = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

                    container.innerHTML = data.messages.map(msg => {
                        const isSent = msg.sender_id === retailerCurrentUserId;
                        const time = retailerFormatTime(msg.created_at);
                        return `
                        <div class="flex ${isSent ? 'justify-end' : 'justify-start'}">
                            <div class="retailer-msg-bubble ${isSent ? 'retailer-msg-bubble-sent' : 'retailer-msg-bubble-received'}">
                                <div>${msg.message}</div>
                                <div class="text-xs mt-1 opacity-70">${time}</div>
                            </div>
                        </div>
                    `;
                    }).join('');

                    if (shouldScroll) {
                        container.scrollTop = container.scrollHeight;
                    }

                    // Update unread badge
                    retailerUpdateUnreadBadge();
                }
            } catch (err) {
                console.error('Error loading messages:', err);
            }
        }

        function retailerFormatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;

            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `${diffHours}h ago`;

            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 7) return `${diffDays}d ago`;

            return date.toLocaleDateString();
        }
    })();
</script>