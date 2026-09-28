<?php
// process_shipping.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // login required
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: pharmacy.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$address   = trim($_POST['address'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$method    = $_POST['method'] ?? 'cod';

if ($full_name === '' || $address === '' || $phone === '') {
    header('Location: pharmacy.php');
    exit;
}

// Only Cash on Delivery allowed for now
if ($method !== 'cod') {
    header('Location: pharmacy.php?err=payment_unavailable');
    exit;
}

// Calculate total
$total = 0;
foreach ($cart as $c) {
    $total += $c['price'] * $c['qty'];
}

// Insert order (status pending; payment_method cod)
$stmt = $conn->prepare("
    INSERT INTO orders (full_name, address, phone, total_ugx, status, payment_method, user_id)
    VALUES (?,?,?,?, 'pending', 'cod', ?)
");
$stmt->bind_param("sssii", $full_name, $address, $phone, $total, $user_id);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert order items
$itemStmt = $conn->prepare("
    INSERT INTO order_items (order_id, medicine_id, name_snapshot, unit_price_ugx, quantity)
    VALUES (?,?,?,?,?)
");
foreach ($cart as $c) {
    $itemStmt->bind_param("iisii", $order_id, $c['id'], $c['name'], $c['price'], $c['qty']);
    $itemStmt->execute();
}

// Reduce stock
$stockStmt = $conn->prepare("UPDATE medicines SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
foreach ($cart as $c) {
    $stockStmt->bind_param("ii", $c['qty'], $c['id']);
    $stockStmt->execute();
}

// Clear cart and store current order
$_SESSION['cart'] = [];
$_SESSION['current_order_id'] = $order_id;

// Redirect to cart page to show the new order
header('Location: cart.php?order=created');
exit;
