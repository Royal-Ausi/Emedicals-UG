<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php'); exit;
}

// Handle add / delete actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $dosage = trim($_POST['dosage'] ?? '');
        $tablets = (int)($_POST['tablets_count'] ?? 0);
        $desc = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price_ugx'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        if ($name && $price >= 0) {
            $stmt = $conn->prepare("INSERT INTO medicines (name, dosage, tablets_count, description, price_ugx, stock, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param('ssidsi', $name, $dosage, $tablets, $desc, $price, $stock);
            if ($stmt->execute()) { $message = 'Medicine added.'; } else { $message = 'Failed to add: ' . $conn->error; }
            $stmt->close();
        } else {
            $message = 'Name and price are required.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // soft-delete by setting status inactive
            $stmt = $conn->prepare("UPDATE medicines SET status='inactive' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) { $message = 'Medicine marked inactive.'; } else { $message = 'Failed to remove: ' . $conn->error; }
            $stmt->close();
        }
    }
}

$res = $conn->query("SELECT * FROM medicines ORDER BY name ASC");
$meds = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin — Pharmacy Catalog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif;background:#f4f7fb;padding:20px}
        .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.06)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
        th{background:#003366;color:#fff}
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h1><img src="includes/images/logo 22.jpg" alt="Logo" style="height:40px;width:auto;display:block;margin:0 auto 10px auto;"> E-Medicals UG</h1>
            <a href="admin_dashboard.php">Dashboard</a>
            
            <a href="admin_medicines.php" class="active">Medicines</a>
           
            
            <a href="logout.php" class="logout-btn">Logout</a>
            <div style="margin-top:20px;padding:12px;background:#fff;color:#003366;border-radius:10px;box-shadow:0 6px 18px rgba(6,30,78,0.04)">
               
                
                
                
            </div>
        </aside>

        <main class="main" style="flex:1">
            <div class="card">
                <h2>Pharmacy Catalog</h2>
                <?php if ($message): ?>
                    <div id="server-message" data-message="<?= htmlspecialchars($message, ENT_QUOTES) ?>"></div>
                <?php endif; ?>

                <h3 style="margin-top:10px">Add Medicine</h3>
        <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
            <input type="hidden" name="action" value="add">
            <input name="name" placeholder="Name" required style="padding:8px;border:1px solid #ddd;border-radius:6px">
            <input name="dosage" placeholder="Dosage (e.g. 500mg)" style="padding:8px;border:1px solid #ddd;border-radius:6px">
            <input name="tablets_count" placeholder="Tablets count" type="number" style="width:120px;padding:8px;border:1px solid #ddd;border-radius:6px">
            <input name="price_ugx" placeholder="Price (UGX)" type="number" step="0.01" style="width:140px;padding:8px;border:1px solid #ddd;border-radius:6px">
            <input name="stock" placeholder="Stock" type="number" style="width:100px;padding:8px;border:1px solid #ddd;border-radius:6px">
            <input name="description" placeholder="Short description" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px">
            <button class="btn btn-blue" type="submit">Add Product</button>
        </form>

        <?php if (!$meds): ?>
            <p>No medicines found.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Dosage</th><th>Stock</th><th>Price (UGX)</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($meds as $m): ?>
                    <tr>
                        <td><?= (int)$m['id'] ?></td>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><?= htmlspecialchars($m['dosage'] ?? '') ?></td>
                        <td><?= (int)($m['stock'] ?? 0) ?></td>
                        <td><?= number_format($m['price_ugx'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($m['status'] ?? 'active') ?></td>
                        <td>
                            <?php if (($m['status'] ?? '') === 'active'): ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('Mark this product inactive?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                    <button class="btn btn-red" type="submit">Remove</button>
                                </form>
                            <?php else: ?>
                                <em>removed</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="admin-toast" style="position:fixed;right:20px;bottom:20px;display:none;background:#0f5132;color:#e6f4ea;padding:12px 16px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.18);z-index:12000"></div>
    <style>
        .container { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:#003366; color:#fff; padding:20px; overflow-y:auto; max-height:100vh; }
        .sidebar h1 { text-align:center; margin-bottom:20px; font-size:18px; }
        .sidebar a { display:block; padding:12px; margin:5px 0; border-radius:6px; color:#fff; text-decoration:none; cursor:pointer; transition:0.3s; }
        .sidebar a:hover { background:#0055aa; }
        .sidebar a.active { background:#007bff; }
        .logout-btn { background:#c62828; color:#fff; padding:10px 18px; border:none; border-radius:5px; cursor:pointer; font-weight:600; width:100%; margin-top:20px; }
        .logout-btn:hover { background:#a30f0f; }
    </style>

    <script>
        // show server message as JS popup
        (function(){
            var el = document.getElementById('server-message');
            if (!el) return;
            var msg = el.getAttribute('data-message') || '';
            if (!msg) return;
            var toast = document.getElementById('admin-toast');
            toast.textContent = msg;
            toast.style.display = 'block';
            setTimeout(function(){ toast.style.opacity = 1; }, 10);
            setTimeout(function(){ toast.style.transition = 'opacity .4s'; toast.style.opacity = 0; setTimeout(function(){ toast.style.display='none'; }, 400); }, 3000);
        })();

        // simple AJAX newsletter (progressive enhancement)
        document.getElementById('newsletterForm')?.addEventListener('submit', function(e){
            e.preventDefault();
            var fd = new FormData(this);
            fetch(this.action, { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(d){ alert(d.message || 'Subscribed'); }).catch(function(){ alert('Could not subscribe'); });
        });
    </script>

</body>
</html>
