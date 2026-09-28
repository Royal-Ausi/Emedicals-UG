<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: index.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $slot_date = $_POST['slot_date'];
    $slot_time = $_POST['slot_time'];
    $symptoms = trim($_POST['symptoms']);
    $session_type = $_POST['session_type'] ?? 'Physical';
    $payment_method = $_POST['payment_method'] ?? 'mobilemoney'; // enforce online

    if (empty($doctor_id) || empty($slot_date) || empty($slot_time) || empty($symptoms)) {
        $_SESSION['toast_message'] = "Please fill all fields.";
        $_SESSION['toast_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }

    $appointment_date = date("Y-m-d H:i:s", strtotime("$slot_date $slot_time"));
    $appointment_end_time = date("Y-m-d H:i:s", strtotime("$appointment_date +20 minutes"));
    $amount = 50000; // UGX consultation fee

    // Check if slot is already booked
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id=? AND appointment_date=?");
    $check->bind_param("is", $doctor_id, $appointment_date);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    $check->close();

    if ($result['cnt'] > 0) {
        $_SESSION['toast_message'] = "This slot is already booked. Please choose another time.";
        $_SESSION['toast_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }

    $conn->begin_transaction();
    try {
        // Insert appointment
        $stmt = $conn->prepare("
            INSERT INTO appointments 
            (patient_id, doctor_id, appointment_date, appointment_end_time, symptoms, status, session_type, payment_status)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, 'pending')
        ");
        $stmt->bind_param("iissss", $patient_id, $doctor_id, $appointment_date, $appointment_end_time, $symptoms, $session_type);
        $stmt->execute();
        $appointment_id = $stmt->insert_id;
        $stmt->close();

        // Insert payment
        $payment_reference = "EMED_" . $appointment_id . "_" . time();
        $stmt2 = $conn->prepare("
            INSERT INTO payments
            (appointment_id, patient_id, amount, payment_reference, payment_method, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt2->bind_param("iidss", $appointment_id, $patient_id, $amount, $payment_reference, $payment_method);
        $stmt2->execute();
        $stmt2->close();

        // Temporarily mark payment as completed without external gateway
        $updatePay = $conn->prepare("UPDATE payments SET status='paid' WHERE payment_reference=?");
        $updatePay->bind_param("s", $payment_reference);
        $updatePay->execute();
        $updatePay->close();

        $updateAppt = $conn->prepare("UPDATE appointments SET payment_status='paid' WHERE id=?");
        $updateAppt->bind_param("i", $appointment_id);
        $updateAppt->execute();
        $updateAppt->close();

        $conn->commit();

        $_SESSION['toast_message'] = "Appointment booked and payment recorded successfully.";
        $_SESSION['toast_type'] = "success";
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast_message'] = "Booking failed.";
        $_SESSION['toast_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }
}
