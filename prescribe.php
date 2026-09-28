<?php
session_start();
require 'db.php';

if ($_SESSION['user_type'] !== 'doctor') {
    header("Location: dashboard.php");
    exit;
}

$appointment_id = intval($_GET['appointment_id']);

// get appointment
$stmt = $conn->prepare("
    SELECT a.patient_id, u.fullname 
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

// drugs
$drugs = $conn->query("SELECT * FROM pharmacy_drugs WHERE quantity > 0");
?>
<!doctype html>
<html>
<head>
<title>Prescribe Drugs</title>
<style>
body{font-family:Inter;background:#f5f6fa;padding:20px}
.card{background:#fff;padding:20px;border-radius:10px}
</style>
</head>
<body>

<div class="card">
<h2>Prescribe Drugs</h2>
<p>Patient: <strong><?= htmlspecialchars($appt['fullname']); ?></strong></p>

<form method="POST" action="save_prescription.php">
<input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

<table width="100%" border="1" cellpadding="8">
<tr>
<th>Drug</th><th>Dosage</th><th>Duration</th><th>Qty</th>
</tr>

<?php while ($d = $drugs->fetch_assoc()): ?>
<tr>
<td>
<input type="checkbox" name="drug_id[]" value="<?= $d['id']; ?>">
<?= htmlspecialchars($d['drug_name']); ?>
</td>
<td><input type="text" name="dosage[<?= $d['id']; ?>]"></td>
<td><input type="text" name="duration[<?= $d['id']; ?>]"></td>
<td><input type="number" min="1" name="qty[<?= $d['id']; ?>]"></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<textarea name="notes" placeholder="Doctor notes" style="width:100%"></textarea>
<br><br>
<button class="btn">Save Prescription</button>
</form>
</div>

</body>
</html>
