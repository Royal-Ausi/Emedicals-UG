<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $new_date = $_POST['new_date'];

    $stmt = $conn->prepare("UPDATE appointments SET appointment_date=? WHERE id=? AND doctor_id=?");
    $stmt->bind_param("sii", $new_date, $appointment_id, $doctor_id);
    if ($stmt->execute()) {
        $_SESSION['toast_message'] = "✅ Appointment rescheduled successfully!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_message'] = "❌ Failed to reschedule. Please try again.";
        $_SESSION['toast_type'] = "error";
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// GET request to fetch appointment info
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = $_GET['id'];

$stmt = $conn->prepare("SELECT a.id, a.appointment_date, u.fullname AS patient_name 
                        FROM appointments a
                        JOIN users u ON a.patient_id = u.id
                        WHERE a.id=? AND a.doctor_id=?");
$stmt->bind_param("ii", $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['toast_message'] = "❌ Appointment not found!";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reschedule Appointment</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: Arial, sans-serif; background:#f5f6fa; margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh; }
.modal-content { background:#fff; padding:20px; border-radius:10px; width:400px; box-shadow:0 0 10px rgba(0,0,0,.2);}
h3 { margin-bottom:20px; text-align:center; }
.form-group { margin-bottom:15px; }
.form-group label { display:block; margin-bottom:5px; }
.form-group input { width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; }
.btn-primary { background:#004080; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; width:100%; }
.btn-primary:hover { background:#0066cc; }
</style>
</head>
<body>
<div class="modal-content">
    <h3>Reschedule Appointment</h3>
    <form method="POST" action="">
        <input type="hidden" name="appointment_id" value="<?= $appointment['id']; ?>">
        <div class="form-group">
            <label>Patient</label>
            <input type="text" value="<?= htmlspecialchars($appointment['patient_name']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>New Date & Time</label>
            <input type="datetime-local" name="new_date" value="<?= date("Y-m-d\TH:i", strtotime($appointment['appointment_date'])); ?>" required>
        </div>
        <button type="submit" class="btn-primary">Update Appointment</button>
    </form>
</div>
</body>
</html>
