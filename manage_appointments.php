<?php
/**
 * 
 * Dashboard stays default; this page is where the doctor does Confirm/Reschedule/Cancel.
 */
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header('Location: index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$fullname = $_SESSION['fullname'] ?? 'Doctor';

// Fetch doctor's appointments
$stmt = $conn->prepare("
    SELECT a.id, a.appointment_date, a.appointment_end_time, a.symptoms, a.status,
           u.fullname AS patient_name, r.id AS report_id
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    LEFT JOIN patient_reports r ON r.appointment_id = a.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Appointments | E-Medicals UG</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #004080; --primary-light: #0066cc; --bg: #f5f6fa; --muted: #666; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: #111; }
        .sidebar {
            width: 220px; background: var(--primary); color: #fff; height: 100vh; position: fixed; left: 0; top: 0;
            padding: 20px; display: flex; flex-direction: column; justify-content: space-between;
        }
        .sidebar h2 { font-size: 1.1rem; margin-bottom: 20px; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 8px 10px; border-radius: 6px; margin-bottom: 6px; }
        .sidebar a:hover { background: var(--primary-light); }
        .sidebar a.active { background: rgba(255,255,255,0.2); }
        .main { margin-left: 220px; padding: 24px; min-height: 100vh; }
        .header { margin-bottom: 24px; }
        .header h1 { font-size: 1.5rem; color: #111; }
        .small { font-size: 0.85rem; color: var(--muted); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .btn { padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-primary:hover { background: #0056b3; }
        .btn.secondary { background: #6c757d; color: #fff; }
        .status.pending { background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status.confirmed { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status.rescheduled { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status.cancelled { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status.completed { background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .status-badge.status-confirmed { background: #d4edda; color: #155724; }
        .status-badge.status-rescheduled { background: #fff3cd; color: #856404; }
        .status-badge.status-cancelled { background: #f8d7da; color: #721c24; }
        select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
        /* Response modal - must sit on top and be visible when shown */
        #responseModal { position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); overflow: auto; align-items: center; justify-content: center; display: none; }
        #responseModal[style*="flex"] { display: flex !important; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .modal-header h2 { margin: 0; color: #003366; font-size: 22px; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #666; }
        .close-btn:hover { color: #000; }
        .patient-info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .patient-info p { margin: 5px 0; }
        .radio-group { display: flex; flex-direction: column; gap: 10px; }
        .radio-label { display: flex; align-items: center; padding: 10px; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; transition: all 0.2s; }
        .radio-label:hover { background: #f8f9fa; border-color: #007bff; }
        .radio-label input[type="radio"] { margin-right: 10px; }
        .radio-label input[type="radio"]:checked + span { font-weight: 600; color: #007bff; }
        .response-fields { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #007bff; }
        .help-text { display: block; margin-top: 5px; font-size: 12px; color: #666; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .success-msg { color: #28a745; font-weight: 600; padding: 10px; background: #d4edda; border-radius: 5px; }
        .error-msg { color: #dc3545; font-weight: 600; padding: 10px; background: #f8d7da; border-radius: 5px; }
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="../includes/images/logo 22.jpg" alt="E-Medicals Logo" style="height: 40px; width: auto; object-fit: contain;">
            </div>
            <h2>E-Medicals UG</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_appointments.php" class="active">Manage Appointments</a>
            <!-- <a href="#" onclick="openNotificationsModal(); return false;">Notifications</a> -->
        </div>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Manage Appointments</h1>
            <p class="small">Respond to pending requests: confirm with time & link, or reschedule/cancel with a reason. The patient is notified and emailed.</p>
        </div>

        <div id="appointments">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date & Time</th>
                        <th>Symptoms</th>
                        <th>Status</th>
                        <th>Report</th>
                        <th>Respond</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['patient_name'] ?? ''); ?></td>
                        <td><?= date('d M Y, H:i', strtotime($a['appointment_date'])); ?></td>
                        <td><?= htmlspecialchars($a['symptoms']); ?></td>
                        <td>
                            <span class="status <?= htmlspecialchars($a['status']); ?>">
                                <?php
                                $s = $a['status'];
                                echo $s === 'confirmed' ? 'Confirmed' : ($s === 'rescheduled' ? 'Rescheduled' : ($s === 'cancelled' ? 'Cancelled' : ucfirst($s)));
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($a['report_id'])): ?>
                                <a class="btn secondary" href="view_report.php?id=<?= (int)$a['report_id']; ?>">View</a>
                            <?php else: ?>
                                <a class="btn btn-primary" href="add_report.php?appointment_id=<?= (int)$a['id']; ?>">Add</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-respond" data-id="<?= (int)$a['id']; ?>" data-name="<?= htmlspecialchars($a['patient_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-date="<?= date('Y-m-d', strtotime($a['appointment_date'])); ?>" data-time="<?= date('H:i', strtotime($a['appointment_date'])); ?>">Respond</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($appointments)): ?>
        <p class="small" style="margin-top:16px">No appointments yet.</p>
        <?php endif; ?>

        <?php if (!empty($_SESSION['toast_message'])): ?>
        <div class="toast <?= ($_SESSION['toast_type'] ?? '') === 'success' ? 'success' : 'error' ?>" id="toast" style="position:fixed;bottom:20px;right:20px;padding:12px 20px;border-radius:8px;color:#fff;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:999;">
            <?= htmlspecialchars($_SESSION['toast_message']); ?>
        </div>
        <script>setTimeout(function(){ var t=document.getElementById('toast'); if(t) t.style.display='none'; }, 4000);</script>
        <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
        <?php endif; ?>
    </div>

    <!-- Response Modal (pops out on same page, no redirect) -->
    <div id="responseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Respond to Appointment</h2>
                <button class="close-btn" type="button" onclick="closeResponseModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p class="small" style="margin-bottom:12px;background:#e7f3ff;padding:8px 12px;border-radius:6px;">What you enter below is sent to the patient as their notification and by email.</p>
                <div class="patient-info">
                    <p><strong>Patient:</strong> <span id="modalPatientName"></span></p>
                    <p><strong>Requested Date:</strong> <span id="modalAppointmentDate"></span></p>
                    <p><strong>Requested Time:</strong> <span id="modalAppointmentTime"></span></p>
                </div>
                <form id="responseForm">
                    <input type="hidden" id="responseAppointmentId" name="appointment_id">
                    <input type="hidden" id="responseType" name="response_type">
                    <div class="form-group">
                        <label>Choose action</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="confirm" onchange="toggleResponseFields()" required>
                                <span>✓ Confirm — fill details below (sent to patient)</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="reschedule" onchange="toggleResponseFields()">
                                <span>↻ Reschedule — write reason below (sent to patient)</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="response_type_radio" value="cancel" onchange="toggleResponseFields()">
                                <span>✗ Cancel — write reason below (sent to patient)</span>
                            </label>
                        </div>
                    </div>
                    <div id="confirmFields" class="response-fields" style="display:none">
                        <p class="small" style="margin-bottom:10px">Enter the details the patient will see in their notification:</p>
                        <div class="form-group">
                            <label for="confirmedDate">Confirmed date *</label>
                            <input type="date" id="confirmedDate" name="confirmed_date" min="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="confirmedTime">Confirmed time *</label>
                            <input type="time" id="confirmedTime" name="confirmed_time">
                        </div>
                        <div class="form-group">
                            <label for="meetingType">How will you meet? *</label>
                            <select id="meetingType" name="meeting_type" onchange="updateMeetingPlaceholder()">
                                <option value="">-- Select --</option>
                                <option value="zoom">Zoom (paste your meeting link)</option>
                                <option value="whatsapp">WhatsApp / Call</option>
                                <option value="phone">Phone</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="meetingLink">Zoom link or contact details * (e.g. link or &quot;I will call you on +256…&quot;)</label>
                            <input type="text" id="meetingLink" name="meeting_link" placeholder="Paste Zoom link or e.g. I will call you on +256 700 000 000">
                            <span class="help-text" id="meetingHelp"></span>
                        </div>
                    </div>
                    <div id="rescheduleFields" class="response-fields" style="display:none">
                        <p class="small" style="margin-bottom:10px">This message is sent to the patient as their notification:</p>
                        <div class="form-group">
                            <label for="rescheduleReason">Reason for rescheduling *</label>
                            <textarea id="rescheduleReason" name="reschedule_reason" rows="4" placeholder="Write the reason (e.g. Unavailable that day. Please book another slot.)"></textarea>
                        </div>
                    </div>
                    <div id="cancelFields" class="response-fields" style="display:none">
                        <p class="small" style="margin-bottom:10px">This message is sent to the patient as their notification:</p>
                        <div class="form-group">
                            <label for="cancelReason">Reason for cancellation *</label>
                            <textarea id="cancelReason" name="cancel_reason" rows="4" placeholder="Write the reason the patient will see."></textarea>
                        </div>
                    </div>
                    <div id="responseMessage" style="display:none;margin:15px 0"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeResponseModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitResponseBtn">
                            <span id="submitBtnText">Submit</span>
                            <span id="submitBtnLoader" style="display:none">Sending…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openResponseModal(appointmentId, patientName, appointmentDate, appointmentTime) {
        var modal = document.getElementById('responseModal');
        if (!modal) return;
        var nameEl = document.getElementById('modalPatientName');
        var dateEl = document.getElementById('modalAppointmentDate');
        var timeEl = document.getElementById('modalAppointmentTime');
        if (nameEl) nameEl.textContent = patientName || '';
        if (dateEl) dateEl.textContent = formatDate(appointmentDate);
        if (timeEl) timeEl.textContent = formatTime(appointmentTime);
        var idInput = document.getElementById('responseAppointmentId');
        if (idInput) idInput.value = appointmentId;
        var form = document.getElementById('responseForm');
        if (form) form.reset();
        var msgEl = document.getElementById('responseMessage');
        if (msgEl) { msgEl.style.display = 'none'; msgEl.className = ''; }
        var cd = document.getElementById('confirmedDate');
        var ct = document.getElementById('confirmedTime');
        if (cd) cd.value = appointmentDate || '';
        if (ct) ct.value = appointmentTime || '';
        var cf = document.getElementById('confirmFields'), rf = document.getElementById('rescheduleFields'), cxf = document.getElementById('cancelFields');
        if (cf) cf.style.display = 'none';
        if (rf) rf.style.display = 'none';
        if (cxf) cxf.style.display = 'none';
        if (idInput) idInput.value = appointmentId;
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.visibility = 'visible';
        modal.setAttribute('aria-hidden', 'false');
    }
    function closeResponseModal() {
        var modal = document.getElementById('responseModal');
        if (modal) { modal.style.display = 'none'; modal.style.visibility = 'hidden'; modal.setAttribute('aria-hidden', 'true'); }
        var form = document.getElementById('responseForm');
        if (form) form.reset();
        var msgEl = document.getElementById('responseMessage');
        if (msgEl) msgEl.style.display = 'none';
    }
    function toggleResponseFields() {
        var sel = document.querySelector('input[name="response_type_radio"]:checked');
        if (!sel) return;
        var v = sel.value;
        document.getElementById('responseType').value = v;
        document.getElementById('confirmFields').style.display = v === 'confirm' ? 'block' : 'none';
        document.getElementById('rescheduleFields').style.display = v === 'reschedule' ? 'block' : 'none';
        document.getElementById('cancelFields').style.display = v === 'cancel' ? 'block' : 'none';
    }
    function updateMeetingPlaceholder() {
        var t = document.getElementById('meetingType').value;
        var inp = document.getElementById('meetingLink');
        var help = document.getElementById('meetingHelp');
        if (t === 'zoom') { inp.placeholder = 'Paste Zoom link (e.g. https://zoom.us/j/...)'; help.textContent = 'Paste the link from Zoom.'; }
        else if (t === 'whatsapp' || t === 'phone') { inp.placeholder = 'e.g. I will call you on +256 700 000 000'; help.textContent = 'This is sent to the patient.'; }
        else { inp.placeholder = ''; help.textContent = ''; }
    }
    function formatDate(s) { try { return new Date(s).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }); } catch(e) { return s; } }
    function formatTime(s) {
        if (!s) return '';
        var p = String(s).split(':');
        var h = parseInt(p[0], 10);
        var m = (p[1] || '00');
        return (h % 12 || 12) + ':' + m + (h >= 12 ? ' PM' : ' AM');
    }
    function showMsg(msg, type) {
        var el = document.getElementById('responseMessage');
        el.textContent = msg;
        el.className = type === 'success' ? 'success-msg' : 'error-msg';
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    document.getElementById('responseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        var rt = fd.get('response_type');
        if (!rt) { showMsg('Please select a response type.', 'error'); return; }
        if (rt === 'reschedule' && !(fd.get('reschedule_reason') || '').trim()) { showMsg('Please enter the reason for rescheduling.', 'error'); return; }
        if (rt === 'cancel' && !(fd.get('cancel_reason') || '').trim()) { showMsg('Please enter the reason for cancellation.', 'error'); return; }
        var btn = document.getElementById('submitResponseBtn');
        var txt = document.getElementById('submitBtnText');
        var loader = document.getElementById('submitBtnLoader');
        btn.disabled = true;
        txt.style.display = 'none';
        loader.style.display = 'inline';
        fetch('doctor/respond-appointment.php', { method: 'POST', body: fd })
                .then(function(resp) {
                    return resp.text().then(function(text) {
                        if (!resp.ok) {
                            throw new Error(text || 'Server error');
                        }
                        var data;
                        try {
                            data = JSON.parse(text || '{}');
                        } catch (e) {
                            data = { success: true, message: 'Response processed (server returned non-JSON). Refresh to confirm.' };
                        }
                        if (data.success) {
                            showMsg(data.message || 'Done. Patient notified.', 'success');
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            showMsg(data.message || 'Something went wrong.', 'error');
                            btn.disabled = false;
                            document.getElementById('submitBtnText').style.display = 'inline';
                            document.getElementById('submitBtnLoader').style.display = 'none';
                        }
                    });
                })
                .catch(function(err) {
                    var message = (err && err.message) ? err.message : 'Network error';
                    showMsg('Request failed: ' + message, 'error');
                    btn.disabled = false;
                    document.getElementById('submitBtnText').style.display = 'inline';
                    document.getElementById('submitBtnLoader').style.display = 'none';
                });
    });
    window.addEventListener('click', function(ev) {
        if (ev.target.id === 'responseModal') closeResponseModal();
    });
    document.addEventListener('keydown', function(ev) {
        if (ev.key === 'Escape') closeResponseModal();
    });
    document.addEventListener('click', function(ev) {
        var btn = ev.target.closest('.btn-respond');
        if (btn) {
            ev.preventDefault();
            ev.stopPropagation();
            var id = btn.getAttribute('data-id');
            var name = btn.getAttribute('data-name') || '';
            var date = btn.getAttribute('data-date') || '';
            var time = btn.getAttribute('data-time') || '';
            openResponseModal(id, name, date, time);
        }
    });
    function openNotificationsModal() {
        window.location.href = 'dashboard.php';
    }
    </script>
</body>
</html>
