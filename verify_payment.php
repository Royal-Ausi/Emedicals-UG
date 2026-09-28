<?php
session_start();
require_once __DIR__ . '/includes/env.php';
require 'db.php';

$reference = $_GET['reference'] ?? null;
if (!$reference) die("Reference missing.");

$secret_key = env_value('PAYSTACK_SECRET_KEY', '');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/$reference");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secret_key"
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['status'] && $result['data']['status'] === 'success') {
    // Payment successful
    $stmt = $conn->prepare("UPDATE payments SET status='paid', transaction_id=? , paid_at=NOW() WHERE payment_reference=?");
    $stmt->bind_param("ss", $result['data']['id'], $reference);
    $stmt->execute();
    $stmt->close();

    // Update appointment
    $conn->query("
        UPDATE appointments a
        JOIN payments p ON a.id = p.appointment_id
        SET a.payment_status='paid', a.status='confirmed'
        WHERE p.payment_reference='$reference'
    ");

    $_SESSION['toast_message'] = "Payment successful! Appointment confirmed.";
    $_SESSION['toast_type'] = "success";
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['toast_message'] = "Payment failed or cancelled.";
    $_SESSION['toast_type'] = "error";
    header("Location: dashboard.php");
    exit();
}
