<?php
/**
 * Notification Functions
 * Handles creation and queuing of appointment notifications
 */

require_once __DIR__ . '/appointment-helpers.php';

/**
 * Create notification for appointment response
 * @param int $appointment_id
 * @param string $response_type 'confirmed', 'rescheduled', or 'cancelled'
 * @param array $data Additional data (meeting_link, new_date, reason, etc.)
 * @return int|false Notification ID or false on failure
 */
function createNotification($appointment_id, $response_type, $data = []) {
    global $conn;
    
    // Validate response type
    if (!in_array($response_type, ['confirmed', 'rescheduled', 'cancelled'])) {
        return false;
    }
    
    // Get appointment details
    $appointment = getAppointmentDetails($appointment_id);
    if (!$appointment) {
        return false;
    }
    
    $patient_id = $appointment['patient_id'];
    $doctor_name = $appointment['doctor_name'];
    $appointment_date = $appointment['appointment_date'];
    
    // Generate message based on response type
    $message = generateNotificationMessage($response_type, $appointment, $data);
    
    // Insert notification
    $stmt = $conn->prepare("
        INSERT INTO notifications 
        (patient_id, appointment_id, notification_type, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $patient_id, $appointment_id, $response_type, $message);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    
    $notification_id = $conn->insert_id;
    $stmt->close();
    
    // Queue email/SMS
    queueEmailSMS($patient_id, $notification_id, $message);
    
    return $notification_id;
}

/**
 * Generate notification message based on response type
 * @param string $response_type
 * @param array $appointment
 * @param array $data
 * @return string Notification message
 */
function generateNotificationMessage($response_type, $appointment, $data) {
    $doctor_name = $appointment['doctor_name'];
    $date = date("d M Y", strtotime($appointment['appointment_date']));
    $time = date("h:i A", strtotime($appointment['appointment_date']));
    
    switch ($response_type) {
        case 'confirmed':
            $cdate = !empty($data['confirmed_date']) ? date("d M Y", strtotime($data['confirmed_date'])) : $date;
            $ctime = !empty($data['confirmed_time']) ? date("h:i A", strtotime($data['confirmed_time'])) : $time;
            $message = "Your appointment with Dr. {$doctor_name} is confirmed for {$cdate} at {$ctime}.";
            if (!empty($data['meeting_link'])) {
                $mt = isset($data['meeting_type']) ? $data['meeting_type'] : '';
                if ($mt === 'zoom' || $mt === 'google_meet') {
                    $message .= "\n\nJoin via Zoom: " . $data['meeting_link'];
                } else {
                    $message .= "\n\n" . $data['meeting_link'];
                }
            }
            break;
            
        case 'rescheduled':
            $message = "Your appointment with Dr. {$doctor_name} has been rescheduled.";
            if (!empty($data['reason'])) {
                $message .= "\n\nReason: " . $data['reason'];
            }
            break;
            
        case 'cancelled':
            $message = "Your appointment with Dr. {$doctor_name} scheduled for {$date} at {$time} has been cancelled.";
            if (!empty($data['reason'])) {
                $message .= "\n\nReason: " . $data['reason'];
            }
            break;
            
        default:
            $message = "Your appointment status has been updated.";
    }
    
    return $message;
}

/**
 * Queue email and SMS for sending
 * @param int $patient_id
 * @param int $notification_id
 * @param string $message
 * @return bool Success status
 */
function queueEmailSMS($patient_id, $notification_id, $message) {
    global $conn;
    
    // Get patient email and phone
    $patient = getPatientDetails($patient_id);
    if (!$patient) {
        return false;
    }
    
    $email = $patient['email'] ?? null;
    $phone = $patient['phone'] ?? null;
    
    // Insert into queue
    $stmt = $conn->prepare("
        INSERT INTO email_sms_queue 
        (patient_id, recipient_email, recipient_phone, message, notification_id, send_type)
        VALUES (?, ?, ?, ?, ?, 'both')
    ");
    $stmt->bind_param("isssi", $patient_id, $email, $phone, $message, $notification_id);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Get unread notification count for patient
 * @param int $patient_id
 * @return int Count of unread notifications
 */
function getUnreadNotificationCount($patient_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count 
        FROM notifications 
        WHERE patient_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)$row['count'];
}

/**
 * Mark notification as read
 * @param int $notification_id
 * @param int $patient_id (for security - verify ownership)
 * @return bool Success status
 */
function markNotificationAsRead($notification_id, $patient_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notification_id = ? AND patient_id = ?
    ");
    $stmt->bind_param("ii", $notification_id, $patient_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Mark all notifications as read for patient
 * @param int $patient_id
 * @return bool Success status
 */
function markAllNotificationsAsRead($patient_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE patient_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $patient_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

