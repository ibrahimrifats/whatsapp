
<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$email = sanitize($_POST['email']);

// Check if trying to add self
$query = "SELECT email FROM users WHERE user_id = '$user_id'";
$result = $conn->query($query);
$current_user = $result->fetch_assoc();
if ($current_user['email'] === $email) {
    echo json_encode(['success' => false, 'message' => 'Cannot add yourself']);
    exit;
}

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

// Create bi-directional connection (both users can see each other)
$query1 = "INSERT INTO connections (user_id, connected_user_id, status) VALUES ('$user_id', '$other_user', 'accepted')";
$query2 = "INSERT INTO connections (user_id, connected_user_id, status) VALUES ('$other_user', '$user_id', 'accepted')";

if ($conn->query($query1) && $conn->query($query2)) {
    echo json_encode(['success' => true, 'message' => 'User added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add user: ' . $conn->error]);
}
?>