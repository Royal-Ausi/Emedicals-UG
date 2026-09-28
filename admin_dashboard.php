<?php
session_start();
require 'db.php';

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['fullname'];
$admin_email = $_SESSION['email'] ?? '';

// Fetch Users
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Doctors
$result = $conn->query("
    SELECT u.id, u.fullname, d.id as doctor_id, d.specialization, d.consultation_fee
    FROM users u
    LEFT JOIN doctors d ON d.user_id = u.id
    WHERE u.user_type='doctor'
");
$doctors = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Appointments
$result = $conn->query("
    SELECT a.*, u1.fullname AS patient_name, u2.fullname AS doctor_name
    FROM appointments a
    JOIN users u1 ON a.patient_id=u1.id
    JOIN users u2 ON a.doctor_id=u2.id
    ORDER BY a.appointment_date DESC
");
$appointments = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Availability
$result = $conn->query("
    SELECT da.*, u.fullname
    FROM doctor_availability da
    JOIN users u ON u.id=da.doctor_id
    ORDER BY da.available_date DESC
");
$availability = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Pharmacy Orders 
$result = $conn->query("
    SELECT po.*, u.fullname AS patient_name
    FROM pharmacy_orders po
    JOIN users u ON u.id = po.patient_id
    ORDER BY po.created_at DESC
");
$pharmacy_orders = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Payments check
$payments_exist = false;
$result = $conn->query("SHOW TABLES LIKE 'payments'");
if ($result) {
    $payments_exist = $result->num_rows;
}
$payments = [];
if ($payments_exist) {
    $result = $conn->query("
        SELECT p.*, u.fullname
FROM payments p
LEFT JOIN appointments a ON p.appointment_id = a.id
LEFT JOIN users u ON a.patient_id = u.id
ORDER BY p.created_at DESC

    ");
    if ($result) {
        $payments = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Pending Doctors
$pendingDoctorsQuery = "SELECT d.id AS doctor_id, u.id as user_id, u.fullname, d.specialization, u.created_at
                        FROM doctors d
                        JOIN users u ON d.user_id = u.id
                        WHERE u.status = 'pending'
                        ORDER BY u.created_at DESC";
$pendingResult = $conn->query($pendingDoctorsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard |E-Medicals UG</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background:#f4f7fb; font-family:'Inter',sans-serif; margin:0; }
        .container { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:#003366; color:#fff; padding:20px; overflow-y:auto; max-height:100vh; }
        .sidebar h1 { text-align:center; margin-bottom:20px; font-size:18px; }
        .sidebar a { display:block; padding:12px; margin:5px 0; border-radius:6px; color:#fff; text-decoration:none; cursor:pointer; transition:0.3s; }
        .sidebar a:hover { background:#0055aa; }
        .sidebar a.active { background:#007bff; }
        .main { flex:1; padding:25px; overflow-y:auto; max-height:100vh; }
        .card { background:#fff; padding:20px; margin-bottom:25px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1); display:none; }
        .card.active { display:block; }
        .card h3 { margin:0 0 15px 0; color:#003366; }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#003366; color:#fff; font-weight:600; }
        tr:hover { background:#f0f8ff; }
        .logout-btn { background:#c62828; color:#fff; padding:10px 18px; border:none; border-radius:5px; cursor:pointer; font-weight:600; width:100%; margin-top:20px; }
        .logout-btn:hover { background:#a30f0f; }
        .btn { padding:8px 12px; border:none; border-radius:5px; cursor:pointer; font-weight:600; margin-right:5px; font-size:13px; transition:0.3s; }
        .btn-green { background:#28a745; color:#fff; }
        .btn-green:hover { background:#218838; }
        .btn-blue { background:#007bff; color:#fff; }
        .btn-blue:hover { background:#0056b3; }
        .btn-red { background:#dc3545; color:#fff; }
        .btn-red:hover { background:#c82333; }
        .btn:disabled { opacity:0.6; cursor:not-allowed; }

        /* Modal Styles */
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); }
        .modal.active { display:flex; align-items:center; justify-content:center; }
        .modal-content { background:#fff; padding:30px; border-radius:10px; width:90%; max-width:500px; box-shadow:0 5px 20px rgba(0,0,0,0.3); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:2px solid #007bff; padding-bottom:10px; }
        .modal-header h2 { margin:0; color:#003366; font-size:22px; }
        .close-btn { background:none; border:none; font-size:28px; cursor:pointer; color:#666; }
        .close-btn:hover { color:#000; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:600; color:#333; }
        .form-group input, .form-group select { width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-family:inherit; }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:#007bff; box-shadow:0 0 5px rgba(0,123,255,0.3); }
        .modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
        .modal-footer .btn { margin-right:0; }
        .success-msg { color:#28a745; font-weight:600; }
        .error-msg { color:#dc3545; font-weight:600; }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="includes/images/logo 22.jpg" alt="E-Medicals Logo" style="height: 50px; width: auto; border-radius: 10px; object-fit: contain;">
        </div>
        <h1>ADMIN PANEL</h1>
        <a onclick="showSingleSection('usersSection')"> Users</a>
        <a onclick="showSingleSection('doctorsSection')">Doctors</a>
        <a onclick="showSingleSection('appointmentsSection')">Appointments</a>
        <a onclick="showSingleSection('availabilitySection')"> Doctor Availability</a>
        <a href="admin_orders.php">Pharmacy Orders</a>
        
        <a onclick="showSingleSection('approvals')"> Pending Doctor Approvals</a>
        <?php if ($payments_exist): ?><a onclick="showSingleSection('paymentsSection')"> Payments</a><?php endif; ?>
        <a href="admin_medicines.php">Pharmacy Catalog</a> 
       
        <a href="adminBlogs.php">Blog Management</a>
        <form action="logout.php" method="POST"><button class="logout-btn">Logout</button></form> 
        <!-- <a href="logout.php">Logout</a> -->
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Users -->
        <div id="usersSection" class="card active">
            <h3>Users Management</h3>
            <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr id="user-<?= $u['id']; ?>">
                    <td><?= $u['id']; ?></td>
                    <td><?= htmlspecialchars($u['fullname']); ?></td>
                    <td><?= htmlspecialchars($u['email']); ?></td>
                    <td><?= $u['user_type']; ?></td>
                    <td><?= $u['status']; ?></td>
                    <td>
                        <button class="btn btn-blue" data-id="<?= $u['id']; ?>" data-fullname="<?= htmlspecialchars($u['fullname'], ENT_QUOTES, 'UTF-8'); ?>" data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?>" data-type="<?= htmlspecialchars($u['user_type'], ENT_QUOTES, 'UTF-8'); ?>" data-status="<?= htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-user-btn">Edit</button>
                        <button class="btn btn-red" onclick="deleteUser(<?= $u['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <!-- Doctors -->
        <div id="doctorsSection" class="card">
            <h3>Doctors Management</h3>
            <table><thead><tr><th>ID</th><th>Name</th><th>Specialization</th><th>Fee</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($doctors as $d): ?>
                <tr id="doctor-<?= $d['doctor_id']; ?>">
                    <td><?= $d['id']; ?></td>
                    <td><?= htmlspecialchars($d['fullname']); ?></td>
                    <td><?= htmlspecialchars($d['specialization'] ?? 'N/A'); ?></td>
                    <td><?= $d['consultation_fee'] ?? 'N/A'; ?></td>
                    <td><button class="btn btn-blue" data-id="<?= $d['doctor_id']; ?>" data-specialization="<?= htmlspecialchars($d['specialization'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-fee="<?= htmlspecialchars($d['consultation_fee'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="edit-doctor-btn">Edit</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <!-- Appointments -->
        <div id="appointmentsSection" class="card">
            <h3>Appointments Management</h3>
            <table><thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
                <tr id="appointment-<?= $a['id']; ?>">
                    <td><?= $a['id']; ?></td>
                    <td><?= htmlspecialchars($a['patient_name']); ?></td>
                    <td><?= htmlspecialchars($a['doctor_name']); ?></td>
                    <td><?= $a['appointment_date']; ?></td>
                    <td><?= $a['status']; ?></td>
                    <td>
                        <button class="btn btn-blue" data-id="<?= $a['id']; ?>" data-status="<?= htmlspecialchars($a['status'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-appointment-btn">Edit</button>
                        <button class="btn btn-red" onclick="deleteAppointment(<?= $a['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <!-- Availability -->
        <div id="availabilitySection" class="card">
            <h3>Doctor Availability</h3>
            <table><thead><tr><th>ID</th><th>Doctor</th><th>Date</th><th>Start Time</th><th>End Time</th></tr></thead>
            <tbody>
            <?php foreach ($availability as $av): ?>
                <tr id="availability-<?= $av['id']; ?>">
                    <td><?= $av['id']; ?></td>
                    <td><?= htmlspecialchars($av['fullname']); ?></td>
                    <td><?= $av['available_date']; ?></td>
                    <td><?= $av['start_time'] ?? 'N/A'; ?></td>
                    <td><?= $av['end_time'] ?? 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <!-- Pharmacy Orders -->
        <div id="pharmacyOrdersSection" class="card">
            <h3>Pharmacy Orders</h3>
            <table><thead><tr><th>ID</th><th>Patient</th><th>Total Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($pharmacy_orders as $po): ?>
                <tr id="pharmacyOrder-<?= $po['id']; ?>">
                    <td><?= $po['id']; ?></td>
                    <td><?= htmlspecialchars($po['patient_name']); ?></td>
                    <td><?= $po['total_amount']; ?></td>
                    <td><?= $po['status']; ?></td>
                    <td><?= $po['created_at']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <!-- Pending Doctor Approvals -->
        <div id="approvals" class="card">
            <h3>Pending Doctor Approvals</h3>
            <table><thead><tr><th>ID</th><th>Doctor Name</th><th>Specialization</th><th>Applied On</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                <tr id="approval-<?= $row['doctor_id']; ?>">
                    <td><?= $row['doctor_id']; ?></td>
                    <td><?= htmlspecialchars($row['fullname']); ?></td>
                    <td><?= htmlspecialchars($row['specialization']); ?></td>
                    <td><?= $row['created_at']; ?></td>
                    <td>
                        <button class="btn btn-green" onclick="approveDoctor(<?= $row['doctor_id']; ?>)">Approve</button>
                        <button class="btn btn-red" onclick="rejectDoctor(<?= $row['doctor_id']; ?>)">Reject</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody></table>
        </div>

        <!-- Payments Section -->
       <?php if ($payments_exist): ?>
<div id="paymentsSection" class="card">
    <h3>Payments</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
                <tr id="payment-<?= $p['id']; ?>">
                    <td><?= $p['id']; ?></td>
                    <td><?= htmlspecialchars($p['fullname'] ?? 'N/A'); ?></td>
                    <td><?= $p['amount']; ?></td>
                    <td><?= htmlspecialchars($p['method'] ?? $p['payment_method'] ?? 'N/A'); ?></td>
                    <td><?= htmlspecialchars($p['status']); ?></td>
                    <td><?= $p['created_at']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>


    </div> <!-- end main -->
</div> <!-- end container -->



<!-- User Edit Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <button class="close-btn" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm">
            <input type="hidden" id="userId" name="id">
            <div class="form-group">
                <label for="userFullname">Full Name</label>
                <input type="text" id="userFullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="userEmail">Email</label>
                <input type="email" id="userEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="userType">User Type</label>
                <select id="userType" name="user_type" required>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="userStatus">Status</label>
                <select id="userStatus" name="status" required>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div id="userMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-red" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Doctor Edit Modal -->
<div id="doctorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Doctor</h2>
            <button class="close-btn" onclick="closeDoctorModal()">&times;</button>
        </div>
        <form id="doctorForm">
            <input type="hidden" id="doctorId" name="id">
            <div class="form-group">
                <label for="doctorSpecialization">Specialization</label>
                <input type="text" id="doctorSpecialization" name="specialization" required>
            </div>
            <div class="form-group">
                <label for="doctorFee">Consultation Fee</label>
                <input type="number" id="doctorFee" name="consultation_fee" step="0.01" required>
            </div>
            <div id="doctorMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-red" onclick="closeDoctorModal()">Cancel</button>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Appointment Edit Modal -->
<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Appointment</h2>
            <button class="close-btn" onclick="closeAppointmentModal()">&times;</button>
        </div>
        <form id="appointmentForm">
            <input type="hidden" id="appointmentId" name="id">
            <div class="form-group">
                <label for="appointmentStatus">Status</label>
                <select id="appointmentStatus" name="status" required>
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="rescheduled">Rescheduled</option>
                </select>
            </div>
            <div id="appointmentMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-red" onclick="closeAppointmentModal()">Cancel</button>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// ========= SECTION NAVIGATION =========
function showSingleSection(sectionId) {
    const sections = document.querySelectorAll('.card');
    sections.forEach(sec => sec.classList.remove('active'));
    const target = document.getElementById(sectionId);
    if (target) target.classList.add('active');
}

// ========= EVENT DELEGATION FOR EDIT BUTTONS =========
document.addEventListener('DOMContentLoaded', function() {
    showSingleSection('usersSection');
});

// User Edit Button Event Delegation
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-user-btn')) {
        const btn = e.target;
        const id = btn.dataset.id;
        const fullname = btn.dataset.fullname;
        const email = btn.dataset.email;
        const userType = btn.dataset.type;
        const status = btn.dataset.status;
        openUserModal(id, fullname, email, userType, status);
    }
});

// Doctor Edit Button Event Delegation
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-doctor-btn')) {
        const btn = e.target;
        const id = btn.dataset.id;
        const specialization = btn.dataset.specialization;
        const fee = btn.dataset.fee;
        openDoctorModal(id, specialization, fee);
    }
});

// Appointment Edit Button Event Delegation
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-appointment-btn')) {
        const btn = e.target;
        const id = btn.dataset.id;
        const status = btn.dataset.status;
        openAppointmentModal(id, status);
    }
});

// ========= USER MODAL =========
function openUserModal(id, fullname, email, userType, status) {
    document.getElementById('userId').value = id;
    document.getElementById('userFullname').value = fullname;
    document.getElementById('userEmail').value = email;
    document.getElementById('userType').value = userType;
    document.getElementById('userStatus').value = status;
    document.getElementById('userMessage').innerHTML = '';
    document.getElementById('userModal').classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
}

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('userMessage');
    
    fetch('admin_update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msgDiv.innerHTML = '<p class="success-msg">User updated successfully!</p>';
            setTimeout(() => {
                const id = formData.get('id');
                const row = document.getElementById('user-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('fullname');
                    row.cells[2].innerText = formData.get('email');
                    row.cells[3].innerText = formData.get('user_type');
                    row.cells[4].innerText = formData.get('status');
                }
                closeUserModal();
            }, 1000);
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// ========= DOCTOR MODAL =========
function openDoctorModal(id, specialization, fee) {
    document.getElementById('doctorId').value = id;
    document.getElementById('doctorSpecialization').value = specialization;
    document.getElementById('doctorFee').value = fee;
    document.getElementById('doctorMessage').innerHTML = '';
    document.getElementById('doctorModal').classList.add('active');
}

function closeDoctorModal() {
    document.getElementById('doctorModal').classList.remove('active');
}

document.getElementById('doctorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('doctorMessage');
    
    fetch('admin_update_doctor.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msgDiv.innerHTML = '<p class="success-msg">Doctor updated successfully!</p>';
            setTimeout(() => {
                const id = formData.get('id');
                const row = document.getElementById('doctor-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('specialization');
                    row.cells[2].innerText = formData.get('consultation_fee');
                }
                closeDoctorModal();
            }, 1000);
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">Network error: ' + e + '</p>';
    });
});

// ========= APPOINTMENT MODAL =========
function openAppointmentModal(id, status) {
    document.getElementById('appointmentId').value = id;
    document.getElementById('appointmentStatus').value = status;
    document.getElementById('appointmentMessage').innerHTML = '';
    document.getElementById('appointmentModal').classList.add('active');
}

function closeAppointmentModal() {
    document.getElementById('appointmentModal').classList.remove('active');
}

document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('appointmentMessage');
    
    fetch('admin_update_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msgDiv.innerHTML = '<p class="success-msg"> Appointment updated successfully!</p>';
            setTimeout(() => {
                const id = formData.get('id');
                const row = document.getElementById('appointment-' + id);
                if (row) {
                    row.cells[4].innerText = formData.get('status');
                }
                closeAppointmentModal();
            }, 1000);
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// ========= DELETE FUNCTIONS =========
function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    fetch('admin_delete_user.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('user-' + id).remove();
                alert('✓ User deleted successfully');
            } else {
                alert('✗ Error: ' + (data.message || 'Failed to delete'));
            }
        })
        .catch(e => alert('✗ Network error: ' + e));
}

function deleteAppointment(id) {
    if (!confirm('Are you sure you want to delete this appointment?')) return;
    fetch('admin_delete_appointment.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('appointment-' + id).remove();
                alert('✓ Appointment deleted successfully');
            } else {
                alert('✗ Error: ' + (data.message || 'Failed to delete'));
            }
        })
        .catch(e => alert('✗ Network error: ' + e));
}

// ========= DOCTOR APPROVAL =========
function approveDoctor(id) {
    if (!confirm('Approve this doctor?')) return;
    fetch('update_doctor_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ doctor_id: id, action: 'approve' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('approval-' + id).remove();
            alert('✓ Doctor approved successfully');
        } else {
            alert('✗ Error: ' + (data.message || 'Failed to approve'));
        }
    })
    .catch(e => alert('✗ Network error: ' + e));
}

function rejectDoctor(id) {
    if (!confirm('Reject and remove this doctor? This action cannot be undone.')) return;
    fetch('update_doctor_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ doctor_id: id, action: 'reject' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('approval-' + id).remove();
            alert('✓ Doctor rejected successfully');
        } else {
            alert('✗ Error: ' + (data.message || 'Failed to reject'));
        }
    })
    .catch(e => alert('✗ Network error: ' + e));
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    let userModal = document.getElementById('userModal');
    let doctorModal = document.getElementById('doctorModal');
    let appointmentModal = document.getElementById('appointmentModal');
    
    if (event.target == userModal) userModal.classList.remove('active');
    if (event.target == doctorModal) doctorModal.classList.remove('active');
    if (event.target == appointmentModal) appointmentModal.classList.remove('active');
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.getElementById('userModal').classList.remove('active');
        document.getElementById('doctorModal').classList.remove('active');
        document.getElementById('appointmentModal').classList.remove('active');
    }
});
</script>
  <script src="dashboard.js"></script>
</body>
</html>