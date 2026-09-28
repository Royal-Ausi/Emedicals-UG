<?php
session_start();
require 'db.php';

if ($_SESSION['user_type'] !== 'doctor') exit;

$doctor_id = $_SESSION['user_id'];
$appointment_id = $_POST['appointment_id'];
$drug_ids = $_POST['drug_id'];
$notes = $_POST['notes'];

$stmt = $conn->prepare(
    "SELECT patient_id FROM appointments WHERE id=?"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$patient_id = $stmt->get_result()->fetch_assoc()['patient_id'];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "INSERT INTO prescriptions 
        (appointment_id, doctor_id, patient_id, notes)
        VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iiis", $appointment_id, $doctor_id, $patient_id, $notes);
    $stmt->execute();
    $prescription_id = $stmt->insert_id;

    $item = $conn->prepare(
        "INSERT INTO prescription_items
        (prescription_id, drug_id, dosage, duration, quantity)
        VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($drug_ids as $drug_id) {
        $item->bind_param(
            "iissi",
            $prescription_id,
            $drug_id,
            $_POST['dosage'][$drug_id],
            $_POST['duration'][$drug_id],
            $_POST['qty'][$drug_id]
        );
        $item->execute();
    }

    $conn->commit();
    header("Location: dashboard.php?success=prescribed");
} catch (Exception $e) {
    $conn->rollback();
    die("Error saving prescription");
}
