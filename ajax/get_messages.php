<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$other_user = sanitize($_GET['user_id']);
$last_id = (int)$_GET['last_id'];

$query = "
    SELECT * FROM messages 
    WHERE ((sender_id = '$user_id' AND receiver_id = '$other_user')
    OR (sender_id = '$other_user' AND receiver_id = '$user_id'))
    AND message_id > $last_id
    ORDER BY created_at ASC
";

$result = $conn->query($query);
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['messages' => $messages]);

// Update read status
$conn->query("
    UPDATE messages 
    SET is_read = TRUE 
    WHERE sender_id = '$other_user' 
    AND receiver_id = '$user_id'
    AND is_read = FALSE
");
?>