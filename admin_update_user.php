<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$user_type = trim($_POST['user_type'] ?? '');
$status = trim($_POST['status'] ?? '');

// Validation
if (!$id || !$fullname || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Check if user exists
$checkUser = $conn->query("SELECT id FROM users WHERE id = $id");
if (!$checkUser || $checkUser->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Check if email is unique (excluding current user)
$emailCheck = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $id");
if ($emailCheck && $emailCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already in use']);
    exit();
}

// Update user
$stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, user_type = ?, status = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssssi", $fullname, $email, $user_type, $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}
$stmt->close();
?>
