<?php
session_start();
require_once __DIR__ . '/includes/env.php';
require 'db.php';

$payment_ref = $_GET['payment_ref'] ?? null;
if (!$payment_ref) die("Payment reference missing.");

$stmt = $conn->prepare("SELECT * FROM payments WHERE payment_reference=?");
$stmt->bind_param("s", $payment_ref);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) die("Invalid payment reference.");

$amount = $payment['amount'] * 100; // Paystack expects kobo
$email = $_SESSION['email'] ?? 'patient@email.com';
$fullname = $_SESSION['fullname'] ?? 'Patient Name';

$paystack_public_key = env_value('PAYSTACK_PUBLIC_KEY', '');
?>

<!DOCTYPE html>
<html>

<head>
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>

<body>
    <script>
        var handler = PaystackPop.setup({
            key: <?= json_encode($paystack_public_key) ?>,
            email: '<?= htmlspecialchars($email) ?>',
            amount: <?= $amount ?>,
            ref: '<?= $payment_ref ?>',
            callback: function(response) {
                // Payment complete, redirect to verification
                window.location = "verify_payment.php?reference=" + response.reference;
            },
            onClose: function() {
                alert('Payment window closed.');
            }
        });
        handler.openIframe();
    </script>
</body>

</html>