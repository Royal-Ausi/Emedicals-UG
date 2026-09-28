<?php
/**
 * Email and SMS Notification Functions
 * Handles sending emails and SMS notifications
 */

require_once __DIR__ . '/appointment-helpers.php';

/**
 * Send email notification
 * @param string $email Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @param int|null $queue_id Queue ID for status update
 * @return bool Success status
 */
function sendEmailNotification($email, $subject, $message, $queue_id = null) {
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($queue_id) {
            updateQueueStatus($queue_id, 'failed');
        }
        return false;
    }
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: E-Medicals UG <noreply@emedicals.ug>" . "\r\n";
    $headers .= "Reply-To: support@emedicals.ug" . "\r\n";
    
    // Format message as HTML
    $html_message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #003366; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 10px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>E-Medicals UG</h2>
            </div>
            <div class='content'>
                " . nl2br(htmlspecialchars($message)) . "
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>&copy; " . date('Y') . " E-Medicals UG. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    $sent = mail($email, $subject, $html_message, $headers);
    
    // Update queue status
    if ($queue_id) {
        if ($sent) {
            updateQueueStatus($queue_id, 'sent', 'email');
        } else {
            updateQueueStatus($queue_id, 'failed');
        }
    }
    
    return $sent;
}

/**
 * Send SMS notification (placeholder for future integration)
 * @param string $phone Phone number
 * @param string $message SMS message
 * @param int|null $queue_id Queue ID for status update
 * @return bool Success status
 */
function sendSMSNotification($phone, $message, $queue_id = null) {
    // TODO: Integrate with Africa's Talking or Twilio API
    // For now, just log the SMS
    
    // Validate phone number
    if (!validatePhoneNumber($phone)) {
        if ($queue_id) {
            updateQueueStatus($queue_id, 'failed');
        }
        return false;
    }
    
    // Log SMS (in production, replace with actual API call)
    error_log("SMS to {$phone}: {$message}");
    
    // Placeholder: In production, use API like:
    // $api = new AfricasTalkingGateway($username, $apiKey);
    // $sent = $api->sendMessage($phone, $message);
    
    // For now, simulate success
    $sent = true;
    
    // Update queue status
    if ($queue_id) {
        if ($sent) {
            updateQueueStatus($queue_id, 'sent', 'sms');
        } else {
            updateQueueStatus($queue_id, 'failed');
        }
    }
    
    return $sent;
}

/**
 * Process email/SMS queue
 * Sends pending notifications from the queue
 * @param int $limit Maximum number of items to process
 * @return array Statistics (sent, failed, total)
 */
function processEmailSMSQueue($limit = 50) {
    global $conn;
    
    $stats = ['sent' => 0, 'failed' => 0, 'total' => 0];
    
    // Fetch pending items
    $stmt = $conn->prepare("
        SELECT * FROM email_sms_queue 
        WHERE status = 'pending' AND attempts < 3
        ORDER BY created_at ASC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $stats['total']++;
        
        $success = true;
        
        // Send email if recipient_email exists and send_type includes email
        if (!empty($item['recipient_email']) && 
            ($item['send_type'] === 'email' || $item['send_type'] === 'both')) {
            $subject = "Appointment Notification - E-Medicals UG";
            $email_sent = sendEmailNotification(
                $item['recipient_email'], 
                $subject, 
                $item['message'],
                $item['queue_id']
            );
            if (!$email_sent) {
                $success = false;
            }
        }
        
        // Send SMS if recipient_phone exists and send_type includes sms
        if (!empty($item['recipient_phone']) && 
            ($item['send_type'] === 'sms' || $item['send_type'] === 'both')) {
            $sms_sent = sendSMSNotification(
                $item['recipient_phone'], 
                $item['message'],
                $item['queue_id']
            );
            if (!$sms_sent) {
                $success = false;
            }
        }
        
        // Update statistics
        if ($success) {
            $stats['sent']++;
        } else {
            $stats['failed']++;
            incrementQueueAttempts($item['queue_id']);
        }
    }
    
    $stmt->close();
    
    return $stats;
}

/**
 * Update queue status
 * @param int $queue_id
 * @param string $status 'sent', 'failed', or 'pending'
 * @param string|null $send_type 'email' or 'sms' (for partial updates)
 * @return bool Success status
 */
function updateQueueStatus($queue_id, $status, $send_type = null) {
    global $conn;
    
    if ($status === 'sent') {
        $stmt = $conn->prepare("
            UPDATE email_sms_queue 
            SET status = ?, sent_at = CURRENT_TIMESTAMP 
            WHERE queue_id = ?
        ");
        $stmt->bind_param("si", $status, $queue_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE email_sms_queue 
            SET status = ? 
            WHERE queue_id = ?
        ");
        $stmt->bind_param("si", $status, $queue_id);
    }
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Increment queue attempts counter
 * @param int $queue_id
 * @return bool Success status
 */
function incrementQueueAttempts($queue_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE email_sms_queue 
        SET attempts = attempts + 1 
        WHERE queue_id = ?
    ");
    $stmt->bind_param("i", $queue_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Update notification sent status
 * @param int $notification_id
 * @param string $type 'email' or 'sms'
 * @return bool Success status
 */
function updateNotificationSentStatus($notification_id, $type) {
    global $conn;
    
    $field = $type === 'email' ? 'sent_via_email' : 'sent_via_sms';
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET {$field} = 1 
        WHERE notification_id = ?
    ");
    $stmt->bind_param("i", $notification_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

