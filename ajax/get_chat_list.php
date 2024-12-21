<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];

// Simplified query to get all connected users
$query = "
    SELECT 
        u.user_id,
        u.name,
        u.profile_photo,
        COALESCE(u.last_seen, 'offline') as last_seen,
        (
            SELECT m.message_content
            FROM messages m
            WHERE (m.sender_id = u.user_id AND m.receiver_id = '$user_id')
                OR (m.sender_id = '$user_id' AND m.receiver_id = u.user_id)
            ORDER BY m.created_at DESC
            LIMIT 1
        ) as last_message
    FROM users u
    INNER JOIN connections c ON u.user_id = c.connected_user_id
    WHERE c.user_id = '$user_id'
    ORDER BY last_seen DESC
";

$result = $conn->query($query);

if (!$result) {
    error_log("MySQL Error: " . $conn->error);
    exit(json_encode(['error' => 'Database error']));
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'user_id' => $row['user_id'],
        'name' => $row['name'],
        'profile_photo' => $row['profile_photo'] ?? 'default.jpg',
        'last_message' => $row['last_message'] ?? 'No messages yet',
        'last_seen' => $row['last_seen']
    ];
}

echo json_encode($users);
?>