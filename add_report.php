<?php
session_start();
require 'db.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// Get appointment ID from query string
$appointment_id = $_GET['appointment_id'] ?? $_POST['appointment_id'] ?? null;

if (!$appointment_id) {
    $_SESSION['toast_message'] = "Invalid appointment ID.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

// Fetch appointment info for reference (optional)
$stmt = $conn->prepare("SELECT a.patient_id, u.fullname AS patient_name FROM appointments a JOIN users u ON a.patient_id = u.id WHERE a.id=? AND a.doctor_id=?");
$stmt->bind_param("ii", $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

if (!$appointment) {
    $_SESSION['toast_message'] = " Appointment not found.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$patient_id = $appointment['patient_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report = trim($_POST['report'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $prescription = trim($_POST['prescription'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $follow_up_date = $_POST['follow_up_date'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO patient_reports
        (appointment_id, doctor_id, patient_id, report, diagnosis, prescription, notes, follow_up_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiisssss",
        $appointment_id,
        $doctor_id,
        $patient_id,
        $report,
        $diagnosis,
        $prescription,
        $notes,
        $follow_up_date
    );

    if ($stmt->execute()) {
        $_SESSION['toast_message'] = "Report successfully saved!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_message'] = "Failed to save report.";
        $_SESSION['toast_type'] = "error";
    }

    $stmt->close();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Report | E-Medicals UG</title>
<link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    margin: 0;
    padding: 0;
}


.header {
    background: #004080;
    color: #fff;
    text-align: center;
    padding: 20px;
    font-size: 1.3rem;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.back-btn {
    display: inline-block;
    background: rgba(255,255,255,0.12);
    color: #fff;
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}
.back-btn:hover { background: rgba(255,255,255,0.18); }

.container {
    max-width: 600px;
    margin: 40px auto;
    background: #fff;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.container:hover {
    transform: translateY(-3px);
}

/* Form title */
h2 {
    text-align: center;
    color: #004080;
    margin-bottom: 25px;
}

/* Form groups */
.form-group {
    margin-bottom: 18px;
}
label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #004080;
}
input, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
input:focus, textarea:focus {
    border-color: #004080;
    box-shadow: 0 0 5px rgba(0,64,128,0.3);
    outline: none;
}

/* Button styling */
button {
    background: #004080;
    color: #fff;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s, transform 0.2s;
    width: 100%;
}
button:hover {
    background: #0066cc;
    transform: translateY(-2px);
}

/* Toast */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #4caf50;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    z-index: 9999;
    box-shadow: 0 0 12px rgba(0,0,0,.25);
    animation: fadein .5s, fadeout .5s 3.5s;
}
.toast.error {
    background: #f44336;
}
@keyframes fadein { from {opacity:0;} to {opacity:1;} }
@keyframes fadeout { from {opacity:1;} to {opacity:0;} }

/* Responsiveness */
@media(max-width: 640px){
    .container { margin: 20px; padding: 20px; }
}
</style>
</head>
<body>

<div class="header">
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;">
        <a href="dashboard.php" class="back-btn" title="Back to Dashboard">Back</a>
        <div style="margin-bottom: 10px;">
            <img src="includes/images/logo 22.jpg" alt="E-Medicals Logo" style="height: 35px; width: auto; object-fit: contain;">
        </div>
        <div style="font-weight:700">Add Report for <?= htmlspecialchars($appointment['patient_name']); ?></div>
    </div>
</div>

<div class="container">
    <form method="POST" action="">
        <div class="form-group">
            <label for="report">Report</label>
            <textarea name="report" id="report" rows="4" required placeholder="Describe the consultation details..."></textarea>
        </div>
        <div class="form-group">
            <label for="diagnosis">Diagnosis</label>
            <textarea name="diagnosis" id="diagnosis" rows="3" placeholder="Patient diagnosis..."></textarea>
        </div>
        <div class="form-group">
            <label for="prescription">Prescription</label>
            <textarea name="prescription" id="prescription" rows="3" placeholder="Medicines or advice..."></textarea>
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Additional notes..."></textarea>
        </div>
        <div class="form-group">
            <label for="follow_up_date">Follow-up Date</label>
            <input type="datetime-local" name="follow_up_date" id="follow_up_date">
        </div>
        <button type="submit">Save Report</button>
    </form>
</div>

<!-- Toast -->
<?php if(isset($_SESSION['toast_message'])): ?>
<div class="toast <?= $_SESSION['toast_type']; ?>">
    <?= $_SESSION['toast_message']; ?>
</div>
<script>
setTimeout(() => { document.querySelector('.toast').style.display='none'; }, 4000);
</script>
<?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
<?php endif; ?>

</body>
</html>
