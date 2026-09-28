<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
    $action = $_POST['action'] ?? null;

    if (!$doctor_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit();
    }

    // Fetch the user_id associated with this doctor
    $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        exit();
    }
    $stmt->close();

    // Determine new status
    if ($action === 'approve') {
        $new_status = 'active';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit();
    }

    // Update the user's status
    $update = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $update->bind_param("si", $new_status, $user_id);
    if (!$update->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        exit();
    }
    $update->close();

    // If rejected, remove doctor from doctors table
    if ($action === 'reject') {
        $delete = $conn->prepare("DELETE FROM doctors WHERE id = ?");
        $delete->bind_param("i", $doctor_id);
        $delete->execute();
        $delete->close();
    }

    echo json_encode(['success' => true, 'message' => $action === 'approve' ? 'Doctor approved' : 'Doctor rejected']);
    exit();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

