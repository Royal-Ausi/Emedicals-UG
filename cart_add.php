<?php
// cart_add.php
session_start();

$id    = (int)($_POST['id'] ?? 0);
$name  = trim($_POST['name'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$qty   = max(1, (int)($_POST['qty'] ?? 1));

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($id && $name && $price > 0) {
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $id) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($item);
    if (!$found) {
        $_SESSION['cart'][] = [
            'id'    => $id,
            'name'  => $name,
            'price' => $price,
            'qty'   => $qty
        ];
    }
}

// compute total
$total = 0;
foreach ($_SESSION['cart'] as $c) { $total += ($c['price'] * $c['qty']); }

// If this request is AJAX, return JSON instead of redirecting
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart'], 'total' => $total]);
    exit;
}


header('Location: pharmacy.php');
exit;
