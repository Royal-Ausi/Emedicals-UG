<?php
require_once __DIR__ . '/includes/env.php';
require 'db.php';

$secret_hash = env_value('WEBHOOK_SECRET', '');
$provided_hash = $_SERVER['HTTP_VERIF_HASH'] ?? '';

if ($secret_hash === '' || !hash_equals($secret_hash, $provided_hash)) {
    http_response_code(401);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if ($input['event'] === 'charge.completed') {

    if ($input['data']['status'] === 'successful') {

        $tx_ref = $input['data']['tx_ref'];
        $transaction_id = $input['data']['id'];
        $method = $input['data']['payment_type'];

        // Update payment
        $stmt = $conn->prepare("
            UPDATE payments 
            SET status='paid',
                transaction_id=?,
                payment_method=?,
                paid_at=NOW()
            WHERE payment_reference=?
        ");
        $stmt->bind_param("sss", $transaction_id, $method, $tx_ref);
        $stmt->execute();

        // Update appointment
        $conn->query("
            UPDATE appointments a
            JOIN payments p ON a.id = p.appointment_id
            SET a.payment_status='paid',
                a.status='confirmed'
            WHERE p.payment_reference='$tx_ref'
        ");
    }
}

http_response_code(200);
