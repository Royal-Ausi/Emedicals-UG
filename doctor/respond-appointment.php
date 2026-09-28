<?php
/**
 * 
 * Handles doctor responses to appointment requests (confirm/reschedule/cancel)
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/appointment-helpers.php';
require_once __DIR__ . '/../includes/notification-functions.php';
require_once __DIR__ . '/../includes/email-sms-functions.php';

header('Content-Type: application/json');

// Auth
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in as a doctor.']);
    exit;
}

$doctor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
$response_type = isset($_POST['response_type']) ? trim($_POST['response_type']) : '';

if ($appointment_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
    exit;
}

if (!in_array($response_type, ['confirm', 'reschedule', 'cancel'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid response type.']);
    exit;
}

if (!isDoctorAuthorized($appointment_id, $doctor_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You are not authorized to respond to this appointment.']);
    exit;
}

$appointment = getAppointmentDetails($appointment_id);
if (!$appointment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
    exit;
}

// Accept both 'scheduled' (default DB) and 'pending' (if migration applied)
if (!in_array($appointment['status'], ['pending', 'scheduled'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'This appointment has already been responded to or cannot be modified.']);
    exit;
}

$conn->begin_transaction();

try {
    $notification_data = [];

    switch ($response_type) {
        case 'confirm':
            $confirmed_date = isset($_POST['confirmed_date']) ? trim($_POST['confirmed_date']) : '';
            $confirmed_time = isset($_POST['confirmed_time']) ? trim($_POST['confirmed_time']) : '';
            $meeting_type = isset($_POST['meeting_type']) ? trim($_POST['meeting_type']) : '';
            $meeting_link = isset($_POST['meeting_link']) ? trim($_POST['meeting_link']) : '';

            if (empty($confirmed_date) || empty($confirmed_time)) {
                throw new Exception('Confirmed date and time are required.');
            }
            $confirmed_datetime = strtotime($confirmed_date . ' ' . $confirmed_time);
            if ($confirmed_datetime === false || $confirmed_datetime < time()) {
                throw new Exception('Please enter a valid future date and time.');
            }
            if (empty($meeting_type) || !in_array($meeting_type, ['zoom', 'google_meet', 'whatsapp', 'phone'])) {
                throw new Exception('Please select how you will meet (Zoom, WhatsApp, or Phone).');
            }
            if (empty($meeting_link)) {
                throw new Exception('Please add your Zoom link or write how you will contact the patient.');
            }

            // Keep meeting link for notification, but avoid updating DB columns that may not exist
            $meeting_link = substr($meeting_link, 0, 500);
            $confirmed_mysql = date('Y-m-d H:i:s', $confirmed_datetime);

            $stmt = $conn->prepare(
                "UPDATE appointments SET appointment_date = ?, doctor_notes = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->bind_param('si', $confirmed_mysql, $appointment_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update appointment.');
            }
            $stmt->close();

            $notification_data = [
                'confirmed_date' => $confirmed_date,
                'confirmed_time' => $confirmed_time,
                'meeting_type' => $meeting_type,
                'meeting_link' => $meeting_link
            ];
            break;

        case 'reschedule':
            $reason = isset($_POST['reschedule_reason']) ? trim($_POST['reschedule_reason']) : (isset($_POST['reason']) ? trim($_POST['reason']) : '');
            if (empty($reason)) {
                throw new Exception('Reason for rescheduling is required.');
            }
            $reason = substr($reason, 0, 2000);

            // Save reason in doctor_notes rather than setting an unsupported status
            $stmt = $conn->prepare(
                "UPDATE appointments SET doctor_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->bind_param('si', $reason, $appointment_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update appointment.');
            }
            $stmt->close();

            $notification_data = ['reason' => $reason];
            break;

        case 'cancel':
            $reason = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : (isset($_POST['reason']) ? trim($_POST['reason']) : '');
            if (empty($reason)) {
                throw new Exception('Cancellation reason is required.');
            }

            $stmt = $conn->prepare(
                "UPDATE appointments SET status = 'cancelled', cancellation_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->bind_param('si', $reason, $appointment_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update appointment.');
            }
            $stmt->close();

            $notification_data = ['reason' => $reason];
            break;
    }

    $notification_type = $response_type === 'confirm' ? 'confirmed' : ($response_type === 'reschedule' ? 'rescheduled' : 'cancelled');

    $notification_id = createNotification($appointment_id, $notification_type, $notification_data);
    if (!$notification_id) {
        throw new Exception('Failed to create notification.');
    }

    $conn->commit();

    try {
        if (function_exists('processEmailSMSQueue')) {
            processEmailSMSQueue(10);
        }
    } catch (Exception $e) {
        error_log('Email/SMS queue processing failed: ' . $e->getMessage());
    }

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Appointment response submitted successfully. Patient has been notified.', 'notification_id' => $notification_id]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Appointment response error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

