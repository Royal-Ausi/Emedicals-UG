<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); 
exit; }

$order_id = $_SESSION['current_order_id'] ?? 0;
if (!$order_id) {
    header('Location: pharmacy.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Medicals UG | Payment</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root{--primary:#003366;--accent:#007bff;--hover:#0055aa;}
        body{padding:20px; font-family:'Inter',sans-serif;}
        h4{color:var(--primary);}
        .btn-accent{background:var(--accent); color:#fff; border:none;}
        .btn-accent:hover{background:var(--hover); color:#fff;}
    </style>
</head>
<body>
    <h4>Payment</h4>
    <p>Select a payment method (for now we just simulate success):</p>

    <form method="post" action="payment_process.php">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="method" id="cod" value="cod" checked>
            <label class="form-check-label" for="cod">Cash on Delivery</label>
        </div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="radio" name="method" id="momo" value="momo">
            <label class="form-check-label" for="momo">Mobile Money (simulate)</label>
        </div>
        <button class="btn btn-accent mt-3">Pay Now</button>
    </form>
</body>
</html>
