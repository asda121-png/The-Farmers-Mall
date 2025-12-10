
<?php
session_start();

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get user data from database
$api = getSupabaseAPI();
$userId = $_SESSION['user_id'];
$userData = null;
$profilePicture = '../images/default-avatar.svg'; // Default avatar
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';

try {
    // Fetch user data from database
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        // Check if user has a profile picture
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        // Try to get retailer shop name
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Messages – The Farmer's Mall</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com">    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Load Inter font -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        /* Ensure main content area has minimum height */
        #content {
            min-height: calc(100vh - 200px);
        }
        /* Footer stays at bottom, visible only when scrolling */
        footer {
            margin-top: auto;
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
    /* Force sidebar to be full height with logout at bottom */
    #sidebar {
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #sidebar > *:last-child {
        margin-top: auto !important;
        padding-top: 1rem !important;
        border-top: 1px solid #e5e7eb !important;
    }
    /* Mobile menu toggle */
    #mobileMenuBtn {
        display: none;
    }
    @media (max-width: 768px) {
        #mobileMenuBtn {
            display: flex;
        }
        #sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            height: 100vh;
            z-index: 50;
            transition: left 0.3s ease;
        }
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > div:last-child {
            margin-top: auto !important;
        }
        #sidebar.active {
            left: 0;
        }
        #overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        #overlay.active {
            display: block;
        }
    }
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 bg-green-600 text-white p-3 rounded-lg shadow-lg md:hidden" onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <!-- Main Application Container -->
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <!-- Vouchers & Coupons -->
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-green-600"><i class="fa-solid fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 relative">
                        <i class="fa-regular fa-bell"></i>
                        <!-- Notification badge can be added here if needed -->
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                                <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                        <!-- End Profile Dropdown -->
                    </div>
                </div>
            </header>
        
            <!-- Main Content Area -->
            <main id="content" class="p-8 transition-all duration-300 flex-1">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Messages</h1>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 h-full">

        <!-- Conversations List -->
        <aside id="conversationPanel"
          class="md:col-span-1 bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 overflow-hidden border border-gray-200 flex flex-col h-[600px]">
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
          class="md:col-span-3 bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 flex flex-col border border-gray-200 h-[600px]">
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
        </div>
    </div>
    
    </div>

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
    });

    // Profile dropdown toggle
    document.getElementById('profileDropdownBtn')?.addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('profileDropdown').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('profileDropdown');
      const btn = document.getElementById('profileDropdownBtn');
      if (dropdown && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.add('hidden');
      }
    });

    // Real-time profile picture updates
    let lastProfilePicture = document.getElementById('headerProfilePic')?.src || '';
        
    async function checkProfileUpdates() {
        try {
            const response = await fetch('../api/get-profile.php');
            if (response.ok) {
                const result = await response.json();
                const profilePicElement = document.getElementById('headerProfilePic');
                
                if (result.success && result.data && profilePicElement) {
                    const newProfilePic = result.data.profile_picture;
                    
                    // Only update if the picture has actually changed
                    if (newProfilePic && newProfilePic !== lastProfilePicture) {
                        profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                        lastProfilePicture = result.data.profile_picture;
                        console.log('Profile picture updated in real-time');
                    }
                    
                    // Update title attribute with user's name
                    if (result.data.full_name) {
                        profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                    }
                }
            }
        } catch (error) {
            console.error('Error checking profile updates:', error);
        }
    }
    
    // Check for updates every 5 seconds
    setInterval(checkProfileUpdates, 5000);
    
    // Also check when user returns to the page
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkProfileUpdates();
        }
    });
    
    // Check when page loads
    window.addEventListener('load', () => {
        setTimeout(checkProfileUpdates, 1000);
    });
      
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
</body>
</html> 
