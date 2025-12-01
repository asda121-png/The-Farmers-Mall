document.addEventListener('DOMContentLoaded', () => {
  // Switch conversation dynamically
  const conversationList = document.getElementById('conversationList');
  const chatName = document.getElementById('chatName');
  const chatHeaderImg = document.querySelector('#chatHeader img');
  const chatMessages = document.getElementById('chatMessages');
  const messageInput = document.getElementById('messageInput');
  const sendBtn = document.getElementById('sendBtn');

  conversationList.addEventListener('click', (e) => {
    const li = e.target.closest('li');
    if (!li) return;
    const name = li.dataset.user;
    const img = li.querySelector('img').src;

    chatName.textContent = name;
    chatHeaderImg.src = img;

    // Replace chat with sample messages
    chatMessages.innerHTML = `
      <div class="flex items-end space-x-2">
        <img src="${img}" class="w-8 h-8 rounded-full" alt="${name}">
        <div class="bg-white p-3 rounded-lg shadow-sm max-w-xs">
          <p class="text-sm text-gray-700">Hi! How can I help you today?</p>
        </div>
      </div>
    `;
  });

  // Send message
  sendBtn.addEventListener('click', () => {
    const text = messageInput.value.trim();
    if (!text) return;
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flex justify-end';
    msgDiv.innerHTML = `
      <div class="bg-green-600 text-white p-3 rounded-lg shadow-sm max-w-xs">
        <p class="text-sm">${text}</p>
      </div>`;
    chatMessages.appendChild(msgDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    messageInput.value = '';
  });

  // Send on Enter
  messageInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') sendBtn.click();
  });
});