<?php
/**
 * Appointment Helper Functions
 * Provides utility functions for appointment management
 */

require_once __DIR__ . '/../db.php';

/**
 * Get appointment details by ID
 * @param int $appointment_id
 * @return array|false Appointment data or false if not found
 */
function getAppointmentDetails($appointment_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, 
               u1.fullname AS patient_name, u1.email AS patient_email, u1.phone AS patient_phone,
               u2.fullname AS doctor_name, u2.email AS doctor_email
        FROM appointments a
        JOIN users u1 ON a.patient_id = u1.id
        JOIN users u2 ON a.doctor_id = u2.id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $stmt->close();
    
    return $appointment;
}

/**
 * Get doctor details by ID
 * @param int $doctor_id
 * @return array|false Doctor data or false if not found
 */
function getDoctorDetails($doctor_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT u.*, d.specialization, d.consultation_fee
        FROM users u
        LEFT JOIN doctors d ON d.user_id = u.id
        WHERE u.id = ? AND u.user_type = 'doctor'
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    $stmt->close();
    
    return $doctor;
}

/**
 * Get patient details by ID
 * @param int $patient_id
 * @return array|false Patient data or false if not found
 */
function getPatientDetails($patient_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT u.*, p.date_of_birth, p.gender, p.emergency_contact
        FROM users u
        LEFT JOIN patients p ON p.user_id = u.id
        WHERE u.id = ? AND u.user_type = 'patient'
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $stmt->close();
    
    return $patient;
}

/**
 * Format date and time for display
 * @param string $date Date string
 * @param string|null $time Time string (optional)
 * @return string Formatted date-time string
 */
function formatDateTime($date, $time = null) {
    if ($time) {
        $datetime = $date . ' ' . $time;
        return date("d M Y, h:i A", strtotime($datetime));
    }
    return date("d M Y", strtotime($date));
}

/**
 * Convert timestamp to "time ago" format
 * @param string $timestamp
 * @return string Human-readable time difference
 */
function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);
    
    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Generate a Google Meet link (placeholder - in production, use Google Calendar API)
 * @return string Google Meet link
 */
function generateMeetLink() {
    // In production, integrate with Google Calendar API to create actual meeting
    // For now, return a placeholder format
    $randomId = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3) . 
                '-' . 
                substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4) . 
                '-' . 
                substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3);
    return 'https://meet.google.com/' . $randomId;
}

/**
 * Validate phone number (Uganda format: +256...)
 * @param string $phone
 * @return bool True if valid
 */
function validatePhoneNumber($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    
    // Check Uganda format: +256XXXXXXXXX (12 digits after +256)
    if (preg_match('/^\+256[0-9]{9}$/', $phone)) {
        return true;
    }
    
    // Also accept 256XXXXXXXXX (without +)
    if (preg_match('/^256[0-9]{9}$/', $phone)) {
        return true;
    }
    
    return false;
}

/**
 * Sanitize and validate meeting link
 * @param string $link
 * @param string $type Meeting type (google_meet, whatsapp, phone)
 * @return string|false Sanitized link or false if invalid
 */
function sanitizeMeetingLink($link, $type) {
    $link = trim($link);
    
    switch ($type) {
        case 'google_meet':
            // Accept meet.google.com/xxx-xxxx-xxx with or without https
            if (preg_match('#^(https?://)?(www\.)?meet\.google\.com/[a-z]{3}-[a-z]{4}-[a-z]{3}$#i', $link)) {
                if (strpos($link, 'http') !== 0) {
                    return 'https://' . ltrim($link, '/');
                }
                return $link;
            }
            if (filter_var($link, FILTER_VALIDATE_URL) && strpos($link, 'meet.google.com') !== false) {
                return $link;
            }
            // If it's just the meeting code (e.g. abc-defg-hij), prepend the URL
            if (preg_match('/^[a-z0-9]{3}-[a-z0-9]{4}-[a-z0-9]{3}$/i', $link)) {
                return 'https://meet.google.com/' . strtolower($link);
            }
            return false;
            
        case 'whatsapp':
        case 'phone':
            // Validate phone number
            if (validatePhoneNumber($link)) {
                // Format: ensure +256 prefix
                $phone = preg_replace('/[\s\-]/', '', $link);
                if (substr($phone, 0, 4) === '2567') {
                    return '+' . $phone;
                } elseif (substr($phone, 0, 1) !== '+') {
                    return '+' . $phone;
                }
                return $phone;
            }
            return false;
            
        default:
            return false;
    }
}

/**
 * Check if doctor is authorized for appointment
 * @param int $appointment_id
 * @param int $doctor_id
 * @return bool True if authorized
 */
function isDoctorAuthorized($appointment_id, $doctor_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count 
        FROM appointments 
        WHERE id = ? AND doctor_id = ?
    ");
    $stmt->bind_param("ii", $appointment_id, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'] > 0;
}

