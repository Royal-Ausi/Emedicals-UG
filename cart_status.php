<?php
session_start();
header('Content-Type: application/json');
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $c) { $total += ($c['price'] * $c['qty']); }
echo json_encode(['cart' => $cart, 'total' => $total]);
exit;
