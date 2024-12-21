<!-- File: index.php -->

<?php

require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $conn->query($query);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.24.1/prism.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.24.1/themes/prism-dark.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto h-screen flex">
        <!-- Main chat container -->
        <div class="flex w-full bg-white rounded-lg shadow-lg h-[95vh] my-4">
            <!-- Left sidebar -->
            <div class="w-1/4 border-r border-gray-200">
                <!-- User profile header -->
                <div class="p-4 border-b flex justify-between items-center">
    <div class="flex items-center">
        <img src="uploads/<?php echo $user['profile_photo']; ?>" 
             alt="Profile" 
             class="w-10 h-10 rounded-full">
        <span class="ml-3 font-medium"><?php echo $user['name']; ?></span>
    </div>
    <div class="flex gap-4">
        <button onclick="showAddUserModal()" 
                class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-user-plus"></i>
        </button>
        <button onclick="showUserProfile()" 
                class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <a href="logout.php" 
           class="text-red-500 hover:text-red-700">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

                <!-- Search bar -->
                <div class="p-4 border-b">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search or start new chat" 
                               class="w-full py-2 px-4 bg-gray-100 rounded-full text-sm focus:outline-none"
                               onkeyup="searchUsers(this.value)">
                        <i class="fas fa-search absolute right-4 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Chat list -->
                <div id="chatList" class="overflow-y-auto h-[calc(100%-130px)]">
                    <!-- Chat list items will be loaded here via AJAX -->
                </div>
            </div>

            <!-- Right chat area -->
            <div class="flex-1 flex flex-col">
                <!-- Chat header -->
                <div id="chatHeader" class="p-4 border-b flex justify-between items-center">
                    <div class="flex items-center">
                        <img src="uploads/default.png" 
                             alt="Contact" 
                             class="w-10 h-10 rounded-full" 
                             id="currentChatUserPhoto">
                        <div class="ml-3">
                            <h3 class="font-medium" id="currentChatUserName">Select a chat</h3>
                            <p class="text-sm text-gray-500" id="currentChatUserStatus"></p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <button class="text-gray-600 hover:text-gray-800" onclick="initiateCall('audio')">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="text-gray-600 hover:text-gray-800" onclick="initiateCall('video')">
                            <i class="fas fa-video"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages area -->
                <div id="messagesArea" class="flex-1 overflow-y-auto p-4 bg-gray-50">
                    <!-- Messages will be loaded here via AJAX -->
                </div>

                <!-- Message input area -->
                <div class="p-4 border-t">
                    <div class="flex items-center gap-4">
                        <button class="text-gray-600 hover:text-gray-800" onclick="toggleEmojiPicker()">
                            <i class="far fa-smile"></i>
                        </button>
                        <button class="text-gray-600 hover:text-gray-800" onclick="toggleAttachmentOptions()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <input type="text" 
                               id="messageInput" 
                               placeholder="Type a message" 
                               class="flex-1 py-2 px-4 bg-gray-100 rounded-full focus:outline-none">
                        <button class="text-gray-600 hover:text-gray-800" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add user modal -->
    <div id="addUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-medium mb-4">Add New Contact</h3>
            <input type="email" 
                   id="newUserEmail" 
                   placeholder="Enter email address" 
                   class="w-full p-2 border rounded mb-4">
            <div class="flex justify-end gap-4">
                <button onclick="hideAddUserModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Cancel
                </button>
                <button onclick="addNewUser()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Add
                </button>
            </div>
        </div>
    </div>

    <!-- Attachment options modal -->
    <div id="attachmentModal" class="hidden fixed bottom-20 left-1/4 bg-white rounded-lg shadow-lg p-4">
        <div class="grid grid-cols-3 gap-4">
            <button onclick="attachFile('image')" class="p-2 hover:bg-gray-100 rounded">
                <i class="fas fa-image text-blue-500"></i>
                <span class="block text-sm">Image</span>
            </button>
            <button onclick="attachFile('document')" class="p-2 hover:bg-gray-100 rounded">
                <i class="fas fa-file text-red-500"></i>
                <span class="block text-sm">Document</span>
            </button>
            <button onclick="attachFile('code')" class="p-2 hover:bg-gray-100 rounded">
                <i class="fas fa-code text-green-500"></i>
                <span class="block text-sm">Code</span>
            </button>
        </div>
    </div>

    <!-- Code input modal -->
    <div id="codeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-3/4">
            <h3 class="text-lg font-medium mb-4">Share Code</h3>
            <select id="codeLanguage" class="w-full p-2 border rounded mb-4">
                <option value="javascript">JavaScript</option>
                <option value="python">Python</option>
                <option value="php">PHP</option>
                <option value="java">Java</option>
                <option value="cpp">C++</option>
            </select>
            <textarea id="codeInput" 
                      rows="10" 
                      class="w-full p-2 border rounded mb-4 font-mono"
                      placeholder="Paste your code here..."></textarea>
            <div class="flex justify-end gap-4">
                <button onclick="hideCodeModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Cancel
                </button>
                <button onclick="sendCode()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Send
                </button>
            </div>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>