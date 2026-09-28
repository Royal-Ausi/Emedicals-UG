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
$specialization = trim($_POST['specialization'] ?? '');
$consultation_fee = trim($_POST['consultation_fee'] ?? '');

// Validation
if (!$id || !$specialization || !$consultation_fee) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Check if doctor exists
$checkDoctor = $conn->query("SELECT id FROM doctors WHERE id = $id");
if (!$checkDoctor || $checkDoctor->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Doctor not found']);
    exit();
}

// Validate consultation fee is numeric
if (!is_numeric($consultation_fee)) {
    echo json_encode(['success' => false, 'message' => 'Consultation fee must be a number']);
    exit();
}

// Update doctor
$stmt = $conn->prepare("UPDATE doctors SET specialization = ?, consultation_fee = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sdi", $specialization, $consultation_fee, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}
$stmt->close();
?>
