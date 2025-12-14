
<?php
session_start();

// Check if retailer is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'retailer') {
    header('Location: ../public/index.php');
    exit;
}

require_once __DIR__ . '/../config/supabase-api.php';

$chats = [];
try {
    $api = getSupabaseAPI();
    $customers = $api->select('users', ['user_type' => 'customer']);
    
    foreach ($customers as $customer) {
        $profileImg = '../images/default-avatar.svg';
        if (!empty($customer['profile_picture'])) {
            $profilePath = '../' . ltrim($customer['profile_picture'], '/');
            if (file_exists($profilePath)) $profileImg = $profilePath;
        }

        $chats[] = [
            'id' => $customer['id'],
            'name' => htmlspecialchars($customer['full_name'] ?? $customer['username']),
            'last_message' => 'Say hi to start chat!',
            'time' => date('M j', strtotime($customer['created_at'])),
            'img' => $profileImg,
            'online' => true
        ];
    }
} catch (Exception $e) {
    $chats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Messages</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

<style>
.chat-fab {position: fixed; bottom: 30px; right: 30px; z-index:50; transition: all 0.3s; box-shadow:0 4px 14px rgba(0,0,0,0.25);}
.chat-fab:hover{transform: scale(1.1);}
.chat-widget{position:fixed;bottom:100px;right:30px;width:360px;height:520px;background:white;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.15);display:none;flex-direction:column;overflow:hidden;border:1px solid #e5e7eb;z-index:49;}
.chat-widget.open{display:flex;}
.chat-message-bubble{max-width:75%;padding:10px 14px;border-radius:18px;font-size:14px;word-break:break-word;}
.chat-bubble-received{background-color:#f0f2f5;border-bottom-left-radius:4px;}
.chat-bubble-sent{background-color:#2E7D32;color:white;margin-left:auto;border-bottom-right-radius:4px;}
.chat-list-item:hover{background-color:#f3f4f6;}
</style>
</head>
<body>

<!-- CHAT BUTTON -->
<button id="chatFab" class="chat-fab w-14 h-14 bg-green-600 rounded-full flex items-center justify-center text-white">
    <i class="fas fa-comment-dots text-2xl"></i>
</button>

<!-- CHAT WIDGET -->
<div id="chatWidget" class="chat-widget hidden">

    <!-- CHAT LIST -->
    <div id="chatList" class="flex flex-col h-full">
        <div class="p-4 bg-green-600 text-white font-bold flex justify-between items-center">
            <span>Customer Messages</span>
            <button id="closeChat"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="chatListContainer">
            <?php foreach ($chats as $chat): ?>
            <div class="chat-list-item p-3 rounded-lg cursor-pointer flex items-center gap-3"
                 data-partner-id="<?= htmlspecialchars($chat['id']) ?>"
                 data-partner-name="<?= htmlspecialchars($chat['name']) ?>"
                 data-partner-img="<?= htmlspecialchars($chat['img']) ?>">
                <div class="relative">
                    <img src="<?= $chat['img'] ?>" class="w-10 h-10 rounded-full object-cover border">
                    <?php if ($chat['online']): ?>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between mb-1">
                        <h4 class="font-bold text-sm truncate"><?= htmlspecialchars($chat['name']) ?></h4>
                        <span class="text-xs text-gray-400"><?= $chat['time'] ?></span>
                    </div>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($chat['last_message']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CHAT CONVERSATION -->
    <div id="chatConversation" class="hidden flex-col h-full">
        <div class="p-3 border-b flex items-center gap-3">
            <button onclick="backToChatList()"><i class="fas fa-arrow-left"></i></button>
            <img id="chatHeaderImg" class="w-8 h-8 rounded-full">
            <h4 id="chatHeaderName" class="font-bold text-sm"></h4>
        </div>
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-3 pb-72"></div>
        <div class="p-3 border-t flex gap-2">
            <input id="messageInput" type="text" class="flex-1 bg-gray-100 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Type a message...">
            <button id="sendMessageBtn" class="text-green-600 hover:text-green-700 transition-colors">
                <i class="fas fa-paper-plane text-xl"></i>
            </button>
        </div>
    </div>
</div>

<script>
let currentChatPartnerId = null;
let currentUserId = '<?php echo $_SESSION['user_id']; ?>';
let supabaseClient = null;

document.addEventListener('DOMContentLoaded', function(){
    const supabaseUrl = '<?php echo getenv('SUPABASE_URL'); ?>';
    const supabaseAnonKey = '<?php echo getenv('SUPABASE_ANON_KEY'); ?>';
    supabaseClient = supabase.createClient(supabaseUrl, supabaseAnonKey);

    const chatFab = document.getElementById('chatFab');
    const chatWidget = document.getElementById('chatWidget');
    const closeChat = document.getElementById('closeChat');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const messageInput = document.getElementById('messageInput');
    const chatListContainer = document.getElementById('chatListContainer');

    chatFab.addEventListener('click', () => {
        chatWidget.classList.toggle('open');
        chatWidget.classList.remove('hidden');
    });

    closeChat.addEventListener('click', () => {
        chatWidget.classList.remove('open');
    });

    sendMessageBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', e => { if(e.key==='Enter') sendMessage(); });

    chatListContainer.addEventListener('click', function(e){
        const item = e.target.closest('.chat-list-item');
        if(!item) return;
        const partnerId = item.dataset.partnerId;
        const partnerName = item.dataset.partnerName;
        const partnerImg = item.dataset.partnerImg;
        openConversation(partnerId, partnerName, partnerImg);
    });

    setInterval(() => { if(currentChatPartnerId) loadMessages(); }, 2000);
});

function showChatList(){
    document.getElementById('chatList').classList.remove('hidden');
    document.getElementById('chatConversation').classList.add('hidden');
}

function openConversation(partnerId, partnerName, partnerImg){
    currentChatPartnerId = partnerId;
    document.getElementById('chatList').classList.add('hidden');
    const conv = document.getElementById('chatConversation');
    conv.classList.remove('hidden');
    document.getElementById('chatHeaderName').innerText = partnerName;
    document.getElementById('chatHeaderImg').src = partnerImg;
    loadMessages();
}

async function sendMessage(){
    const message = document.getElementById('messageInput').value.trim();
    console.log('Send button clicked');
    console.log('Message:', message);
    console.log('Current chat partner ID:', currentChatPartnerId);

    if(!message || !currentChatPartnerId) {
        console.log('Cannot send: missing message or partner ID');
        return;
    }

    try{
        console.log('Sending message to API...');
        const res = await fetch('../api/send-message.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({
                receiver_id: currentChatPartnerId,
                message: message
            })
        });

        console.log('API response status:', res.status);
        const data = await res.json();
        console.log('Send message response:', data);

        if(data.success){
            console.log('Message sent successfully!');
            document.getElementById('messageInput').value = '';
            loadMessages();
        } else {
            console.error('Failed to send message:', data.error);
            alert('Failed to send message: ' + (data.error || 'Unknown error'));
        }

    } catch(err){
        console.error('Error sending message:', err);
        alert('Error sending message: ' + err.message);
    }
}

async function loadMessages(){
    if(!currentChatPartnerId) return;
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML='';

    try{
        console.log('Loading messages for partner:', currentChatPartnerId);
        const res = await fetch(`../api/get-messages.php?partner_id=${currentChatPartnerId}`);
        console.log('Load messages response status:', res.status);
        const messages = await res.json();
        console.log('Loaded messages:', messages);

        messages.forEach(msg=>{
            const div = document.createElement('div');
            const isSent = msg.sender_id == currentUserId;
            div.className = `chat-message-bubble ${isSent ? 'chat-bubble-sent' : 'chat-bubble-received'}`;
            const time = new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
            div.innerHTML = `<div>${msg.message}</div><div class="text-xs mt-1 opacity-70">${time}</div>`;
            chatMessages.appendChild(div);
        });

        chatMessages.scrollTop = chatMessages.scrollHeight;

    } catch(err){
        console.error('Error loading messages:', err);
    }
}

function backToChatList(){
    showChatList();
    currentChatPartnerId = null;
    document.getElementById('messageInput').value='';
}
</script>

</body>
</html>
