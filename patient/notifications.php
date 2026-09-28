<?php
/**
 * Patient Notifications Page
 * Displays all notifications for the logged-in patient
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/appointment-helpers.php';
require_once __DIR__ . '/../includes/notification-functions.php';

// Check if patient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: ../index.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$patient_name = $_SESSION['fullname'] ?? 'Patient';

// Handle mark as read (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
        $notification_id = (int)$_POST['notification_id'];
        $result = markNotificationAsRead($notification_id, $patient_id);
        echo json_encode(['success' => $result]);
        exit;
    }
    
    if ($_POST['action'] === 'mark_all_read') {
        $result = markAllNotificationsAsRead($patient_id);
        echo json_encode(['success' => $result]);
        exit;
    }
}

// Fetch notifications
$stmt = $conn->prepare("
    SELECT n.*, a.appointment_date, a.status AS appointment_status
    FROM notifications n
    LEFT JOIN appointments a ON n.appointment_id = a.id
    WHERE n.patient_id = ?
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unread count
$unread_count = getUnreadNotificationCount($patient_id);

// Filter: All or Unread
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if ($filter === 'unread') {
    $notifications = array_filter($notifications, function($n) {
        return $n['is_read'] == 0;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications<?= $unread_count > 0 ? " ({$unread_count})" : '' ?> - E-Medicals UG</title>
    <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #003366;
            margin-bottom: 10px;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 8px 16px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            border-color: #007bff;
            color: #007bff;
        }
        
        .filter-tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
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
        
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #ddd;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .notification-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .notification-item.unread {
            background: #e7f3ff;
            border-left-color: #007bff;
        }
        
        .notification-item.confirmed {
            border-left-color: #28a745;
        }
        
        .notification-item.rescheduled {
            border-left-color: #ffc107;
        }
        
        .notification-item.cancelled {
            border-left-color: #dc3545;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .notification-type {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f0f0f0;
        }
        
        .notification-type.confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .notification-type.rescheduled {
            background: #fff3cd;
            color: #856404;
        }
        
        .notification-type.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .notification-time {
            color: #666;
            font-size: 12px;
        }
        
        .notification-message {
            color: #333;
            white-space: pre-wrap;
            line-height: 1.8;
            margin-top: 10px;
        }
        
        .notification-message a {
            color: #007bff;
            text-decoration: none;
            word-break: break-all;
        }
        
        .notification-message a:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state h2 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            
            .filter-tabs {
                justify-content: center;
                flex-wrap: wrap;
            }

            .container {
                padding: 12px;
            }

            .header {
                padding: 16px;
                margin-bottom: 16px;
            }

            .header h1 {
                font-size: 1.3rem;
            }

            .notification-item {
                padding: 16px;
                margin-bottom: 12px;
            }

            .notification-header {
                flex-direction: column;
                gap: 8px;
            }

            .notification-type {
                font-size: 12px;
                padding: 3px 6px;
            }

            .filter-tab {
                padding: 6px 12px;
                font-size: 0.9rem;
            }

            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
                width: 100%;
            }

            .back-link {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 8px;
            }

            .header {
                padding: 12px;
            }

            .header h1 {
                font-size: 1.1rem;
            }

            .notification-item {
                padding: 12px;
                border-radius: 6px;
            }

            .notification-message {
                font-size: 0.9rem;
                line-height: 1.6;
            }

            .notification-time {
                font-size: 11px;
            }

            .empty-state {
                padding: 40px 15px;
            }

            .empty-state-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }

            .empty-state h2 {
                font-size: 1.1rem;
            }

            .empty-state p {
                font-size: 0.9rem;
            }

            .filter-tabs {
                gap: 6px;
            }

            .filter-tab {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">Back to Dashboard</a>
        
        <div class="header">
            <h1>Notifications<?= $unread_count > 0 ? " <span style='color: #dc3545;'>({$unread_count} unread)</span>" : '' ?></h1>
            
            <div class="header-actions">
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All</a>
                    <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : '' ?>">
                        Unread<?= $unread_count > 0 ? " ({$unread_count})" : '' ?>
                    </a>
                </div>
                
                <?php if ($unread_count > 0): ?>
                    <button class="btn btn-secondary" onclick="markAllAsRead()">Mark All as Read</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <h2>No Notifications</h2>
                    <p><?= $filter === 'unread' ? 'You have no unread notifications.' : 'You don\'t have any notifications yet.' ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] == 0 ? 'unread' : '' ?> <?= $notification['notification_type'] ?>" 
                         onclick="markAsRead(<?= $notification['notification_id']; ?>, this)">
                        <div class="notification-header">
                            <span class="notification-type <?= $notification['notification_type'] ?>">
                                <?= ucfirst($notification['notification_type']) ?>
                            </span>
                            <span class="notification-time">
                                <?= timeAgo($notification['created_at']) ?>
                            </span>
                        </div>
                        <div class="notification-message">
                            <?= parseNotificationMessage($notification['message']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function markAsRead(notificationId, element) {
            if (element.classList.contains('unread')) {
                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_read&notification_id=' + notificationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        element.classList.remove('unread');
                        element.style.background = 'white';
                        // Update unread count in header
                        updateUnreadCount();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
        
        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_all_read'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
        
        function updateUnreadCount() {
            // Reload page to update count, or use AJAX to fetch new count
        
            setTimeout(() => {
                location.reload();
            }, 500);
        }
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            // Check for new notifications (optional - can be removed if not needed)
            // For now, we'll just reload the page
            // location.reload();
        }, 30000);
    </script>
</body>
</html>

<?php
/**
 * Parse notification message to make links clickable
 */
function parseNotificationMessage($message) {
    // Make URLs clickable
    $message = preg_replace(
        '/(https?:\/\/[^\s]+)/',
        '<a href="$1" target="_blank">$1</a>',
        $message
    );
    
    // Make phone numbers clickable (WhatsApp)
    $message = preg_replace(
        '/(\+256[0-9]{9})/',
        '<a href="https://wa.me/$1" target="_blank">$1</a>',
        $message
    );
    
    // Escape HTML but preserve links
    return nl2br(htmlspecialchars($message));
}
?>

