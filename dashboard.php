<?php
session_start();
require 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$user_type = $_SESSION['user_type'];


$notification_unread_count = 0;
if ($user_type === 'patient') {
    require_once __DIR__ . '/includes/notification-functions.php';
    $notification_unread_count = getUnreadNotificationCount($user_id);
}


$doctors = [];
if ($user_type === 'patient') {
    $dq = "SELECT u.id AS doctor_id, u.fullname, d.specialization
           FROM users u
           LEFT JOIN doctors d ON d.user_id = u.id
           WHERE u.user_type = 'doctor' AND u.status = 'active'
           ORDER BY u.fullname";
    $res = $conn->query($dq);
    while ($r = $res->fetch_assoc())
        $doctors[] = $r;
}



$appointments = [];
$db_error = null;

if ($user_type === 'patient') {
   $stmt = $conn->prepare("
    SELECT 
        a.id,
        a.appointment_date,
        a.appointment_end_time,
        a.symptoms,
        a.status,
        a.payment_status,
        u.fullname AS doctor_name,
        r.id AS report_id
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    LEFT JOIN patient_reports r ON r.appointment_id = a.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC
");

    if (!$stmt) {
        $db_error = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} elseif ($user_type === 'doctor') {
   $stmt = $conn->prepare("
    SELECT 
        a.id,
        a.appointment_date,
        a.appointment_end_time,
        a.symptoms,
        a.status,
        u.fullname AS patient_name,
        r.id AS report_id
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    LEFT JOIN patient_reports r ON r.appointment_id = a.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC
");

    if (!$stmt) {
        $db_error = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard | E-Medicals UG</title>
    <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
     :root {
    --primary: #004080;
    --primary-light: #0066cc;
    --bg: #f5f6fa;
    --card: #fff;
    --muted: #666;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
}

body {
    background: var(--bg);
    color: #111;
    line-height: 1.6;
}


.sidebar {
    width: 240px;
    background: var(--primary);
    color: #fff;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 10px;
}

.sidebar h2 {
    font-size: 1.4rem;
    margin-bottom: 25px;
}

.sidebar a {
    color: #fff;
    text-decoration: none;
    display: block;
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: background 0.3s;
}

.sidebar a:hover {
    background: var(--primary-light);
}

.nav-badge {
    display: inline-block;
    background: var(--danger);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    min-width: 20px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    border-radius: 50%;
    margin-left: 6px;
}

/* Main content */
    .main {
    margin-left: 260px;
    padding: 20px;
    transition: margin-left 0.3s;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.header h1 {
    font-size: 1.8rem;
    margin-bottom: 4px;
}

.header .small {
    color: var(--muted);
}

/* Stats cards */
    .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.stat-card {
    background: var(--card);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    text-align: center;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-card h3 {
    font-size: 1rem;
    color: var(--muted);
    margin-bottom: 8px;
}

.stat-card p {
    font-size: 1.4rem;
    font-weight: 600;
}

/* Menu cards */
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.menu-card {
    background: var(--card);
    padding: 22px;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.menu-card div {
    font-size: 28px;
    margin-bottom: 12px;
}

.menu-card strong {
    display: block;
    margin-bottom: 6px;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    background: var(--card);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

thead {
    background: var(--primary);
    color: #fff;
}

thead th {
    padding: 14px 16px;
    text-align: left;
    vertical-align: middle;
    white-space: nowrap;
    font-weight: 600;
    font-size: 14px;
}

tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #eee;
    text-align:left;
    vertical-align: middle;
    font-size: 14px;
}

tbody tr:hover {
    background: #f9faff;
}
tbody td:first-child {
    border-radius: 0 0 0 12px;
}
tbody td:last-child {
    border-radius: 0 0 12px 0;
}

/* Status badges */
.status {
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-transform: capitalize;
}

.status.scheduled { background: #2196f3; color: #fff; }
.status.completed { background: #4caf50; color: #fff; }
.status.cancelled { background: #f44336; color: #fff; }
.status.no-show { background: #ff9800; color: #fff; }
.status.pending { background: #007bff; color: #fff; }
.status.confirmed { background: #28a745; color: #fff; }
.status.rescheduled { background: #ffc107; color: #000; }

/* Buttons */
.btn {
    display: inline-block;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    opacity: 0.9;
}

.btn.secondary {
    background: #6b7280;
    color: #fff;
}

.btn-primary {
    background: var(--primary);
    color: #fff;
}

/* Forms */
.form-group {
    margin-bottom: 16px;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e6eefb;
    border-radius: 8px;
    font-size: 14px;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    align-items: center;
    justify-content: center;
    z-index: 1200;
    padding: 20px;
    overflow-y: auto;
}

.modal-content {
    width: 100%;
    max-width: 900px;
    background: var(--card);
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.modal-content:hover {
    transform: translateY(-2px);
}

.modal-left {
    flex: 1;
    padding: 32px;
    background: linear-gradient(180deg, var(--primary), #003166);
    color: #fff;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
}

.modal-left h2 {
    font-size: 1.6rem;
    margin-bottom: 16px;
}

.modal-left p {
    font-size: 0.95rem;
    line-height: 1.5;
}

.modal-right {
    flex: 1.2;
    padding: 32px;
    max-height: 80vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
/* Form elements inside modal */
.modal-right input, 
.modal-right select, 
.modal-right textarea {
    width: 100%;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #e6eefb;
    font-size: 14px;
    margin-bottom: 14px;
}

.modal-right label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

/* Modal buttons */
.modal-right .btn {
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}

.modal-right .btn:hover {
    transform: translateY(-2px);
}

/* Mobile responsiveness */
@media (max-width: 900px) {
    .modal-content { flex-direction: column; max-width: 95%; }
    .modal-left, .modal-right { padding: 24px; }
}

@media(max-width:768px) {
    .modal-left { min-width: 100%; }
    .modal-right { max-height: none; }
}
    </style>
</head>

<body>

    <!-- Hamburger Menu Button for Mobile -->
    <button class="hamburger-menu" title="Toggle Menu">☰</button>

    <div class="sidebar">
        <div>
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="includes/images/logo 22.jpg" alt="E-Medicals Logo"
                    style="height: 40px; width: auto; border-radius: 10px; object-fit: contain;">
            </div>
            <h2>E-Medicals UG</h2>
            <a href="dashboard.php">Dashboard</a>
            <?php if ($user_type === 'patient'): ?>
                <a href="#appointments">My Appointments</a>
                <a href="#" onclick="openModal('appointmentModal');return false">Book Appointment</a>
            <?php elseif ($user_type === 'doctor'): ?>
                <a href="manage_appointments.php">Manage Appointments</a>
            <?php endif; ?>
            <?php if ($user_type === 'patient'): ?>
                <a href="pharmacy.php">Pharmacy</a>
            <?php endif; ?>

            <?php if ($user_type === 'patient'): ?>
                <a href="#"
                    onclick="openNotificationsModal(); return false;">Notifications<?= ($notification_unread_count > 0) ? ' <span class="nav-badge">' . (int) $notification_unread_count . '</span>' : '' ?></a>
            <?php elseif ($user_type === 'doctor'): ?>
                <a href="#" onclick="openModal('availabilityModal'); return false;">Set Availability</a>
            <?php endif; ?>
        </div>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1>Welcome, <?= htmlspecialchars($fullname); ?></h1>
                <p class="small"><?= ucfirst($user_type); ?></p>
            </div>
            <div class="small"><?= date("D, d M Y H:i"); ?></div>
        </div>

        <?php if ($db_error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 14px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <strong>Database Error:</strong> <?= htmlspecialchars($db_error); ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <?php if ($user_type === 'patient'): ?>
                <div class="stat-card">
                    <h3 class="small">Upcoming</h3>
                    <p><?= count(array_filter($appointments, fn($a) => $a['status'] === 'scheduled')); ?></p>
                </div>
                <div class="stat-card">
                    <h3 class="small">Completed</h3>
                    <p><?= count(array_filter($appointments, fn($a) => $a['status'] === 'completed')); ?></p>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <h3 class="small">Total Appointments</h3>
                    <p><?= count($appointments); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Menu grid: only show for doctors to avoid extra whitespace -->
        

<!-- Appointments Section -->
<div id="appointments">
    <h2><?= $user_type === 'patient' ? 'My Appointments' : 'Appointments' ?></h2>

    <table class="appointments-table">
        <thead>
            <tr>
                <th>
                    <?= $user_type === 'patient' ? 'Doctor' : 'Patient' ?>
                </th>
                <th>Date & Time</th>
                <th>Symptoms</th>
                <th>Status</th>
                <th>Report</th>
                <th>Payment</th>

                

                <?php if ($user_type === 'doctor'): ?>
                    <th>Update Status</th>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
        <?php if (!empty($appointments)): ?>
            <?php foreach ($appointments as $a): ?>
                <tr>

                    <!-- Doctor / Patient Name -->
                    <td>
                        <?= htmlspecialchars(
                            $user_type === 'patient'
                                ? $a['doctor_name']
                                : $a['patient_name']
                        ); ?>
                    </td>

                    <!-- Date & Time -->
                    <td>
                        <?= date('d M Y', strtotime($a['appointment_date'])); ?><br>
                        <?= date('H:i', strtotime($a['appointment_date'])); ?>
                    </td>

                    <!-- Symptoms -->
                    <td>
                        <?= htmlspecialchars($a['symptoms']); ?>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="status <?= htmlspecialchars($a['status']); ?>">
                            <?= ucfirst($a['status']); ?>
                        </span>
                    </td>

                    <!-- Report Column (for both doctor & patient) -->
                    <td>
                        <?php if (!empty($a['report_id'])): ?>
                            <a href="view_report.php?id=<?= (int)$a['report_id']; ?>" class="btn btn-secondary">
                                View
                            </a>
                        <?php else: ?>
                            <span class="no-report">No Report</span>
                        <?php endif; ?>
                    </td>
                    <td>
<?php if (($a['payment_status'] ?? '') === 'paid'): ?>
    <span class="status confirmed">Paid</span>
<?php else: ?>
    <span class="status pending">Unpaid</span>
<?php endif; ?>

</td>


                    <?php if ($user_type === 'doctor'): ?>

                        <!-- Update Status -->
                        <td>
                            <form method="POST" action="manage_appointment.php">
                                <input type="hidden" name="appointment_id" value="<?= (int)$a['id']; ?>">

                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?= $a['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?= $a['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="rescheduled" <?= $a['status'] === 'rescheduled' ? 'selected' : ''; ?>>Rescheduled</option>
                                    <option value="completed" <?= $a['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?= $a['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="no-show" <?= $a['status'] === 'no-show' ? 'selected' : ''; ?>>No-show</option>
                                </select>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td>
                            <?php if ($a['status'] === 'pending'): ?>
                                <button type="button"
                                        class="btn btn-primary"
                                        onclick="openResponseModal(
                                            <?= (int)$a['id']; ?>,
                                            <?= json_encode($a['patient_name']); ?>,
                                            <?= json_encode(date('Y-m-d', strtotime($a['appointment_date']))); ?>,
                                            <?= json_encode(date('H:i', strtotime($a['appointment_date']))); ?>
                                        )">
                                    Respond
                                </button>
                            <?php else: ?>
                                <span class="status-label">
                                    <?= ucfirst($a['status']); ?>
                                </span>
                            <?php endif; ?>
                        </td>

                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= $user_type === 'doctor' ? '7' : '5'; ?>">
                    No appointments found.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


    
    <div class="modal" id="appointmentModal">
        <div class="modal-content">
            <div class="modal-left">
                <h2 style="margin-top:0">Book Appointment</h2>
                <p class="small">Choose doctor, date and pick an available 20 minute timeslot.</p>
            </div>
            <div class="modal-right">
                <button onclick="closeModal('appointmentModal')"
                    style="float:right;border:0;background:transparent;font-size:20px;cursor:pointer">×</button>
                <form id="bookForm" method="POST" action="book_appointment.php">
                    <div class="form-group">
                        <label for="doctor_select">Doctor</label>
                        <select id="doctor_select" name="doctor_id" required>
                            <option value="">Choose doctor</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['doctor_id']; ?>">Dr. <?= htmlspecialchars($d['fullname']); ?>
                                    (<?= htmlspecialchars($d['specialization'] ?: 'General'); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="slot_date">Date</label>
                        <input id="slot_date" type="date" name="slot_date" required onchange="fetchSlots()">
                    </div>

                    <div class="form-group">
                        <label for="slot_time">Available time slots</label>
                        <select id="slot_time" name="slot_time" required>
                            <option value="">Select a date and doctor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="session_type">Appointment Method</label>
                        <select id="session_type" name="session_type" required>
                            <!-- <option value="Physical">Physical visit</option> -->
                            <option value="WhatsApp">WhatsApp video</option>
                            <option value="Zoom">Zoom meeting/ Google</option>
                            <option value="Meet">Google Meet</option>
                        </select>
                    </div>

                <div class="form-group">
    <label>Payment Method</label>
    <select name="payment_method" required>
        <option value="mobilemoney">Mobile Money Deposit</option>
        <option value="card">Card Payment</option>
    </select>
</div>
   

                    <div class="form-group">
                        <label for="symptoms">Symptoms / Notes</label>
                        <textarea id="symptoms" name="symptoms" rows="3" required></textarea>
                    </div>

                    <div style="display:flex;gap:8px;align-items:center">
                        <button type="submit" class="btn btn-primary">Book & Pay</button>
                        <button type="button" class="btn secondary"
                            onclick="closeModal('appointmentModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== Parmacry Model) ===================== -->
    <!-- <div class="modal" id="pharmacyModal">
        <div class="modal-content">
            <div class="modal-left">
                <h2>Hospital Pharmacy</h2>
                <p class="small">Select medicines and place your order.</p>
            </div>
            <div class="modal-right">
                <button onclick="closeModal('pharmacyModal')"
                    style="float:right;border:0;background:none;font-size:20px">×</button>

                <table>
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($user_type === 'patient'): ?>
                            <div class="menu-card" onclick="window.location.href='pharmacy_module.php'">
                                <div style="font-size:28px"></div>
                                <strong>Pharmacy</strong>
                                <p class="small">Purchase prescribed drugs</p>
                            </div>
                        <?php endif; ?>

                    </tbody>
                </table>

                <hr style="margin:14px 0">
                <button class="btn" onclick="checkout()">Checkout</button>
            </div>
        </div>
    </div> -->





    <!-- ===================== Availability Modal (Doctor) ===================== -->
    <div class="modal" id="availabilityModal">
        <div class="modal-content">
            <div class="modal-left">
                <h2 style="margin-top:0">Set Availability</h2>
                <p class="small">Add dates and start/end times. Each day will provide 20-minute slots.</p>
            </div>
            <div class="modal-right">
                <button onclick="closeModal('availabilityModal')"
                    style="float:right;border:0;background:transparent;font-size:20px;cursor:pointer">×</button>
                <form method="POST" action="set_availability.php">
                    <div class="form-group">
                        <label for="available_date">Date</label>
                        <input type="date" name="available_date" required>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <button type="submit" class="btn">Save</button>
                        <button type="button" class="btn secondary"
                            onclick="closeModal('availabilityModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['toast_message'])): ?>
        <div class="toast <?= $_SESSION['toast_type'] === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($_SESSION['toast_message']); ?>
        </div>
        <script>setTimeout(() => { document.querySelector('.toast').style.display = 'none' }, 4000);</script>
        <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
    <?php endif; ?>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        // fetch available slots via AJAX
        async function fetchSlots() {
            const doctorId = document.getElementById('doctor_select').value;
            const date = document.getElementById('slot_date').value;
            const timeSelect = document.getElementById('slot_time');
            timeSelect.innerHTML = '<option>Loading...</option>';
            if (!doctorId || !date) { timeSelect.innerHTML = '<option value="">Select date & doctor</option>'; return; }

            try {
                const res = await fetch(`get_slots.php?doctor_id=${encodeURIComponent(doctorId)}&date=${encodeURIComponent(date)}`);
                const data = await res.json();
                timeSelect.innerHTML = '';
                if (data.length === 0) {
                    timeSelect.innerHTML = '<option value="">No available slots</option>';
                } else {
                    timeSelect.innerHTML = '<option value="">-- Choose slot --</option>';
                    data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s; // format "HH:MM"
                        opt.textContent = s;
                        timeSelect.appendChild(opt);
                    });
                }
            } catch (err) {
                timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                console.error(err);
            }
        }
    </script>

    <!-- Response Modal -->
    <div id="responseModal" class="modal" aria-hidden="true">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Respond to Appointment</h2>
                <button class="close-btn" onclick="closeResponseModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Patient Info Display -->
                <div class="patient-info" id="patientInfo">
                    <p><strong>Patient:</strong> <span id="modalPatientName"></span></p>
                    <p><strong>Requested Date:</strong> <span id="modalAppointmentDate"></span></p>
                    <p><strong>Requested Time:</strong> <span id="modalAppointmentTime"></span></p>
                </div>

                <form id="responseForm">
                    <input type="hidden" id="responseAppointmentId" name="appointment_id">
                    <input type="hidden" id="responseType" name="response_type">

                    <!-- Response Type Selection -->
                    <div class="form-group">
                        <label>Response Type:</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="confirm"
                                    onchange="toggleResponseFields()" required>
                                <span>Confirm Appointment</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="reschedule"
                                    onchange="toggleResponseFields()">
                                <span>Reschedule to another time</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="cancel"
                                    onchange="toggleResponseFields()">
                                <span>Cancel Appointment</span>
                            </label>
                        </div>
                    </div>

                    <!-- Confirm: doctor re-writes confirmed time + Zoom link or call statement -->
                    <div id="confirmFields" class="response-fields" style="display: none;">
                        <div class="form-group">
                            <label for="confirmedDate">Confirmed Date *</label>
                            <input type="date" id="confirmedDate" name="confirmed_date" min="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="confirmedTime">Confirmed Time *</label>
                            <input type="time" id="confirmedTime" name="confirmed_time">
                        </div>
                        <div class="form-group">
                            <label for="meetingType">How will you meet? *</label>
                            <select id="meetingType" name="meeting_type" onchange="updateMeetingPlaceholder()">
                                <option value="">-- Select --</option>
                                <option value="zoom">Zoom/ Google Meet; Attach Meeting Link.</option>
                                <option value="whatsapp">WhatsApp Call </option>
                                <option value="phone">Phone call</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="meetingLink" id="meetingLinkLabel">Meeting link or contact details *</label>
                            <input type="text" id="meetingLink" name="meeting_link"
                                placeholder="Paste Meeting link, or e.g. I will call you on +256 700 000 000">
                            <small class="help-text" id="meetingHelp"></small>
                        </div>
                    </div>

                    <!-- Reschedule: reason only → notification + email -->
                    <div id="rescheduleFields" class="response-fields" style="display: none;">
                        <div class="form-group">
                            <label for="rescheduleReason">Reason for rescheduling *</label>
                            <textarea id="rescheduleReason" name="reschedule_reason" rows="4"
                                placeholder="Attach Reason for Rescheduling."></textarea>
                        </div>
                    </div>

                    <!-- Cancel: reason only → notification + email -->
                    <div id="cancelFields" class="response-fields" style="display: none;">
                        <div class="form-group">
                            <label for="cancelReason">Reason for cancellation *</label>
                            <textarea id="cancelReason" name="cancel_reason" rows="4"
                                placeholder="Attach Reason for Cancellation."></textarea>
                        </div>
                    </div>

                    <!-- Error/Success Message -->
                    <div id="responseMessage" style="display: none; margin: 15px 0;"></div>

                    <!-- Action Buttons -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeResponseModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitResponseBtn">
                            <span id="submitBtnText">Submit Response</span>
                            <span id="submitBtnLoader" style="display: none;">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Response Modal - must show on top when opened */
        #responseModal {
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
            color: #003366;
            font-size: 22px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }

        .close-btn:hover {
            color: #000;
        }

        .patient-info {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .patient-info p {
            margin: 5px 0;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .radio-label {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .radio-label:hover {
            background: #f8f9fa;
            border-color: #007bff;
        }

        .radio-label input[type="radio"] {
            margin-right: 10px;
        }

        .radio-label input[type="radio"]:checked+span {
            font-weight: 600;
            color: #007bff;
        }

        .response-fields {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .help-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .success-msg {
            color: #28a745;
            font-weight: 600;
            padding: 10px;
            background: #d4edda;
            border-radius: 5px;
        }

        .error-msg {
            color: #dc3545;
            font-weight: 600;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
        }
    </style>

    <script>
        // Response Modal JavaScript
        let currentAppointmentId = null;

        function openResponseModal(appointmentId, patientName, appointmentDate, appointmentTime) {
            currentAppointmentId = appointmentId;

            document.getElementById('modalPatientName').textContent = patientName;
            document.getElementById('modalAppointmentDate').textContent = formatDate(appointmentDate);
            document.getElementById('modalAppointmentTime').textContent = formatTime(appointmentTime);

            document.getElementById('responseAppointmentId').value = appointmentId;

            document.getElementById('responseForm').reset();
            document.getElementById('responseMessage').style.display = 'none';
            document.getElementById('responseMessage').className = '';

            // Prefill confirmed date/time with requested slot so doctor can edit
            var cd = document.getElementById('confirmedDate');
            var ct = document.getElementById('confirmedTime');
            if (cd) cd.value = appointmentDate || '';
            if (ct) ct.value = appointmentTime || '';

            document.getElementById('confirmFields').style.display = 'none';
            document.getElementById('rescheduleFields').style.display = 'none';
            document.getElementById('cancelFields').style.display = 'none';

            var modal = document.getElementById('responseModal');
            if (modal) {
                modal.classList.add('active');
                modal.style.setProperty('display', 'flex', 'important');
            }
        }

        function closeResponseModal() {
            var modal = document.getElementById('responseModal');
            if (modal) { modal.classList.remove('active'); modal.style.display = 'none'; modal.style.removeProperty('display'); }
            var f = document.getElementById('responseForm');
            if (f) f.reset();
            document.getElementById('responseMessage').style.display = 'none';
            currentAppointmentId = null;
        }

        function toggleResponseFields() {
            const selected = document.querySelector('input[name="response_type_radio"]:checked');
            if (!selected) return;

            const responseType = selected.value;
            document.getElementById('responseType').value = responseType;

            // Hide all fields
            document.getElementById('confirmFields').style.display = 'none';
            document.getElementById('rescheduleFields').style.display = 'none';
            document.getElementById('cancelFields').style.display = 'none';

            // Show relevant fields
            if (responseType === 'confirm') {
                document.getElementById('confirmFields').style.display = 'block';
            } else if (responseType === 'reschedule') {
                document.getElementById('rescheduleFields').style.display = 'block';
            } else if (responseType === 'cancel') {
                document.getElementById('cancelFields').style.display = 'block';
            }
        }

        function updateMeetingPlaceholder() {
            const type = document.getElementById('meetingType').value;
            const input = document.getElementById('meetingLink');
            const help = document.getElementById('meetingHelp');

            if (type === 'zoom') {
                input.placeholder = 'Paste meeting link (e.g. https://zoom.us/j/123456789)';
                help.textContent = 'Paste the link you generate from Zoom.';
            } else if (type === 'whatsapp' || type === 'phone') {
                input.placeholder = 'e.g. I will call you on +256 700 000 000';
                help.textContent = 'Write how you will contact the patient.';
            } else {
                input.placeholder = 'Paste meeting link, or e.g. I will call you on +256 700 000 000';
                help.textContent = '';
            }
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        function formatTime(timeStr) {
            // Convert 24h to 12h format
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

        // Form submission
        document.getElementById('responseForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('responseMessage');
            const submitBtn = document.getElementById('submitResponseBtn');
            const submitText = document.getElementById('submitBtnText');
            const submitLoader = document.getElementById('submitBtnLoader');

            const responseType = formData.get('response_type');
            if (!responseType) {
                showMessage('Please select a response type.', 'error');
                return;
            }
            if (responseType === 'reschedule' && !formData.get('reschedule_reason')?.trim()) {
                showMessage('Please enter the reason for rescheduling.', 'error');
                return;
            }
            if (responseType === 'cancel' && !formData.get('cancel_reason')?.trim()) {
                showMessage('Please enter the reason for cancellation.', 'error');
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';

            // Send AJAX request
            fetch('doctor/respond-appointment.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            location.reload(); // Reload page to show updated status
                        }, 1500);
                    } else {
                        showMessage(data.message || 'An error occurred. Please try again.', 'error');
                        submitBtn.disabled = false;
                        submitText.style.display = 'inline';
                        submitLoader.style.display = 'none';
                    }
                })
                .catch(error => {
                    showMessage('Network error. Please check your connection and try again.', 'error');
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                });
        });

        function showMessage(message, type) {
            const messageDiv = document.getElementById('responseMessage');
            messageDiv.textContent = message;
            messageDiv.className = type === 'success' ? 'success-msg' : 'error-msg';
            messageDiv.style.display = 'block';

            // Scroll to message
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            const modal = document.getElementById('responseModal');
            if (event.target === modal) {
                closeResponseModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeResponseModal();
                closeNotificationsModal();
            }
        });
    </script>

    <!-- Notifications Modal (doctor response actions) -->
    <div id="notificationsModal" class="modal" style="display: none;">
        <div class="modal-content notifications-modal-content" style="max-width: 520px;">
            <div class="modal-header">
                <h2>Notifications</h2>
                <button class="close-btn" onclick="closeNotificationsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="notificationsModalToolbar"
                    style="display: none; margin-bottom: 12px; justify-content: space-between; align-items: center;">
                    <span id="notificationsUnreadLabel" class="small"></span>
                    <button type="button" class="btn btn-secondary btn-sm" id="markAllNotificationsReadBtn"
                        onclick="markAllNotificationsRead()">Mark all as read</button>
                </div>
                <div id="notificationsListContainer">
                    <div id="notificationsLoading" style="text-align: center; padding: 24px; color: #666;">Loading...
                    </div>
                    <div id="notificationsList" style="display: none;"></div>
                    <div id="notificationsEmpty" style="display: none; text-align: center; padding: 32px; color: #666;">
                        <p style="font-size: 48px; margin-bottom: 8px;">🔔</p>
                        <p>No notifications yet.</p>
                        <p class="small">When a doctor confirms, reschedules, or cancels your appointment, You will be notified here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .notifications-modal-content .notification-item {
            padding: 14px 16px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #ddd;
            background: #f8f9fa;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .notifications-modal-content .notification-item:hover {
            background: #e9ecef;
        }

        .notifications-modal-content .notification-item.unread {
            background: #e7f3ff;
            border-left-color: #007bff;
        }

        .notifications-modal-content .notification-item.confirmed {
            border-left-color: #28a745;
        }

        .notifications-modal-content .notification-item.rescheduled {
            border-left-color: #ffc107;
        }

        .notifications-modal-content .notification-item.cancelled {
            border-left-color: #dc3545;
        }

        .notifications-modal-content .notification-type {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .notifications-modal-content .notification-type.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .notifications-modal-content .notification-type.rescheduled {
            background: #fff3cd;
            color: #856404;
        }

        .notifications-modal-content .notification-type.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .notifications-modal-content .notification-time {
            color: #666;
            font-size: 11px;
        }

        .notifications-modal-content .notification-message {
            color: #333;
            white-space: pre-wrap;
            line-height: 1.5;
            font-size: 14px;
            margin-top: 6px;
        }

        .notifications-modal-content .notification-message a {
            color: #007bff;
            word-break: break-all;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        #notificationsList {
            max-height: 60vh;
            overflow-y: auto;
        }
    </style>
    <script>
        function openNotificationsModal() {
            var modal = document.getElementById('notificationsModal');
            modal.classList.add('active');
            modal.style.display = 'flex';
            loadNotificationsIntoModal();
        }
        function closeNotificationsModal() {
            var modal = document.getElementById('notificationsModal');
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
        function loadNotificationsIntoModal() {
            var loading = document.getElementById('notificationsLoading');
            var listEl = document.getElementById('notificationsList');
            var emptyEl = document.getElementById('notificationsEmpty');
            var toolbar = document.getElementById('notificationsModalToolbar');
            loading.style.display = 'block';
            listEl.style.display = 'none';
            emptyEl.style.display = 'none';
            toolbar.style.display = 'none';

            fetch('get_notifications.php')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    loading.style.display = 'none';
                    var notifications = data.notifications || [];
                    var unreadCount = data.unread_count || 0;

                    if (data.unread_count > 0) {
                        toolbar.style.display = 'flex';
                        document.getElementById('notificationsUnreadLabel').textContent = unreadCount + ' unread';
                    }

                    if (notifications.length === 0) {
                        emptyEl.style.display = 'block';
                        return;
                    }
                    listEl.style.display = 'block';
                    listEl.innerHTML = notifications.map(function (n) {
                        var typeLabel = n.notification_type === 'confirmed' ? 'Confirmed' : (n.notification_type === 'rescheduled' ? 'Rescheduled' : 'Cancelled');
                        var raw = (n.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        var msg = linkifyNotificationMessage(raw);
                        var timeAgo = timeAgoStr(n.created_at);
                        var unreadClass = (n.is_read == 0) ? ' unread' : '';
                        return '<div class="notification-item ' + n.notification_type + unreadClass + '" data-id="' + n.notification_id + '" data-read="' + n.is_read + '" onclick="notificationItemClick(this)">' +
                            '<span class="notification-type ' + n.notification_type + '">' + typeLabel + '</span> ' +
                            '<span class="notification-time">' + timeAgo + '</span>' +
                            '<div class="notification-message">' + msg + '</div></div>';
                    }).join('');
                })
                .catch(function () {
                    loading.style.display = 'none';
                    emptyEl.innerHTML = '<p>Could not load notifications.</p>';
                    emptyEl.style.display = 'block';
                });
        }
        function linkifyNotificationMessage(text) {
            return text
                .replace(/\n/g, '<br>')
                .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>')
                .replace(/\+(256[0-9]{9})/g, '<a href="https://wa.me/$1" target="_blank" rel="noopener">+$1</a>');
        }
        function timeAgoStr(timestamp) {
            if (!timestamp) return '';
            var d = new Date(timestamp);
            var sec = Math.floor((Date.now() - d.getTime()) / 1000);
            if (sec < 60) return 'Just now';
            if (sec < 3600) return Math.floor(sec / 60) + 'm ago';
            if (sec < 86400) return Math.floor(sec / 3600) + 'h ago';
            if (sec < 2592000) return Math.floor(sec / 86400) + 'd ago';
            if (sec < 31536000) return Math.floor(sec / 2592000) + 'mo ago';
            return Math.floor(sec / 31536000) + 'y ago';
        }
        function notificationItemClick(el) {
            var id = el.getAttribute('data-id');
            var read = el.getAttribute('data-read');
            if (read === '1') return;
            var fd = new FormData();
            fd.append('action', 'mark_read');
            fd.append('notification_id', id);
            fetch('get_notifications.php', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        el.classList.remove('unread');
                        el.setAttribute('data-read', '1');
                        el.style.background = '#f8f9fa';
                        el.style.borderLeftColor = '#ddd';
                        updateNavBadge();
                    }
                });
        }
        function markAllNotificationsRead() {
            var fd = new FormData();
            fd.append('action', 'mark_all_read');
            fetch('get_notifications.php', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        loadNotificationsIntoModal();
                        updateNavBadge();
                    }
                });
        }
        function updateNavBadge() {
            fetch('get_notifications.php').then(function (r) { return r.json(); }).then(function (data) {
                var count = data.unread_count || 0;
                var link = document.querySelector('a[onclick*="openNotificationsModal"]');
                if (!link) return;
                var badge = link.querySelector('.nav-badge');
                if (count > 0) {
                    if (!badge) { badge = document.createElement('span'); badge.className = 'nav-badge'; link.appendChild(badge); }
                    badge.textContent = count;
                } else if (badge) { badge.remove(); }
            });
        }
        window.addEventListener('click', function (e) {
            if (e.target.id === 'notificationsModal') closeNotificationsModal();
        });

        // Mobile hamburger menu
        document.addEventListener('DOMContentLoaded', function () {
            const hamburger = document.querySelector('.hamburger-menu');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            // Create overlay if it doesn't exist
            if (!overlay) {
                const newOverlay = document.createElement('div');
                newOverlay.className = 'sidebar-overlay';
                document.body.appendChild(newOverlay);
            }

            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                document.querySelector('.sidebar-overlay').classList.toggle('active');
            }

            // Hamburger click
            if (hamburger) {
                hamburger.addEventListener('click', toggleSidebar);
            }

            // Overlay click to close
            const newOverlay = document.querySelector('.sidebar-overlay');
            if (newOverlay) {
                newOverlay.addEventListener('click', function () {
                    sidebar.classList.remove('active');
                    this.classList.remove('active');
                });
            }

            // Close sidebar when clicking on a link
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                        newOverlay.classList.remove('active');
                    }
                });
            });
        });

    </script>

</body>

</html>