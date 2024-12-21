<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$sender_id = $_SESSION['user_id'];
$receiver_id = sanitize($_POST['receiver_id']);
$message = sanitize($_POST['message']);
$type = sanitize($_POST['type']);
$code_language = isset($_POST['language']) ? sanitize($_POST['language']) : null;

$query = "
    INSERT INTO messages (sender_id, receiver_id, message_type, message_content, code_language)
    VALUES ('$sender_id', '$receiver_id', '$type', '$message', '$code_language')
";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>