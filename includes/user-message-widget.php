<!-- USER/CUSTOMER MESSAGING WIDGET - Include this in all user pages -->
<style>
    .user-msg-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        /* Lower right corner */
        z-index: 9999;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    }

    .user-msg-fab:hover {
        transform: scale(1.1);
    }

    .user-msg-widget {
        position: fixed;
        bottom: 100px;
        right: 30px;
        /* Lower right to match button */
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

    .user-msg-widget.open {
        display: flex;
    }

    .user-msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 18px;
        font-size: 14px;
        word-break: break-word;
        margin-bottom: 8px;
    }

    .user-msg-bubble-received {
        background-color: #f0f2f5;
        border-bottom-left-radius: 4px;
    }

    .user-msg-bubble-sent {
        background-color: #2E7D32;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .user-chat-list-item:hover {
        background-color: #f3f4f6;
    }

    .user-msg-badge {
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

    .user-unread-indicator {
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
<button id="userMsgFab" class="user-msg-fab w-16 h-16 bg-green-600 rounded-full flex items-center justify-center text-white hover:bg-green-700 transition-colors">
    <i class="fas fa-comment-dots text-2xl"></i>
    <span id="userMsgBadge" class="user-msg-badge hidden">0</span>
</button>

<!-- CHAT WIDGET -->
<div id="userMsgWidget" class="user-msg-widget">

    <!-- CHAT LIST VIEW -->
    <div id="userChatList" class="flex flex-col h-full">
        <div class="p-4 bg-green-600 text-white font-bold flex justify-between items-center">
            <div>
                <h3 class="text-lg">Messages</h3>
                <p class="text-xs opacity-90 font-normal">Chat with retailers</p>
            </div>
            <button id="userCloseChat" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-3 border-b">
            <input type="text" id="userSearchChat" placeholder="Search conversations..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="userChatListContainer">
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Loading conversations...</p>
            </div>
        </div>
    </div>

    <!-- CHAT CONVERSATION VIEW -->
    <div id="userChatConversation" class="hidden flex-col h-full">
        <div class="p-4 border-b flex items-center gap-3 bg-white">
            <button id="userBackToList" class="text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
            <img id="userChatHeaderImg" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            <div class="flex-1">
                <h4 id="userChatHeaderName" class="font-bold text-sm"></h4>
                <p class="text-xs text-gray-500" id="userChatHeaderStatus">Online</p>
            </div>
        </div>

        <div id="userChatMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-2"></div>

        <div class="p-4 border-t bg-white flex gap-2">
            <input id="userMessageInput" type="text"
                class="flex-1 bg-gray-100 rounded-full px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="Type a message...">
            <button id="userSendMessageBtn"
                class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center hover:bg-green-700 transition-colors">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
    // User/Customer Messaging Widget JavaScript
    (function() {
        let userCurrentChatPartnerId = null;
        let userCurrentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        let userMessageCheckInterval = null;
        let userChatListInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            const msgFab = document.getElementById('userMsgFab');
            const msgWidget = document.getElementById('userMsgWidget');
            const closeChat = document.getElementById('userCloseChat');
            const sendBtn = document.getElementById('userSendMessageBtn');
            const msgInput = document.getElementById('userMessageInput');
            const backToList = document.getElementById('userBackToList');
            const searchInput = document.getElementById('userSearchChat');

            // Toggle widget
            msgFab.addEventListener('click', () => {
                const isOpen = msgWidget.classList.contains('open');
                if (!isOpen) {
                    msgWidget.classList.add('open');
                    userLoadChatList();
                    // Start polling for new messages
                    userChatListInterval = setInterval(userLoadChatList, 5000);
                } else {
                    msgWidget.classList.remove('open');
                    clearInterval(userChatListInterval);
                    clearInterval(userMessageCheckInterval);
                }
            });

            closeChat.addEventListener('click', () => {
                msgWidget.classList.remove('open');
                clearInterval(userChatListInterval);
                clearInterval(userMessageCheckInterval);
            });

            sendBtn.addEventListener('click', userSendMessage);
            msgInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    userSendMessage();
                }
            });

            backToList.addEventListener('click', userShowChatList);

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const chatItems = document.querySelectorAll('.user-chat-list-item');
                chatItems.forEach(item => {
                    const name = item.dataset.partnerName.toLowerCase();
                    const message = item.querySelector('.user-last-msg')?.textContent.toLowerCase() || '';
                    if (name.includes(searchTerm) || message.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Load unread count on page load
            userUpdateUnreadBadge();
            setInterval(userUpdateUnreadBadge, 10000); // Check every 10 seconds
        });

        async function userLoadChatList() {
            const container = document.getElementById('userChatListContainer');
            try {
                const res = await fetch('../api/get-chat-list.php');

                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const data = await res.json();
                console.log('Chat list response:', data);

                if (data.success && data.chats) {
                    const container = document.getElementById('userChatListContainer');

                    if (data.chats.length === 0) {
                        container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No conversations yet</p>
                            <p class="text-xs mt-2">Start chatting with retailers!</p>
                        </div>
                    `;
                        return;
                    }

                    container.innerHTML = data.chats.map(chat => {
                        const imgSrc = chat.profile_img || '../images/default-avatar.svg';
                        return `
                    <div class="user-chat-list-item p-3 rounded-lg cursor-pointer flex items-center gap-3 transition-colors"
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
                                <span class="text-xs text-gray-400">${userFormatTime(chat.last_message_time)}</span>
                            </div>
                            <p class="text-xs text-gray-600 truncate user-last-msg">
                                ${chat.last_message_sender ? chat.last_message_sender + ': ' : ''}${chat.last_message}
                            </p>
                        </div>
                        ${chat.unread_count > 0 ? `<span class="user-unread-indicator">${chat.unread_count}</span>` : ''}
                    </div>
                `;
                    }).join('');

                    // Add click handlers
                    container.querySelectorAll('.user-chat-list-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const partnerId = item.dataset.partnerId;
                            const partnerName = item.dataset.partnerName;
                            const partnerImg = item.dataset.partnerImg;
                            userOpenConversation(partnerId, partnerName, partnerImg);
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
                    <button onclick="userLoadChatList()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                        Retry
                    </button>
                </div>
            `;
            }
        }

        async function userUpdateUnreadBadge() {
            try {
                const res = await fetch('../api/get-chat-list.php');
                const data = await res.json();

                if (data.success && data.chats) {
                    const totalUnread = data.chats.reduce((sum, chat) => sum + chat.unread_count, 0);
                    const badge = document.getElementById('userMsgBadge');

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

        function userShowChatList() {
            document.getElementById('userChatList').classList.remove('hidden');
            document.getElementById('userChatConversation').classList.add('hidden');
            userCurrentChatPartnerId = null;
            document.getElementById('userMessageInput').value = '';
            clearInterval(userMessageCheckInterval);
            userLoadChatList();
        }

        function userOpenConversation(partnerId, partnerName, partnerImg) {
            userCurrentChatPartnerId = partnerId;
            document.getElementById('userChatList').classList.add('hidden');
            document.getElementById('userChatConversation').classList.remove('hidden');
            document.getElementById('userChatHeaderName').textContent = partnerName;

            const imgElement = document.getElementById('userChatHeaderImg');
            imgElement.src = partnerImg || '../images/default-avatar.svg';
            imgElement.onerror = function() {
                this.src = '../images/default-avatar.svg';
            };

            userLoadMessages();
            clearInterval(userMessageCheckInterval);
            userMessageCheckInterval = setInterval(userLoadMessages, 3000);
        }

        async function userSendMessage() {
            const msgInput = document.getElementById('userMessageInput');
            const message = msgInput.value.trim();

            if (!message || !userCurrentChatPartnerId) return;

            try {
                const res = await fetch('../api/send-message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: userCurrentChatPartnerId,
                        message: message
                    })
                });

                const data = await res.json();

                if (data.success) {
                    msgInput.value = '';
                    userLoadMessages();
                } else {
                    alert('Failed to send message: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error sending message:', err);
                alert('Error sending message');
            }
        }

        async function userLoadMessages() {
            if (!userCurrentChatPartnerId) return;

            try {
                const res = await fetch(`../api/get-messages.php?partner_id=${userCurrentChatPartnerId}`);
                const data = await res.json();

                if (data.success && data.messages) {
                    const container = document.getElementById('userChatMessages');
                    const shouldScroll = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

                    container.innerHTML = data.messages.map(msg => {
                        const isSent = msg.sender_id === userCurrentUserId;
                        const time = userFormatTime(msg.created_at);
                        return `
                        <div class="flex ${isSent ? 'justify-end' : 'justify-start'}">
                            <div class="user-msg-bubble ${isSent ? 'user-msg-bubble-sent' : 'user-msg-bubble-received'}">
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
                    userUpdateUnreadBadge();
                }
            } catch (err) {
                console.error('Error loading messages:', err);
            }
        }

        function userFormatTime(dateString) {
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