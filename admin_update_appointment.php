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
$status = trim($_POST['status'] ?? '');

// Validation
if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status
$valid_statuses = ['scheduled', 'completed', 'cancelled', 'rescheduled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Check if appointment exists
$checkAppt = $conn->query("SELECT id FROM appointments WHERE id = $id");
if (!$checkAppt || $checkAppt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit();
}

// Update appointment
$stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("si", $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}
$stmt->close();
?>
