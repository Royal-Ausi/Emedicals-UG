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
    echo json_encode(['success' => false, 'message' => 'Invalid doctor ID']);
    exit();
}

// Check if doctor exists
$checkDoctor = $conn->query("SELECT id FROM doctors WHERE id = $id");
if (!$checkDoctor || $checkDoctor->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Doctor not found']);
    exit();
}

// Get the user_id associated with this doctor
$getDoctorUser = $conn->query("SELECT user_id FROM doctors WHERE id = $id");
$doctorRow = $getDoctorUser->fetch_assoc();
$user_id = $doctorRow['user_id'];

// Delete in correct order to avoid foreign key constraint errors:
// 1. Delete from doctor_availability (FK to users)
$conn->query("DELETE FROM doctor_availability WHERE doctor_id = $id");
// 2. Delete all appointments for this doctor
$conn->query("DELETE FROM appointments WHERE doctor_id = $user_id");
// 3. Delete all patient_reports for this doctor
$conn->query("DELETE FROM patient_reports WHERE doctor_id = $user_id");
// 4. Delete doctor record
$conn->query("DELETE FROM doctors WHERE id = $id");
// 5. Delete associated user
$result = $conn->query("DELETE FROM users WHERE id = $user_id");

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>
