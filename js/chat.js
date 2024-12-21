// File: js/chat.js

let currentChatUser = null;
let lastMessageId = 0;

// Initialize chat
function loadChatList() {
    fetch('ajax/get_chat_list.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            const chatList = document.getElementById('chatList');
            if (!chatList) {
                console.error('Chat list container not found');
                return;
            }

            if (data.length === 0) {
                chatList.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        No contacts yet. Add users to start chatting!
                    </div>`;
                return;
            }

            chatList.innerHTML = data.map(user => `
                <div class="p-4 border-b hover:bg-gray-50 cursor-pointer" 
                     onclick="openChat('${user.user_id}')">
                    <div class="flex items-center">
                        <img src="uploads/${user.profile_photo}" 
                             alt="${user.name}" 
                             onerror="this.src='uploads/default.jpg'"
                             class="w-12 h-12 rounded-full">
                        <div class="ml-4 flex-1">
                            <div class="flex justify-between">
                                <h3 class="font-medium">${user.name}</h3>
                                <span class="text-sm text-gray-500">
                                    ${user.last_seen === 'online' ? 'online' : ''}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                ${user.last_message || 'No messages yet'}
                            </p>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading chat list:', error);
            document.getElementById('chatList').innerHTML = `
                <div class="p-4 text-center text-red-500">
                    Error loading contacts. Please refresh the page.
                </div>`;
        });
}

// Add this to ensure chat list loads immediately and refreshes periodically
document.addEventListener('DOMContentLoaded', () => {
    console.log('Loading chat list...');
    loadChatList();
    // Refresh chat list every 5 seconds
    setInterval(loadChatList, 1000);
});

// Open chat with user
function openChat(userId) {
    currentChatUser = userId;
    lastMessageId = 0;
    
    // Update chat header
    fetch(`ajax/get_user_info.php?user_id=${userId}`)
        .then(response => response.json())
        .then(user => {
            document.getElementById('currentChatUserPhoto').src = `uploads/${user.profile_photo}`;
            document.getElementById('currentChatUserName').textContent = user.name;
            document.getElementById('currentChatUserStatus').textContent = 
                user.last_seen === 'online' ? 'online' : `last seen ${user.last_seen}`;
        });
    
    loadMessages();
}

// File: js/chat.js (continued)

