<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') { header("Location: index.php"); exit(); }

$orders = $conn->query("
    SELECT o.id, o.full_name, o.phone, o.address, o.total_ugx, o.status, o.payment_method, o.created_at,
           u.fullname AS user_fullname, u.email AS user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin - Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body{background:#f4f7fb; font-family:'Inter',sans-serif;}
        .card{background:#fff; padding:20px; margin:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
        h2{color:#003366;}
        table{width:100%; border-collapse:collapse;}
        th,td{padding:12px; border-bottom:1px solid #ddd; text-align:left;}
        th{background:#003366; color:#fff;}
        a.btn{background:#007bff; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none;}
        a.btn:hover{background:#0055aa;}
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="includes/images/logo 22.jpg" alt="E-Medicals Logo" style="height: 50px; width: auto; border-radius: 10px; object-fit: contain;">
        </div>
        <h1 style="text-align:center; margin-bottom:20px; font-size:18px;">ADMIN PANEL</h1>
        <a href="admin_dashboard.php">Users</a>
        <a href="admin_dashboard.php">Doctors</a>
        <a href="admin_dashboard.php">Appointments</a>
        <a href="admin_dashboard.php">Doctor Availability</a>
        <a href="admin_orders.php" class="active">Pharmacy Orders</a>
        <a href="admin_dashboard.php">Pending Doctor Approvals</a>
        <a href="admin_medicines.php">Pharmacy Catalog</a>
        <a href="adminBlogs.php">Blog Management</a>
        <form action="logout.php" method="POST"><button class="logout-btn">Logout</button></form>
    </div>
    <div class="main">
        <div class="card active">
            <h2>Pharmacy Orders</h2>
            <table class="mt-3">
                <tr>
                    <th>Order #</th><th>User</th><th>Contact</th><th>Total (UGX)</th><th>Payment</th><th>Status</th><th>Placed</th><th>Actions</th>
                </tr>
                <?php while($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$o['id'] ?></td>
                    <td><?= htmlspecialchars($o['user_fullname']) ?> <br><small><?= htmlspecialchars($o['user_email']) ?></small></td>
                    <td><?= htmlspecialchars($o['phone']) ?><br><small><?= htmlspecialchars($o['address']) ?></small></td>
                    <td><?= number_format($o['total_ugx']) ?></td>
                    <td><?= strtoupper($o['payment_method']) ?></td>
                    <td><?= ucfirst($o['status']) ?></td>
                    <td><?= htmlspecialchars($o['created_at']) ?></td>
                    <td>
                        <?php if ($o['status'] !== 'delivered'): ?>
                            <a class="btn" href="admin_order_deliver.php?id=<?= (int)$o['id'] ?>">Mark Delivered</a>
                        <?php else: ?>
                            <span>Delivered</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>
<style>
    .container { display:flex; min-height:100vh; }
    .sidebar { width:260px; background:#003366; color:#fff; padding:20px; overflow-y:auto; max-height:100vh; }
    .sidebar h1 { text-align:center; margin-bottom:20px; font-size:18px; }
    .sidebar a { display:block; padding:12px; margin:5px 0; border-radius:6px; color:#fff; text-decoration:none; cursor:pointer; transition:0.3s; }
    .sidebar a:hover { background:#0055aa; }
    .sidebar a.active { background:#007bff; }
    .main { flex:1; padding:25px; overflow-y:auto; max-height:100vh; }
    .card { background:#fff; padding:20px; margin-bottom:25px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
    .card h2 { color:#003366; }
    table { width:100%; border-collapse:collapse; }
    th,td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#003366; color:#fff; }
    a.btn { background:#007bff; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; }
    a.btn:hover { background:#0055aa; }
    .logout-btn { background:#c62828; color:#fff; padding:10px 18px; border:none; border-radius:5px; cursor:pointer; font-weight:600; width:100%; margin-top:20px; }
    .logout-btn:hover { background:#a30f0f; }
</style>
</body>
</html>
