<?php
/**
 * Notifications API - JSON endpoint for dashboard notifications modal
 * GET: returns list of notifications and unread count
 * POST: mark_read (notification_id) or mark_all_read
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/notification-functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';

// Only patients have doctor-response notifications
if ($user_type !== 'patient') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode([
        'notifications' => [],
        'unread_count' => 0
    ]);
    exit;
}

// POST: mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if ($action === 'mark_read' && isset($_POST['notification_id'])) {
        $notification_id = (int) $_POST['notification_id'];
        $result = markNotificationAsRead($notification_id, $user_id);
        echo json_encode(['success' => (bool) $result]);
        exit;
    }
    
    if ($action === 'mark_all_read') {
        $result = markAllNotificationsAsRead($user_id);
        echo json_encode(['success' => (bool) $result]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// GET: fetch notifications
$stmt = $conn->prepare("
    SELECT n.notification_id, n.appointment_id, n.notification_type, n.message, n.is_read, n.created_at
    FROM notifications n
    WHERE n.patient_id = ?
    ORDER BY n.created_at DESC
    LIMIT 100
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$unread_count = getUnreadNotificationCount($user_id);

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => (int) $unread_count
]);
