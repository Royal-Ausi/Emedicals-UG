<?php
// pharmacy.php
session_start();
require 'db.php'; // $conn = new mysqli(...)
$logged_in = isset($_SESSION['user_id']);

$search = $_GET['q'] ?? '';
$sql = "SELECT * FROM medicines WHERE status='active'";
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $sql .= " AND name LIKE '%$safe%'";
}
$sql .= " ORDER BY name ASC";
$meds = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Medicals UG | Online Pharmacy</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
        :root {
            --navy: #004080;
            --navy-700: #003166;
            --accent-blue: #0055aa;
            --muted: #4a5568;
            --bg: #f5f8fc;
            --card: #fff;
            --radius: 12px;
            --glass: rgba(255, 255, 255, 0.85);
        }

        * {
            box-sizing: border-box;
            font-family: "Inter", Arial, sans-serif;
        }

        body {
            background: var(--bg);
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.45;
        }

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Navigation */
        .top-nav {
            background-color: var(--navy-700);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .top-nav .logo {
            background: #ffffff;
            padding: 12px 14px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .top-nav .logo img {
            height: 20px;
            width: auto;
            max-height: 48px;
            display: block;
            object-fit: contain;
        }

        .top-nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        .top-nav a {
            color: white;
            text-decoration: none;
            transition: opacity .28s ease;
        }

        .top-nav a:hover {
            opacity: 0.8;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
            border: 0;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--navy);
            color: #fff;
        }

        .btn-primary:hover {
            background: #003d7a;
            color: #fff;
        }

        /* Pharmacy Content */
        .catalog-card,
        .checkout-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: 0 6px 18px rgba(6, 30, 78, 0.06);
            border: none;
        }

        h5, h6 {
            color: var(--navy);
            font-weight: 700;
        }

        .product-item {
            border-bottom: 1px solid #eef3fb;
            padding: 14px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-name {
            font-weight: 600;
            color: var(--navy);
        }

        .product-desc {
            font-size: .92rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .product-price {
            font-weight: 600;
            color: var(--accent-green);
            margin-top: 6px;
        }

        .search-input {
            border-radius: 20px;
            border: 1px solid #e6eefb;
            padding: 10px 16px;
        }

        .btn-accent {
            background: var(--accent-blue);
            color: #fff;
            border: none;
        }

        .btn-accent:hover {
            background: #003d7a;
            color: #fff;
        }

        .step-indicator span {
            font-size: .9rem;
            margin-right: 12px;
        }

        .step-active {
            font-weight: 700;
            color: var(--accent-blue);
        }

        .step-muted {
            color: var(--muted);
        }

        .form-label {
            color: var(--navy);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1px solid #e6eefb;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 85, 170, 0.1);
        }

        .cart-link {
            color: white;
            text-decoration: none;
            transition: opacity .28s ease;
        }

        .cart-link:hover {
            opacity: 0.8;
        }

        .badge-cart {
            background: var(--accent-blue);
            color: white;
        }

        /* Footer */
        footer.site-footer {
            background: var(--navy);
            color: #fff;
            padding: 20px 0;
            margin-top: 36px;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        footer.site-footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            transition: opacity .28s ease;
        }

        footer.site-footer a:hover {
            opacity: 0.8;
        }

        /* MOBILE RESPONSIVENESS */
        @media (max-width: 992px) {
            .top-nav {
                padding: 0.8rem 1.2rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .top-nav ul {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .container {
                width: 95%;
                padding: 16px;
            }

            .row {
                flex-direction: column;
            }

            .col-lg-7, .col-lg-5 {
                flex-basis: 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                padding: 0.6rem 1rem;
                gap: 0.8rem;
            }

            .top-nav .logo img {
                height: 16px;
                max-height: 32px;
            }

            .top-nav ul {
                gap: 0.8rem;
                font-size: 0.85rem;
            }

            .top-nav a {
                font-size: 0.85rem;
            }

            .container {
                padding: 12px;
            }

            .search-input {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .catalog-card {
                padding: 16px !important;
            }

            .product-item {
                padding: 12px 0;
            }

            .product-name {
                font-size: 0.95rem;
            }

            .product-desc {
                font-size: 0.85rem;
            }

            .product-price {
                font-size: 0.95rem;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .input-group-sm .form-control,
            .input-group-sm .btn {
                padding: 6px 8px;
                font-size: 0.8rem;
            }

            input[name="qty"] {
                width: 60px !important;
            }

            .alert {
                padding: 10px 12px !important;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .top-nav {
                padding: 0.5rem 0.8rem;
                gap: 0.5rem;
            }

            .top-nav ul {
                gap: 0.5rem;
                font-size: 0.75rem;
            }

            .top-nav a {
                font-size: 0.75rem;
            }

            .container {
                padding: 8px;
            }

            .product-item {
                display: flex;
                flex-direction: column;
                padding: 8px 0;
                gap: 8px;
            }

            .product-item form {
                margin-left: 0 !important;
            }

            .product-name {
                font-size: 0.9rem;
            }

            .product-desc {
                font-size: 0.8rem;
            }

            .search-input {
                padding: 6px 10px;
                font-size: 0.85rem;
            }

            input[type="text"],
            input[type="email"],
            input[type="number"],
            select {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 10px 8px;
            }

            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .step-indicator span {
                font-size: 0.8rem;
                margin-right: 8px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            footer.site-footer {
                padding: 16px;
            }
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-nav {
                flex-wrap: wrap;
                gap: 12px;
            }

            .top-nav ul {
                gap: 1rem;
            }

            .container {
                width: 95%;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<!-- ================= Navigation ================= -->


<div class="container">
    <!-- Search Bar -->
    <div style="margin:24px 0;text-align:center">
        <form method="get" action="pharmacy.php" style="display:flex;gap:12px;max-width:500px;margin:0 auto">
            <input name="q" value="<?= htmlspecialchars($search) ?>" class="form-control search-input" placeholder="Search medicines (e.g., Paracetamol)" style="flex:1">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="catalog-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
                    <h5 style="margin:0">Product Catalog</h5>
                </div>

                <div id="product-list">
                    <?php while($m = $meds->fetch_assoc()): ?>
                        <div class="product-item">
                            <div style="flex:1">
                                <div class="product-name">
                                    <?= htmlspecialchars($m['name']) . ($m['dosage'] ? ' ' . htmlspecialchars($m['dosage']) : '') ?>
                                </div>
                                <div class="product-desc">
                                    <?= $m['tablets_count'] ? intval($m['tablets_count']).' tablets - ' : '' ?>
                                    <?= htmlspecialchars($m['description'] ?? '') ?>
                                </div>
                                <div class="product-price">UGX <?= number_format($m['price_ugx']) ?></div>
                                <?php if ((int)$m['stock'] <= 0): ?>
                                    <span style="color:#dc3545;font-size:0.85rem">Out of stock</span>
                                <?php endif; ?>
                            </div>
                            <form method="post" action="cart_add.php" style="margin-left:12px">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($m['name']) . ($m['dosage'] ? ' ' . htmlspecialchars($m['dosage']) : '') ?>">
                                <input type="hidden" name="price" value="<?= $m['price_ugx'] ?>">
                                <div class="input-group input-group-sm" style="width:120px">
                                    <input type="number" name="qty" class="form-control" min="1" value="1" <?= (int)$m['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <button class="btn btn-accent btn-sm" type="submit" <?= (int)$m['stock'] <= 0 ? 'disabled' : '' ?>>Add</button>
                                </div>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Checkout (Shipping) -->
        <div class="col-lg-5">
            <?php if (!$logged_in): ?>
                <div class="alert alert-warning" style="border-radius:8px;display:flex;align-items:center;gap:12px;padding:14px">
                    <i class="fa fa-info-circle"></i>
                    <div>
                        <strong>Please log in to place an order</strong><br>
                        <button onclick="openAuthModal('login')" class="btn btn-sm btn-primary" style="margin-top:8px;padding:6px 14px"><i class="fa fa-right-to-bracket"></i> Login</button>
                        <button onclick="openAuthModal('register')" class="btn btn-sm btn-outline" style="margin-top:8px;margin-left:6px;padding:6px 14px;background:transparent;color:#856404;border:1px solid #856404"><i class="fa fa-user-plus"></i> Register</button>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="checkout-card">
                <h5 style="margin-bottom:12px">Checkout</h5>

                <div id="cart-preview" style="margin-bottom:14px">
                    <div id="cart-items">Loading cart…</div>
                    <div style="margin-top:12px;font-weight:700">Total: <span id="cart-total">0</span> UGX</div>
                </div>

                <button id="show-checkout-btn" class="btn btn-primary w-100" style="margin-bottom:12px">Proceed to Payment</button>

                <div id="checkoutPanel" style="display:none">
                    <h6 style="margin-bottom:16px">Shipping Details</h6>
                    <form id="shipping-form" method="post" action="process_shipping.php">
                        <div style="margin-bottom:14px">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="" required>
                        </div>
                        <div style="margin-bottom:14px">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="MUBs Nakawa, Old Port Bell Road" required></textarea>
                        </div>
                        <div style="margin-bottom:14px">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="" required>
                        </div>
                        <div style="margin-bottom:14px">
                            <label class="form-label">Payment Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">Cash on Delivery</label>
                            </div>
                            <div class="form-check" style="margin-top:8px">
                                <input class="form-check-input" type="radio" name="method" id="momo" value="momo">
                                <label class="form-check-label" for="momo">Mobile Money / Card</label>
                            </div>
                        </div>
                        
                        <?php if ($logged_in): ?>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-shopping-cart"></i> Confirm & Pay
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary w-100" onclick="openAuthModal('login')">
                                <i class="fa fa-right-to-bracket"></i> Login to Checkout
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= Footer ================= -->
 <?php include 'footer.php'; ?>
<!-- <footer class="site-footer">
    <div class="container" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:20px">
        <div>
            <strong style="font-size:0.95rem">© <?= date('Y'); ?> E-Medicals UG.</strong> Your trusted partner in health.
        </div>
        <div style="display:flex;gap:15px;flex-wrap:wrap;justify-content:center">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="terms.php">Terms</a>
            <a href="privacy.php">Privacy</a>
        </div>
    </div>
</footer> -->

<!-- ================= Auth Modal ================= -->
<div id="authModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;padding:20px;z-index:1200">
    <div style="width:100%;max-width:920px;background:var(--card);border-radius:12px;overflow:hidden;display:flex;flex-wrap:wrap;box-shadow:0 20px 60px rgba(2,6,23,0.4)">

        <!-- Left Welcome Panel -->
        <div style="flex:1;padding:28px;background:linear-gradient(180deg,var(--navy),var(--navy-700));color:#fff;min-width:280px">
            <h2 style="margin-top:0">Welcome to E-Medicals UG</h2>
            <p style="opacity:.95">Login or register to access appointments, reports and more.</p>
            <p style="margin-top:20px;font-size:.95rem;opacity:.95">We'll never share your data without consent.</p>
        </div>

        <!-- Right Panel (Scrollable) -->
        <div style="flex:1;padding:22px;max-height:90vh;overflow-y:auto;min-width:300px;position:relative;">
            <button onclick="closeAuthModal()" style="position:absolute;top:12px;right:12px;border:0;background:transparent;font-size:20px;cursor:pointer;z-index:10">×</button>

            <!-- Login form -->
            <form id="loginForm" action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <h3 style="margin:6px 0 12px;color:var(--navy)">Login</h3>
                <label style="display:block;margin-bottom:8px">Email</label>
                <input name="email" type="email" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:12px">
                <label style="display:block;margin-bottom:8px">Password</label>
                <input name="password" type="password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:14px">
                <div style="display:flex;gap:8px;align-items:center">
                    <button class="btn btn-primary" type="submit">Login</button>
                    <a href="#" onclick="switchAuth('register');return false;" style="color:var(--navy);font-weight:600">Create account</a>
                </div>
            </form>

            <!-- Register form (hidden by default) -->
            <form id="registerForm" action="auth.php" method="POST" style="display:none;margin-top:6px">
                <input type="hidden" name="action" value="register">
                <h3 style="margin:6px 0 12px;color:var(--navy)">Register</h3>

                <label style="display:block;margin-bottom:8px">Full name</label>
                <input name="fullname" type="text" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Email</label>
                <input name="email" type="email" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Phone</label>
                <input name="phone" type="tel" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Account type</label>
                <select id="userType" name="user_type" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                    <option value="">Select</option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                </select>

                <!-- doctor-specific fields -->
                <div id="doctorFields" style="display:none;margin-bottom:10px">
                    <label style="display:block;margin-bottom:8px">Specialization</label>
                    <input name="specialization" type="text" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                    <label style="display:block;margin-bottom:8px">License number</label>
                    <input name="license_number" type="text" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                </div>

                <label style="display:block;margin-bottom:8px">Password</label>
                <input id="regPassword" name="password" type="password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                <label style="display:block;margin-bottom:8px">Confirm password</label>
                <input id="confirmPassword" name="confirm_password" type="password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:12px">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="font-size:.95rem;color:#333">I agree to the <a href="terms.php">Terms</a> & <a href="privacy.php">Privacy</a></label>
                </div>

                <div style="display:flex;gap:8px;align-items:center">
                    <button class="btn btn-primary" type="submit">Register</button>
                    <a href="#" onclick="switchAuth('login');return false;" style="color:var(--navy);font-weight:600">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// AJAX add-to-cart and cart preview
function formatCurrency(n){ return new Intl.NumberFormat('en-UG').format(n); }
function updateCartUI(){
    fetch('cart_status.php')
        .then(r=>r.json())
        .then(data=>{
            var itemsEl = document.getElementById('cart-items');
            var totalEl = document.getElementById('cart-total');
            if (!itemsEl || !totalEl) return;
            var cart = data.cart || [];
            if (!cart.length) { itemsEl.innerHTML = '<p>Your cart is empty.</p>'; totalEl.textContent = '0'; return; }
            var html = '<ul style="list-style:none;padding:0;margin:0;">';
            cart.forEach(function(it){ html += '<li style="padding:8px 0;border-bottom:1px solid #f0f4fb;display:flex;justify-content:space-between;align-items:center">' +
                '<div><strong>' + escapeHtml(it.name) + '</strong><div style="font-size:0.9rem;color:#666">Qty: ' + (it.qty) + '</div></div>' +
                '<div style="font-weight:700">' + formatCurrency(it.price * it.qty) + '</div></li>'; });
            html += '</ul>';
            itemsEl.innerHTML = html;
            totalEl.textContent = formatCurrency(data.total || 0);
        }).catch(()=>{});
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

document.addEventListener('DOMContentLoaded', function(){
    updateCartUI();
    // intercept cart add forms
    document.querySelectorAll('form[action="cart_add.php"]').forEach(function(f){
        f.addEventListener('submit', function(e){
            e.preventDefault();
            var fd = new FormData(f);
            fetch('cart_add.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function(data){ if (data && data.cart){ updateCartUI(); } })
                .catch(function(){ /* ignore */ });
        });
    });

    var showBtn = document.getElementById('show-checkout-btn');
    if (showBtn){ showBtn.addEventListener('click', function(){ document.getElementById('checkoutPanel').style.display = 'block'; showBtn.style.display = 'none'; window.scrollTo({top: document.getElementById('checkoutPanel').offsetTop - 20, behavior: 'smooth'}); }); }
});
</script>
<script>
    /* ================= Auth modal functions ================= */
    function openAuthModal(mode = 'login') {
        var m = document.getElementById('authModal'); if (!m) return;
        m.style.display = 'flex';
        if (mode === 'register') switchAuth('register'); else switchAuth('login');
    }
    function closeAuthModal() { var m = document.getElementById('authModal'); if (m) m.style.display = 'none'; }
    function switchAuth(mode) {
        var lf = document.getElementById('loginForm'); var rf = document.getElementById('registerForm');
        if (lf) lf.style.display = (mode === 'login') ? 'block' : 'none';
        if (rf) rf.style.display = (mode === 'register') ? 'block' : 'none';
    }

    /* show doctor fields on select */
    (function(){
        var userType = document.getElementById('userType');
        var doctorFields = document.getElementById('doctorFields');
        if (userType && doctorFields) {
            userType.addEventListener('change', function(){ doctorFields.style.display = (userType.value === 'doctor') ? 'block' : 'none'; });
        }
    })();

    /* password validation */
    (function(){
        var regPassword = document.getElementById('regPassword');
        var confirmPassword = document.getElementById('confirmPassword');
        if (regPassword && confirmPassword) {
            function validatePassword() {
                if (regPassword.value !== confirmPassword.value) confirmPassword.setCustomValidity("Passwords do not match");
                else confirmPassword.setCustomValidity("");
            }
            regPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        }
    })();

    /* close modal with Escape */
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeAuthModal(); } });
</script>

</body>
</html>