// Load messages
function loadMessages() {
    if (!currentChatUser) return;
    
    fetch(`ajax/get_messages.php?user_id=${currentChatUser}&last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages.length > 0) {
                lastMessageId = data.messages[data.messages.length - 1].message_id;
                const messagesArea = document.getElementById('messagesArea');
                
                data.messages.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `mb-4 ${message.sender_id === currentChatUser ? 'mr-auto' : 'ml-auto'}`;
                    
                    switch(message.message_type) {
                        case 'text':
                            messageDiv.innerHTML = createTextMessage(message);
                            break;
                        case 'code':
                            messageDiv.innerHTML = createCodeMessage(message);
                            break;
                        case 'file':
                            messageDiv.innerHTML = createFileMessage(message);
                            break;
                    }
                    
                    messagesArea.appendChild(messageDiv);
                });
                
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        });
}

// Create text message HTML
function createTextMessage(message) {
    return `
        <div class="max-w-[70%] ${message.sender_id === currentChatUser ? 'bg-white' : 'bg-blue-500 text-white'} 
                    rounded-lg p-3 shadow">
            <p>${message.message_content}</p>
            <p class="text-xs ${message.sender_id === currentChatUser ? 'text-gray-500' : 'text-blue-100'} 
                      text-right mt-1">
                ${message.created_at}
                ${message.sender_id === currentChatUser ? '' : 
                  `<i class="fas fa-check ${message.is_read ? 'text-blue-300' : ''} ml-1"></i>`}
            </p>
        </div>
    `;
}

// Create code message HTML
function createCodeMessage(message) {
    return `
        <div class="max-w-[70%] bg-gray-900 rounded-lg p-3 shadow">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-300">${message.code_language}</span>
                <button onclick="copyCode(this)" class="text-gray-300 hover:text-white">
                    <i class="far fa-copy"></i>
                </button>
            </div>
            <pre><code class="language-${message.code_language}">${message.message_content}</code></pre>
            <p class="text-xs text-gray-500 text-right mt-1">${message.created_at}</p>
        </div>
    `;
}

// Create file message HTML
function createFileMessage(message) {
    const fileExtension = message.file_url.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension);
    
    return `
        <div class="max-w-[70%] bg-white rounded-lg p-3 shadow">
            ${isImage ? 
                `<img src="uploads/${message.file_url}" class="max-w-full rounded" />` :
                `<div class="flex items-center">
                    <i class="fas fa-file-alt text-gray-500 text-2xl"></i>
                    <a href="uploads/${message.file_url}" 
                       class="ml-2 text-blue-500 hover:underline" 
                       download>${message.message_content}</a>
                </div>`
            }
            <p class="text-xs text-gray-500 text-right mt-1">${message.created_at}</p>
        </div>
    `;
}

// Send message
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || !currentChatUser) return;
    
    const formData = new FormData();
    formData.append('receiver_id', currentChatUser);
    formData.append('message', message);
    formData.append('type', 'text');
    
    fetch('ajax/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages();
        }
    });
}

// Send code
function sendCode() {
    const code = document.getElementById('codeInput').value.trim();
    const language = document.getElementById('codeLanguage').value;
    
    if (!code || !currentChatUser) return;
    
    const formData = new FormData();
    formData.append('receiver_id', currentChatUser);
    formData.append('message', code);
    formData.append('type', 'code');
    formData.append('language', language);
    
    fetch('ajax/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCodeModal();
            loadMessages();
        }
    });
}

// File upload handling
function attachFile(type) {
    const input = document.createElement('input');
    input.type = 'file';
    
    if (type === 'image') {
        input.accept = 'image/*';
    } else if (type === 'document') {
        input.accept = '.pdf,.doc,.docx,.txt';
    }
    
    input.onchange = e => {
        const file = e.target.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('receiver_id', currentChatUser);
        formData.append('file', file);
        formData.append('type', 'file');
        
        fetch('ajax/upload_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideAttachmentOptions();
                loadMessages();
            }
        });
    };
    
    input.click();
}

// Modal handling functions
function showAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function hideAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('newUserEmail').value = '';
}

function showCodeModal() {
    document.getElementById('codeModal').classList.remove('hidden');
}

function hideCodeModal() {
    document.getElementById('codeModal').classList.add('hidden');
    document.getElementById('codeInput').value = '';
}

function toggleAttachmentOptions() {
    const modal = document.getElementById('attachmentModal');
    modal.classList.toggle('hidden');
}

// Add new user
function addNewUser() {
    const email = document.getElementById('newUserEmail').value.trim();
    
    if (!email) return;
    
    const formData = new FormData();
    formData.append('email', email);
    
    fetch('ajax/add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddUserModal();
            loadChatList();
        } else {
            alert(data.message);
        }
    });
}

// Update chat periodically
function updateChat() {
    if (currentChatUser) {
        loadMessages();
    }
    loadChatList();
}

// Copy code to clipboard
function copyCode(button) {
    const codeBlock = button.parentElement.nextElementSibling;
    const code = codeBlock.textContent;
    
    navigator.clipboard.writeText(code).then(() => {
        button.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            button.innerHTML = '<i class="far fa-copy"></i>';
        }, 2000);
    });
}

// Add this to js/chat.js

function addNewUser() {
    const email = document.getElementById('newUserEmail').value.trim();
    
    if (!email) return;
    
    const formData = new FormData();
    formData.append('email', email);
    
    fetch('ajax/add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message); // Show success/error message
        if (data.success) {
            hideAddUserModal();
            loadChatList();
        }
    })
    .catch(error => {
        alert('Failed to add user. Please try again.');
    });
}

// Make sure loadChatList is called when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadChatList();
    setInterval(loadChatList, 5000); // Refresh every 5 seconds
});