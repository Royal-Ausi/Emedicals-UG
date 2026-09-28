<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=? AND doctor_id=?");
    $stmt->bind_param("sii", $status, $appointment_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['toast_message'] = " Appointment status updated!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_message'] = " Failed to update appointment. Try again.";
        $_SESSION['toast_type'] = "error";
    }
    $stmt->close();
}

$from = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($from, 'manage_appointments.php') !== false) {
    header('Location: manage_appointments.php');
} else {
    header('Location: dashboard.php#appointments');
}
exit();
