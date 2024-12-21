<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$email = sanitize($_POST['email']);

// Check if user exists
$query = "SELECT user_id FROM users WHERE email = '$email'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$other_user = $result->fetch_assoc()['user_id'];

// Check if connection already exists
$query = "
    SELECT * FROM connections 
    WHERE (user_id = '$user_id' AND connected_user_id = '$other_user')
    OR (user_id = '$other_user' AND connected_user_id = '$user_id')
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Connection already exists']);
    exit;
}

// Create connection
$query = "
    INSERT INTO connections (user_id, connected_user_id, status)
    VALUES ('$user_id', '$other_user', 'accepted')
";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>