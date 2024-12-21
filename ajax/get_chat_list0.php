
<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$query = "
    SELECT DISTINCT 
        u.user_id,
        u.name,
        u.profile_photo,
        u.last_seen,
        (
            SELECT message_content 
            FROM messages m 
            WHERE (m.sender_id = u.user_id AND m.receiver_id = '$user_id')
            OR (m.sender_id = '$user_id' AND m.receiver_id = u.user_id)
            ORDER BY m.created_at DESC 
            LIMIT 1
        ) as last_message,
        (
            SELECT created_at 
            FROM messages m 
            WHERE (m.sender_id = u.user_id AND m.receiver_id = '$user_id')
            OR (m.sender_id = '$user_id' AND m.receiver_id = u.user_id)
            ORDER BY m.created_at DESC 
            LIMIT 1
        ) as last_message_time
    FROM users u
    INNER JOIN connections c ON c.connected_user_id = u.user_id
    WHERE c.user_id = '$user_id' AND c.status = 'accepted'
    ORDER BY last_message_time DESC NULLS LAST
";

$result = $conn->query($query);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = [
        'user_id' => $row['user_id'],
        'name' => $row['name'],
        'profile_photo' => $row['profile_photo'] ?? 'default.jpg',
        'last_message' => $row['last_message'] ?? '',
        'last_message_time' => $row['last_message_time'] ? date('h:i A', strtotime($row['last_message_time'])) : '',
        'last_seen' => $row['last_seen']
    ];
}

echo json_encode($users);
?>