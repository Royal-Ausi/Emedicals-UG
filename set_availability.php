<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$date = $_POST['available_date'] ?? null;
$start = $_POST['start_time'] ?? null;
$end = $_POST['end_time'] ?? null;

if (!$date || !$start || !$end) {
    $_SESSION['toast_message'] = "Please provide date, start and end times.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

// basic validation: start < end
if (strtotime($start) >= strtotime($end)) {
    $_SESSION['toast_message'] = "Start time must be earlier than end time.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

// insert - allow duplicates check via unique constraint or manual check
$stmt = $conn->prepare("INSERT INTO doctor_availability (doctor_id, available_date, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, 20)");
$stmt->bind_param("isss", $doctor_id, $date, $start, $end);

if ($stmt->execute()) {
    $_SESSION['toast_message'] = "✅ Availability saved!";
    $_SESSION['toast_type'] = "success";
} else {
    // if duplicate / error
    $_SESSION['toast_message'] = "❌ Could not save availability (maybe duplicate).";
    $_SESSION['toast_type'] = "error";
}

$stmt->close();
header("Location: dashboard.php");
exit();
