
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Messages – The Farmer’s Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Prevent horizontal scroll */
    body {
      overflow-x: hidden;
    }

    /* Custom scrollbar for chat messages */
    .scrollbar-thin {
      scrollbar-width: thin;
      scrollbar-color: #16a34a #e5e7eb;
    }

    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
      background-color: #16a34a;
      border-radius: 3px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
      background-color: #e5e7eb;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <header class="bg-white border-b shadow-sm sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">

      <!-- Left: Logo -->
      <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='retailerdashboard.php'">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold text-green-700">Farmers Mall</h1>
      </div>

      <!-- Center: Search -->
      <div class="flex-1 mx-8 max-w-xl">
        <form action="products.php" method="GET" class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" name="search" placeholder="Search products..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm" />
        </form>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-6">
        <!-- Messages -->
        <a href="retailermessage.php" class="relative cursor-pointer">
          <i class="fa-regular fa-comment text-xl text-gray-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">3</span>
        </a>

        <!-- Notifications -->
        <a href="retailernotifications.php" class="relative cursor-pointer">
          <i class="fa-regular fa-bell text-xl text-gray-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">5</span>
        </a>

        <!-- Profile -->
        <a href="retailerprofile.php" class="flex items-center space-x-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-8 h-8 rounded-full" alt="Seller Profile">
          <div class="profile-info">
            <p class="text-sm font-medium text-gray-800">Mesa Farm</p>
            <p class="text-xs text-gray-500">Seller</p>
          </div>
        </a>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="flex-grow flex flex-col">
    <div class="max-w-6xl mx-auto px-6 py-8 min-h-[92vh]">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 h-full">

        <!-- Conversations List -->
        <aside id="conversationPanel"
          class="md:col-span-1 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 flex flex-col h-[600px]">
          <div class="p-4 border-b">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input id="searchInput" type="text" placeholder="Search customer..."
                class="w-full pl-9 pr-3 py-1.5 border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
          </div>
          <ul id="conversationList" class="divide-y divide-gray-100 overflow-y-auto flex-grow scrollbar-thin"></ul>
        </aside>

        <!-- Chat Section -->
        <section id="chatPanel"
          class="md:col-span-3 bg-white rounded-lg shadow-sm flex flex-col border border-gray-200 h-[600px]">
          <!-- Chat Header -->
          <div id="chatHeader" class="p-4 border-b flex items-center space-x-3">
          </div>

          <!-- Chat Messages -->
          <div id="chatMessages"
            class="flex-1 p-4 overflow-y-auto overflow-x-hidden space-y-4 bg-gray-50 scrollbar-thin scrollbar-thumb-green-500 scrollbar-track-gray-200">
          </div>

          <!-- Chat Input -->
          <div class="p-4 border-t bg-white flex-shrink-0">
            <div class="relative flex items-center">
              <button id="attachFileBtn" class="p-2 text-gray-500 hover:text-gray-700" aria-label="Attach file">
                <i class="fa-solid fa-paperclip text-xl"></i>
              </button>
              <input type="file" id="fileUpload" class="hidden" />
              <input id="messageInput" type="text" placeholder="Type your message..."
                class="flex-1 mx-2 px-4 py-2 border rounded-lg text-sm focus:ring-1 focus:ring-green-500 focus:outline-none"
                aria-label="Message input">
              <button id="sendBtn"
                class="bg-green-600 text-white w-10 h-10 rounded-lg hover:bg-green-700 flex items-center justify-center"
                aria-label="Send message">
                <i class="fa-solid fa-paper-plane"></i>
              </button>
            </div>
          </div>
        </section>

      </div>
    </div>
  </main>

  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      
      <!-- Logo/About -->
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
        <p class="text-gray-300 text-sm">
          Fresh, organic produce delivered straight to your home from local farmers.
        </p>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="font-bold text-lg mb-3">Quick Links</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="#" class="hover:underline">Support</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div>
        <h3 class="font-bold text-lg mb-3">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">Vegetables</a></li>
          <li><a href="#" class="hover:underline">Fruits</a></li>
          <li><a href="#" class="hover:underline">Dairy</a></li>
          <li><a href="#" class="hover:underline">Meat</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div>
        <h3 class="font-bold text-lg mb-3">Follow Us</h3>
        <div class="flex space-x-4 text-xl">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <!-- Delete Message Confirmation Modal -->
  <div id="deleteMessageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Deletion</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to delete this message? This action cannot be undone.</p>
      <div class="flex justify-center gap-4">
        <button id="cancelDeleteMessage" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <button id="confirmDeleteMessage" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Delete</button>
      </div>
    </div>
  </div>


  <!-- JS -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const conversationListEl = document.getElementById('conversationList');
      const chatHeaderEl = document.getElementById('chatHeader');
      const chatMessagesEl = document.getElementById('chatMessages');
      const messageInput = document.getElementById('messageInput');
      const sendBtn = document.getElementById('sendBtn');
      const searchInput = document.getElementById('searchInput');
      const attachFileBtn = document.getElementById('attachFileBtn');
      const fileUpload = document.getElementById('fileUpload');

      // Delete Message Modal elements
      const deleteMessageModal = document.getElementById('deleteMessageModal');
      const cancelDeleteMessageBtn = document.getElementById('cancelDeleteMessage');
      const confirmDeleteMessageBtn = document.getElementById('confirmDeleteMessage');

      const MY_NAME = "Mesa Farm";

      let conversations = JSON.parse(localStorage.getItem('conversations')) || {
        "Piodos De Blanco": {
          avatar: "https://randomuser.me/api/portraits/men/1.jpg",
          messages: [
            { sender: "Mesa Farm", text: "Hello! We have new stock of red onions available now.", timestamp: new Date().toISOString() },
            { sender: "Piodos De Blanco", text: "Great! I'd like to place an order.", timestamp: new Date(Date.now() + 1000).toISOString() },
          ],
          unread: 0,
          online: true
        },
        "Jane Smith": {
          avatar: "https://randomuser.me/api/portraits/women/2.jpg",
          messages: [
            { sender: "Jane Smith", text: "Thank you for the fast delivery!", timestamp: new Date(Date.now() - 86400000).toISOString(), liked: true }
          ],
          unread: 0,
          online: false
        }
      };

      let activeUser = Object.keys(conversations)[0];

      function formatTimestampForList(isoString) {
        const date = new Date(isoString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;

        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const startOfYesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);

        if (date >= startOfToday) {
          return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else if (date >= startOfYesterday) {
          return 'Yesterday';
        }
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
      }

      function saveConversations() {
        localStorage.setItem('conversations', JSON.stringify(conversations));
      }

      function renderConversationList() {
        conversationListEl.innerHTML = '';
        const searchQuery = searchInput.value.toLowerCase();

        // Filter users based on the search query
        const filteredUsers = Object.keys(conversations).filter(user =>
          user.toLowerCase().startsWith(searchQuery)
        );

        // Sort the filtered users alphabetically
        filteredUsers.sort((a, b) => new Date(conversations[b].messages.slice(-1)[0].timestamp) - new Date(conversations[a].messages.slice(-1)[0].timestamp));

        for (const user of filteredUsers) {
          const conv = conversations[user];
          const lastMessage = conv.messages[conv.messages.length - 1];
          const li = document.createElement('li');
          li.className = `p-4 flex items-center space-x-3 hover:bg-gray-50 cursor-pointer ${user === activeUser ? 'bg-green-50 border-l-4 border-green-600' : ''}`;
          li.dataset.user = user;
          const timeString = formatTimestampForList(lastMessage.timestamp);
          const onlineIndicatorClass = conv.online ? 'bg-green-500' : 'bg-gray-400';
          li.innerHTML = `
            <div class="relative">
              <img src="${conv.avatar}" alt="Customer" class="w-10 h-10 rounded-full">
              <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ${onlineIndicatorClass} ring-2 ring-white"></span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center">
                <p class="font-medium text-gray-800">${user}</p>
                <p class="text-xs text-gray-400 flex-shrink-0 ml-2">${timeString}</p>
              </div>
              <p class="text-sm text-gray-500 truncate">${lastMessage.text}</p>
            </div>
            ${conv.unread > 0 ? `<span class="bg-green-600 text-white text-xs rounded-full px-2 py-1">${conv.unread}</span>` : ''}`;
          li.addEventListener('click', () => {
            activeUser = user;
            conversations[activeUser].unread = 0;
            renderAll();
          });
          conversationListEl.appendChild(li);
        }
      }

      function renderChat() {
        const conv = conversations[activeUser];
        chatHeaderEl.innerHTML = `
          <div class="relative">
            <img src="${conv.avatar}" alt="Customer" class="w-10 h-10 rounded-full">
            <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ${conv.online ? 'bg-green-500' : 'bg-gray-400'} ring-2 ring-white"></span>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800">${activeUser}</h3>
          </div>
        `;

        chatMessagesEl.innerHTML = '';
        conv.messages.forEach(msg => {
          const isMine = msg.sender === MY_NAME;
          const div = document.createElement('div');
          div.className = `flex items-start group ${isMine ? 'justify-end' : 'justify-start'}`;

          const timeString = new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

          // Define the reaction toolbar that appears on hover
          const reactionToolbar = `
            <div class="absolute top-1/2 -translate-y-1/2 flex items-center gap-1 bg-white border rounded-full shadow-sm p-1 opacity-0 group-hover:opacity-100 transition-opacity ${isMine ? 'left-0 -translate-x-full' : 'right-0 translate-x-full'}">
              <button data-timestamp="${msg.timestamp}" class="reaction-btn p-1.5 rounded-full hover:bg-gray-100 text-gray-500" aria-label="Like message">
                <i class="fa-solid fa-thumbs-up ${msg.liked ? 'text-blue-500' : ''}"></i>
              </button>
              <button data-timestamp="${msg.timestamp}" class="delete-message-btn p-1.5 rounded-full hover:bg-gray-100 text-gray-500" aria-label="Unsend message">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>`;
          
          // Define the like indicator (thumbs-up icon) that shows if a message is liked
          const likeIndicator = msg.liked ? `<i class="fa-solid fa-thumbs-up text-blue-500 text-xs absolute -bottom-2 right-1"></i>` : '';

          let messageContent = '';
          if (msg.imageUrl) {
            messageContent = `
              <div class="relative group" data-timestamp="${msg.timestamp}">
                <img src="${msg.imageUrl}" alt="Sent image" class="rounded-lg shadow-sm max-w-[70%] sm:max-w-xs max-h-60 object-cover cursor-pointer" onclick="window.open('${msg.imageUrl}', '_blank')">
                <span class="block text-xs text-gray-400 mt-1 ${isMine ? 'text-right pr-5' : 'text-left'}">${timeString}</span>
                ${reactionToolbar} ${likeIndicator}
              </div>`;
          } else {
            const bgColor = isMine ? 'bg-green-600 text-white' : 'bg-white text-gray-700';
            messageContent = `
              <div class="relative group" data-timestamp="${msg.timestamp}">
                <div class="${bgColor} p-3 rounded-lg shadow-sm max-w-[70%] sm:max-w-xs"><p class="text-sm break-words">${msg.text}</p></div>
                <span class="block text-xs text-gray-400 mt-1 ${isMine ? 'text-right pr-5' : 'text-left'}">${timeString}</span>
                ${reactionToolbar} ${likeIndicator}
              </div>`;
          }

          div.innerHTML = isMine ? messageContent : `<img src="${conv.avatar}" class="w-8 h-8 rounded-full self-end" alt="Customer"> <div class="ml-2">${messageContent}</div>`;
          chatMessagesEl.appendChild(div);
        });

        // We will handle scrolling separately to avoid jumps on reactions.
      }

      function sendMessage() {
        const text = messageInput.value.trim();
        if (!text) return;

        conversations[activeUser].messages.push({
          sender: MY_NAME,
          text: text,
          timestamp: new Date().toISOString()
        });
        messageInput.value = '';
        renderAll(true); // Pass true to scroll to bottom for new messages
        simulateReply();
      }

      function simulateReply() {
        setTimeout(() => {
          // Simulate receiving 3 messages
          const replies = [
            { sender: activeUser, text: "Okay, thank you for the information!", timestamp: new Date(Date.now() + 1).toISOString() },
            { sender: activeUser, text: "I will check it out now.", timestamp: new Date(Date.now() + 2).toISOString() },
            { sender: activeUser, text: "Can you confirm the delivery time as well?", timestamp: new Date(Date.now() + 3).toISOString() }
          ];

          // Add all new messages to the conversation
          conversations[activeUser].messages.push(...replies);

          // If tab is not active, increment unread count by the number of new messages
          if (document.hidden) {
            conversations[activeUser].unread = (conversations[activeUser].unread || 0) + replies.length;
          }

          renderAll(true); // Pass true to scroll to bottom for new messages
        }, 1500);
      }

      // --- Attach File Logic ---
      attachFileBtn.addEventListener('click', () => fileUpload.click());
      fileUpload.addEventListener('change', () => {
        const file = fileUpload.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            conversations[activeUser].messages.push({
              sender: MY_NAME,
              imageUrl: e.target.result,
              timestamp: new Date().toISOString()
            });
            renderAll(true);
          }
          reader.readAsDataURL(file);
        }
      });

      function renderAll(scrollToBottom = false) {
        saveConversations();
        renderConversationList();
        renderChat();

        // Only scroll to bottom if explicitly told to (e.g., for new messages)
        if (scrollToBottom) {
          chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
        }
      }

      sendBtn.addEventListener('click', sendMessage);
      messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          sendMessage();
        }
      });

      // --- Like & Delete Logic ---
      chatMessagesEl.addEventListener('click', (e) => {
        const targetButton = e.target.closest('button');
        if (targetButton) {
          const timestamp = targetButton.dataset.timestamp;
          const message = conversations[activeUser]?.messages.find(m => m.timestamp === timestamp);

          if (message) {
            if (targetButton.classList.contains('reaction-btn')) {
              message.liked = !message.liked; // Toggle the liked state
              renderAll(false); // Re-render without scrolling
            } else if (targetButton.classList.contains('delete-message-btn')) {
              // Store timestamp for confirmation
              deleteMessageModal.dataset.messageTimestamp = timestamp;
              deleteMessageModal.classList.remove('hidden');
            }
          }
        }
      });

      // --- Delete Message Modal Logic ---
      cancelDeleteMessageBtn.addEventListener('click', () => {
        deleteMessageModal.classList.add('hidden');
      });

      confirmDeleteMessageBtn.addEventListener('click', () => {
        const messageTimestampToDelete = deleteMessageModal.dataset.messageTimestamp;
        if (messageTimestampToDelete && activeUser) {
          const conv = conversations[activeUser];
          conv.messages = conv.messages.filter(m => m.timestamp !== messageTimestampToDelete);
          // If the last message was deleted, handle conversation deletion
          if (conv.messages.length === 0) {
            delete conversations[activeUser];
            activeUser = Object.keys(conversations)[0] || null; // Select first available or null
          }
          renderAll(false); // Re-render without scrolling
        }
        deleteMessageModal.classList.add('hidden');
      });

      // Close modal if user clicks on the background overlay
      deleteMessageModal.addEventListener('click', (e) => {
        if (e.target === deleteMessageModal) {
          deleteMessageModal.classList.add('hidden');
        }
      });

      // --- Search Logic ---
      searchInput.addEventListener('input', renderConversationList);

      // When tab becomes visible, clear unread for the active chat
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          if (conversations[activeUser]) {
            conversations[activeUser].unread = 0;
            renderAll();
          }
        }
      });

      // Initial Render
      renderAll();

      // Also update notification badge on load (from previous context)
      const notifCount = localStorage.getItem('unreadNotifications');
      const notifBadge = document.querySelector('a[href="retailernotifications.php"] .absolute');
      if (notifBadge) {
        if (notifCount && parseInt(notifCount) > 0) {
          notifBadge.textContent = notifCount;
          notifBadge.style.display = 'inline-flex';
        } else {
          notifBadge.style.display = 'none';
        }
      }
    });
  </script>

</body>

</html> 