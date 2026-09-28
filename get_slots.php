<?php
require 'db.php';

$doctor_id = $_GET['doctor_id'] ?? '';
$date = $_GET['date'] ?? '';

header('Content-Type: application/json');

if (!$doctor_id || !$date) {
    echo json_encode([]);
    exit;
}

// Fetch doctor availability for the selected date
$stmt = $conn->prepare("
    SELECT start_time, end_time
    FROM doctor_availability
    WHERE doctor_id=? AND available_date=?
");
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    while ($start + 20*60 <= $end) {
        $slots[] = date("H:i", $start);
        $start += 20*60; // 20-minute slots
    }
}

// Remove slots that are already booked
$booked_stmt = $conn->prepare("
    SELECT appointment_date 
    FROM appointments 
    WHERE doctor_id=? AND DATE(appointment_date)=?
");
$booked_stmt->bind_param("is", $doctor_id, $date);
$booked_stmt->execute();
$booked_result = $booked_stmt->get_result();

$booked_slots = [];
while ($b = $booked_result->fetch_assoc()) {
    $booked_slots[] = date("H:i", strtotime($b['appointment_date']));
}
$booked_stmt->close();

// Filter out booked slots
$available_slots = array_diff($slots, $booked_slots);

echo json_encode(array_values($available_slots));
exit;
?>
