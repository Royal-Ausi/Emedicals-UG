<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') { header("Location: index.php"); exit(); }

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("UPDATE orders SET status='delivered' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header('Location: admin_orders.php');
exit;
