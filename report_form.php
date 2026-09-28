<?php
session_start();
require 'db.php';

// Check if doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    header("Location: doctor_dashboard.php");
    exit();
}

$appointment_id = intval($_GET['appointment_id']);

// Fetch appointment details
$stmt = $conn->prepare("
    SELECT a.*, u.fullname AS patient_name, u.email AS patient_email
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: doctor_dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Medicals UG |Patient Report</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container" style="max-width: 700px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px;">
    <h2>Add Diagnosis Report</h2>
    <p><strong>Patient:</strong> <?php echo $appointment['patient_name']; ?></p>
    <p><strong>Email:</strong> <?php echo $appointment['patient_email']; ?></p>
    <p><strong>Date:</strong> <?php echo $appointment['appointment_date']; ?></p>
    <p><strong>Time:</strong> <?php echo $appointment['appointment_time']; ?></p>
    <p><strong>Symptoms:</strong> <?php echo $appointment['symptoms']; ?></p>
    <hr>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="add_report.php">

        <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
        <input type="hidden" name="patient_id" value="<?php echo $appointment['patient_id']; ?>">

        <label>Diagnosis *</label>
        <textarea name="diagnosis" required placeholder="Enter diagnosis..." style="height: 120px;"></textarea>

        <label>Prescription</label>
        <textarea name="prescription" placeholder="Medication or treatment prescribed..."></textarea>

        <label>Doctor Notes</label>
        <textarea name="notes" placeholder="Any extra notes..."></textarea>

        <label>Follow-Up Date (optional)</label>
        <input type="date" name="follow_up_date">

        <button type="submit" class="btn">Save Report</button>
        <a href="doctor_dashboard.php" class="btn cancel">Cancel</a>

    </form>
</div>

</body>
</html>
