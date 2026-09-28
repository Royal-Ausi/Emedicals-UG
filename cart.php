<?php
session_start();
require 'db.php';

// --- Remove item from session cart ---
if (isset($_GET['remove'])) {
    $i = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$i])) {
        unset($_SESSION['cart'][$i]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit;
}

// --- Prepare cart data ---
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $c) { $total += $c['price'] * $c['qty']; }

// --- Fetch orders for logged-in user ---
$orders_pending = [];
$orders_delivered = [];

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $res = $conn->query("
        SELECT id, total_ugx, status, payment_method, created_at
        FROM orders
        WHERE user_id = $uid
        ORDER BY created_at DESC
    ");
    while ($o = $res->fetch_assoc()) {
        if ($o['status'] === 'delivered') {
            $orders_delivered[] = $o;
        } else {
            $orders_pending[] = $o; // pending/paid/cancelled can be shown here; focus on pending
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart & Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root{--primary:#003366;--accent:#007bff;--hover:#0055aa;}
        body{padding:20px; font-family:'Inter',sans-serif; background:#f4f7fb;}
        h4,h5{color:var(--primary);}
        .btn-accent{background:var(--accent); color:#fff; border:none;}
        .btn-accent:hover{background:var(--hover); color:#fff;}
        .card{background:#fff; border-radius:10px; padding:20px; box-shadow:0 3px 10px rgba(0,0,0,0.1); margin-bottom:20px;}
        .status-badge{font-size:.85rem;}
    </style>
</head>
<body>

    <div class="card">
        <h4>Your Cart</h4>
        <?php if ($cart): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th><th>Qty</th><th>Unit (UGX)</th><th>Subtotal</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($cart as $i => $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= $c['qty'] ?></td>
                        <td><?= number_format($c['price']) ?></td>
                        <td><?= number_format($c['price'] * $c['qty']) ?></td>
                        <td><a class="text-danger" href="cart.php?remove=<?= $i ?>" onclick="return confirm('Remove this item?')">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th><?= number_format($total) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <a href="pharmacy.php" class="btn btn-accent">Back to Pharmacy</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
            <a href="pharmacy.php" class="btn btn-accent">Browse Medicines</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h5>Your Orders (Pending)</h5>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p>Please log in to view your orders.</p>
        <?php elseif (!$orders_pending): ?>
            <p>No pending orders.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Order #</th><th>Total (UGX)</th><th>Payment</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                <?php foreach($orders_pending as $o): ?>
                    <tr>
                        <td><?= (int)$o['id'] ?></td>
                        <td><?= number_format($o['total_ugx']) ?></td>
                        <td><?= strtoupper($o['payment_method']) ?></td>
                        <td><span class="badge bg-warning status-badge"><?= ucfirst($o['status']) ?></span></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h5>Your Orders (Delivered)</h5>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p>Please log in to view delivered orders.</p>
        <?php elseif (!$orders_delivered): ?>
            <p>No delivered orders yet.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Order #</th><th>Total (UGX)</th><th>Payment</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                <?php foreach($orders_delivered as $o): ?>
                    <tr>
                        <td><?= (int)$o['id'] ?></td>
                        <td><?= number_format($o['total_ugx']) ?></td>
                        <td><?= strtoupper($o['payment_method']) ?></td>
                        <td><span class="badge bg-success status-badge">Delivered</span></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
