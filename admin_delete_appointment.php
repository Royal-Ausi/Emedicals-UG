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

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

// Checks if appointment exists
$checkAppt = $conn->query("SELECT id FROM appointments WHERE id = $id");
if (!$checkAppt || $checkAppt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit();
}

// Delete the appointment
$result = $conn->query("DELETE FROM appointments WHERE id = $id");

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>
