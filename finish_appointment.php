<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$appointment_id = intval($_POST['appointment_id'] ?? 0);

if (!$appointment_id) {
    $_SESSION['toast_message'] = "Invalid appointment.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

// ensure this appointment belongs to this doctor
$stmt = $conn->prepare("SELECT doctor_id FROM appointments WHERE id=?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || intval($row['doctor_id']) !== $doctor_id) {
    $_SESSION['toast_message'] = "You are not allowed to modify this appointment.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$u = $conn->prepare("UPDATE appointments SET status='completed' WHERE id=?");
$u->bind_param("i", $appointment_id);
$u->execute();
$u->close();

$_SESSION['toast_message'] = "✅ Appointment marked as completed.";
$_SESSION['toast_type'] = "success";
header("Location: dashboard.php");
exit();
