<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    exit(json_encode(['error' => 'Not authenticated']));
}

$sender_id = $_SESSION['user_id'];
$receiver_id = sanitize($_POST['receiver_id']);
$file = $_FILES['file'];

$allowed_types = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain'
];

if (!in_array($file['type'], $allowed_types)) {
    exit(json_encode(['success' => false, 'error' => 'Invalid file type']));
}

$filename = uniqid() . '_' . $file['name'];
$upload_path = '../uploads/' . $filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    $query = "
        INSERT INTO messages (sender_id, receiver_id, message_type, message_content, file_url)
        VALUES ('$sender_id', '$receiver_id', 'file', '" . $file['name'] . "', '$filename')
    ";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'File upload failed']);
}
?>