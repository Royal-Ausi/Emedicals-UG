<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/blog_debug.log');

session_start();
require 'db.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['fullname'] ?? 'Admin';

// --- HELPER FUNCTIONS ---
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/','-',$text);
    return trim($text,'-');
}
function getCountResult($conn, $query) {
    $result = $conn->query($query);
    return $result ? $result->fetch_row()[0] : 0;
}
function getStats($conn) {
    return [
        'published' => getCountResult($conn, "SELECT COUNT(*) FROM articles WHERE status='published'"),
        'drafts' => getCountResult($conn, "SELECT COUNT(*) FROM articles WHERE status='draft'"),
        'views' => getCountResult($conn, "SELECT COUNT(*) FROM article_views WHERE viewed_at >= NOW() - INTERVAL 30 DAY"),
        'subs' => getCountResult($conn, "SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active=1")
    ];
}
function getArticles($conn) {
    $sql = "SELECT a.*, c.name AS category_name, au.name AS author_name 
            FROM articles a
            LEFT JOIN categories c ON a.category_id=c.category_id
            LEFT JOIN authors au ON a.author_id=au.author_id
            ORDER BY a.published_at DESC";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
function getCategories($conn) {
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
function getAuthors($conn) {
    $result = $conn->query("SELECT * FROM authors ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
function getArticle($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE article_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// --- FORM HANDLER ---
$msg = "";
$debug_info = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log POST data
    error_log("[BLOG DEBUG] POST request received. Data: " . json_encode($_POST));
    
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $slug = slugify($title);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $cat = (int)($_POST['category_id'] ?? 0);
    $author = (int)($_POST['author_id'] ?? 0);
    $img = trim($_POST['featured_image'] ?? '');
    $read = (int)($_POST['read_time'] ?? 5);
    $slider = isset($_POST['is_slider_tip']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $pub_input = $_POST['published_at'] ?? '';
    $pub = $pub_input ? date('Y-m-d H:i:s', strtotime($pub_input)) : ($status === 'published' ? date('Y-m-d H:i:s') : null);

    // Log parsed data
    error_log("[BLOG DEBUG] Parsed: ID=$id, Title=$title, Cat=$cat, Author=$author, Status=$status");
    
    // Detailed validation
    $val_errors = [];
    if (strlen($title) < 10) $val_errors[] = "Title too short (min 10)";
    if (strlen($excerpt) < 50) $val_errors[] = "Excerpt too short (min 50)";
    if (strlen($content) < 100) $val_errors[] = "Content too short (min 100)";
    if ($cat <= 0) $val_errors[] = "Invalid category";
    if ($author <= 0) $val_errors[] = "Invalid author";
    
    if (!empty($val_errors)) {
        $msg = "❌ Validation failed: " . implode("; ", $val_errors);
        error_log("[BLOG DEBUG] Validation errors: " . $msg);
    } else {
        if ($id) {
            // UPDATE
            error_log("[BLOG DEBUG] Attempting UPDATE for article ID=$id");
            $stmt = $conn->prepare(
                "UPDATE articles SET title=?, slug=?, excerpt=?, content=?, featured_image=?, 
                category_id=?, author_id=?, is_slider_tip=?, status=?, read_time=?, 
                published_at=?, updated_at=NOW() WHERE article_id=?"
            );
            if (!$stmt) {
                $msg = " Database error (prepare): " . $conn->error;
                error_log("[BLOG DEBUG] $msg");
            } else {
                $stmt->bind_param("sssssiiisisi", $title, $slug, $excerpt, $content, $img, 
                    $cat, $author, $slider, $status, $read, $pub, $id);
                if (!$stmt->execute()) {
                    $msg = " Database error (update): " . $stmt->error;
                    error_log("[BLOG DEBUG] $msg");
                } else {
                    $msg = "Article updated successfully!";
                    error_log("[BLOG DEBUG] Article ID $id updated successfully");
                }
                $stmt->close();
            }
        } else {
            // INSERT
            error_log("[BLOG DEBUG] Attempting INSERT with: Title=$title, Slug=$slug, Cat=$cat, Author=$author");
            
            // First, try INSERT with created_at
            $stmt = $conn->prepare(
                "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
                category_id, author_id, is_slider_tip, status, read_time, published_at, created_at) 
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            
            // If that fails due to column not existing, try without created_at
            if (!$stmt) {
                error_log("[BLOG DEBUG] First INSERT attempt failed, trying without created_at: " . $conn->error);
                $stmt = $conn->prepare(
                    "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
                    category_id, author_id, is_slider_tip, status, read_time, published_at) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
            }
            
            if (!$stmt) {
                $msg = " Database error (prepare): " . $conn->error;
                error_log("[BLOG DEBUG] $msg");
            } else {
                error_log("[BLOG DEBUG] Binding params with types: sssssiiisis");
                $stmt->bind_param("sssssiiisis", $title, $slug, $excerpt, $content, $img, 
                    $cat, $author, $slider, $status, $read, $pub);
                if (!$stmt->execute()) {
                    $msg = " Database error (insert): " . $stmt->error;
                    error_log("[BLOG DEBUG] Insert failed: $msg");
                } else {
                    $msg = "Article created successfully!";
                    error_log("[BLOG DEBUG] New article created successfully");
                }
                $stmt->close();
            }
        }
    }
    error_log("[BLOG DEBUG] Final message: $msg");
}

// --- DELETE HANDLER ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM article_views WHERE article_id=$id");
    $conn->query("DELETE FROM articles WHERE article_id=$id");
    $msg = " Article deleted successfully.";
}

// --- PAGE MODE ---
$mode = $_GET['mode'] ?? 'list';
$id = $_GET['id'] ?? null;
$article = $id ? getArticle($conn, $id) : null;
$stats = getStats($conn);
$categories = getCategories($conn);
$authors = getAuthors($conn);
$articles = getArticles($conn);

// Test that logging is working
error_log("[BLOG DEBUG] Page loaded. Mode=$mode, ID=$id, Categories=" . count($categories) . ", Authors=" . count($authors));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management | Admin</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/jwldkha3912j0t64aykujm3q5qinokzq23cvrmyg9n726vlc/tinymce/6/tinymce.min.js" referrerpolicy="origin" async defer></script>
    <script>
        // Initialize TinyMCE with error handling
        function initTinyMCE() {
            if (typeof tinymce === 'undefined') {
                console.warn('[TINYMCE] TinyMCE not loaded, will retry...');
                setTimeout(initTinyMCE, 1000);
                return;
            }
            try {
                tinymce.init({ 
                    selector:'#content', 
                    height:400, 
                    plugins:'link image lists code', 
                    toolbar:'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                    skin: 'oxide',
                    content_css: 'default',
                    setup: function(editor) {
                        console.log('[TINYMCE] Editor initialized successfully');
                    },
                    oninit: function() {
                        console.log('[TINYMCE] Init complete');
                    }
                });
                console.log('[TINYMCE] Init call completed');
            } catch(e) {
                console.error('[TINYMCE] Init failed:', e);
            }
        }
        // Try to init, but don't block if it fails
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTinyMCE);
        } else {
            setTimeout(initTinyMCE, 100);
        }
    </script>
    <style>
        
        :root {
            --navy: #003366;
            --navy-700: #003166;
            --accent-blue: #0055aa;
            --muted: #4a5568;
            --bg: #f4f7fb;
            --card: #fff;
            --radius: 10px;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bg);
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--navy);
            color: #fff;
            padding: 20px;
            overflow-y: auto;
            max-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
        }

        .sidebar h1 {
            text-align: center;
            margin-bottom: 3px;
            font-size: 18px;
            margin-top: -10px;
        }

        .sidebar-logo {
            text-align: center;
        
            margin-bottom: 20px;
        }

        .sidebar-logo img {
            height: 50px;
            border-radius: 8px;
            width: auto;
            object-fit: contain;
        }

        .sidebar a {
            display: block;
            padding: 12px 16px;
            margin: 5px 0;
            border-radius: var(--radius);
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar a:hover {
            background: var(--accent-blue);
            transform: translateX(4px);
        }

        .sidebar a.active {
            background: var(--accent-blue);
            box-shadow: 0 2px 8px rgba(0, 85, 170, 0.3);
        }

        .logout-btn {
            background: #c62828;
            color: #fff;
            padding: 12px 16px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 700;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            font-family: inherit;
            font-size: 0.95rem;
        }

        .logout-btn:hover {
            background: #a30f0f;
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.3);
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
            overflow-y: auto;
            max-height: 100vh;
        }

        /* Header Bar */
        .header-bar {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-700) 100%);
            color: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 30px -30px;
            border-radius: 0;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.15);
        }

        .header-bar h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .header-bar > div {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.95rem;
        }

        .header-bar a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .header-bar a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #d4edda;
            border-color: var(--success);
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            border-color: var(--danger);
            color: #721c24;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: 0 3px 10px rgba(0, 51, 102, 0.08);
            text-align: center;
            border-top: 4px solid var(--accent-blue);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.12);
        }

        .stat-card h6 {
            color: var(--muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-card .stat-value {
            font-size: 2.2rem;
            color: var(--navy);
            font-weight: 700;
        }

        /* Table */
        .table-wrapper {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: 0 3px 10px rgba(0, 51, 102, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, var(--navy), var(--navy-700));
            color: #fff;
            padding: 16px;
            text-align: left;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        tbody tr:hover {
            background: #f9fbff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .article-thumb {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge.bg-success {
            background: #d4edda;
            color: #155724;
        }

        .badge.bg-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge.bg-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--accent-blue);
            color: #fff;
        }

        .btn-primary:hover {
            background: #004080;
            box-shadow: 0 4px 12px rgba(0, 85, 170, 0.3);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn + .btn {
            margin-left: 8px;
        }

        /* Forms */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: 0 3px 10px rgba(0, 51, 102, 0.08);
            border: none;
        }

        .card h3 {
            color: var(--navy);
            font-size: 1.5rem;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--navy);
            font-size: 0.95rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 85, 170, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            font-family: 'Courier New', monospace;
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .mb-4 {
            margin-bottom: 30px;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-check {
            margin-bottom: 12px;
        }

        .form-check-input {
            margin-right: 8px;
            cursor: pointer;
            accent-color: var(--accent-blue);
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex !important;
        }

        .modal-dialog {
            background: var(--card);
            border-radius: var(--radius);
            padding: 40px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .modal-icon.success {
            color: var(--success);
        }

        .modal-icon.error {
            color: var(--danger);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 12px;
        }

        .modal-message {
            color: var(--muted);
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                max-height: none;
            }

            .main {
                margin-left: 0;
                padding: 20px;
                max-height: none;
            }

            .header-bar {
                flex-direction: column;
                gap: 15px;
                margin: -20px -20px 20px -20px;
            }

            .header-bar > div {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="includes/images/logo 22.jpg" alt="E-Medicals Logo">
        </div>
        <h1>BLOG</h1>
        <a href="?mode=list" class="active">View Articles</a>
        <a href="?mode=form">New Article</a>
        <a href="admin_dashboard.php">Back to Admin</a>
        <form action="logout.php" method="POST" style="margin-top: auto;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Header -->
        <div class="header-bar">
            <h2>Blog Management</h2>
            <div>
                <span>Welcome, <?= htmlspecialchars($admin_name); ?></span>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?= strpos($msg,'✓')!==false?'success':'danger' ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if($mode==='list'): ?>
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h6>Published</h6>
                    <div class="stat-value"><?= $stats['published'] ?></div>
                </div>
                <div class="stat-card">
                    <h6>Drafts</h6>
                    <div class="stat-value"><?= $stats['drafts'] ?></div>
                </div>
                <div class="stat-card">
                    <h6>Views (30d)</h6>
                    <div class="stat-value"><?= $stats['views'] ?></div>
                </div>
                <div class="stat-card">
                    <h6>Subscribers</h6>
                    <div class="stat-value"><?= $stats['subs'] ?></div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <a href="?mode=form" class="btn btn-primary"><i class="fa fa-plus"></i> Add New Article</a>
            </div>

            <!-- Articles Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($articles as $a): ?>
                            <tr>
                                <td>
                                    <?php if($a['featured_image']): ?>
                                        <img src="<?=htmlspecialchars($a['featured_image'])?>" alt="Article" class="article-thumb">
                                    <?php else: ?>
                                        <div style="width:60px;height:40px;background:#eee;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:20px;"></div>
                                    <?php endif;?>
                                </td>
                                <td><strong><?=htmlspecialchars(substr($a['title'], 0, 50))?></strong></td>
                                <td><?=htmlspecialchars($a['category_name']??'Uncategorized')?></td>
                                <td><?=htmlspecialchars($a['author_name']??'Unknown')?></td>
                                <td><span class="badge bg-<?= $a['status']=='published'?'success':($a['status']=='draft'?'warning':'secondary')?>"><?= ucfirst($a['status'])?></span></td>
                                <td><?= $a['views_count']??0 ?></td>
                                <td><?= date('M d, Y', strtotime($a['published_at'])) ?></td>
                                <td>
                                    <a href="?mode=form&id=<?=$a['article_id']?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="?delete=<?=$a['article_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this article? This cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($mode==='form'): ?>
            <!-- Form -->
            <div class="card" style="padding: 30px;">
                <h3><?= $article ? ' Edit Article' : ' Create New Article' ?></h3>
                
                <!-- DEBUG INFO -->
                <!-- <div style="background: #f0f0f0; border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                    <strong> Debug Information:</strong><br>
                    Categories loaded: <?= count($categories) ?> <br>
                    Authors loaded: <?= count($authors) ?> <br>
                    Form mode: <?= $article ? 'EDIT (ID: ' . htmlspecialchars($article['article_id']) . ')' : 'CREATE' ?> <br>
                    Server: <?= $_SERVER['HTTP_HOST'] ?> <br>
                    Session User: <?= htmlspecialchars($_SESSION['fullname'] ?? 'Not Set') ?> <br>
                    Last message: <?= $msg ? htmlspecialchars($msg) : 'None' ?><br>
                    <br>
                    <strong> Instructions:</strong> Open browser DevTools (F12) and check the Console tab for form submission logs. Submit the form and watch for [FORM DEBUG] messages.
                </div> -->
                
                <form method="post" id="articleForm">
                    <input type="hidden" name="id" value="<?= $article['article_id'] ?? '' ?>">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title'] ?? '') ?>">
                        <small style="color: var(--muted);">Minimum 10 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
                        <small style="color: var(--muted);">Minimum 50 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                        <small style="color: var(--muted);">Minimum 100 characters.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php if (empty($article) || empty($article['category_id'])): ?>
                                    <!-- For new articles, auto-select first category -->
                                    <?php foreach($categories as $idx => $c): ?>
                                        <option value="<?= $c['category_id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- For existing articles, show all and mark selected -->
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?= $c['category_id'] ?>" <?= ($article['category_id'] ?? '') == $c['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Author</label>
                            <select name="author_id" class="form-select" required>
                                <?php if (empty($article) || empty($article['author_id'])): ?>
                                    <!-- For new articles, auto-select first author -->
                                    <?php foreach($authors as $idx => $au): ?>
                                        <option value="<?= $au['author_id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($au['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- For existing articles, show all and mark selected -->
                                    <?php foreach($authors as $au): ?>
                                        <option value="<?= $au['author_id'] ?>" <?= ($article['author_id'] ?? '') == $au['author_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($au['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"> Featured Image URL</label>
                        <input type="url" name="featured_image" class="form-control" value="<?= htmlspecialchars($article['featured_image'] ?? '') ?>" placeholder="https://pexels.com/image.jpg">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Read Time</label>
                            <input type="number" name="read_time" class="form-control" value="<?= htmlspecialchars($article['read_time'] ?? 5) ?>" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Published Date</label>
                            <input type="datetime-local" name="published at" class="form-control" 
                                   value="<?= $article && $article['published_at'] ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_slider_tip" class="form-check-input" id="slider_check" <?= !empty($article['is_slider_tip']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="slider_check">Display in homepage slider</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="draft" id="status_draft" <?= (!$article || ($article['status'] ?? 'draft') == 'draft') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_draft">Draft</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="published" id="status_published" <?= ($article && $article['status'] == 'published') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_published">Published</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="archived" id="status_archived" <?= ($article && $article['status'] == 'archived') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status_archived">Archived</label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" id="saveArticleBtn" class="btn btn-success">Save Article</button>
                        <a href="?mode=list" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Success/Error Modal -->
<div id="resultModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-icon success" id="modalIcon">✓</div>
        <h5 class="modal-title" id="resultModalLabel">Article Saved!</h5>
        <p class="modal-message" id="resultMessage">Your article has been processed successfully.</p>
        <button type="button" class="btn btn-primary" id="modalCloseBtn">Go Back to Articles</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const msg = '<?= addslashes($msg) ?>';
        if (msg) {
            const isSuccess = msg.includes('✓');
            const modal = document.getElementById('resultModal');
            const icon = document.getElementById('modalIcon');
            const title = document.getElementById('resultModalLabel');
            const message = document.getElementById('resultMessage');

            if (isSuccess) {
                icon.className = 'modal-icon success';
                icon.textContent = '✓';
                title.textContent = 'Success!';
                message.textContent = msg;
            } else {
                icon.className = 'modal-icon error';
                icon.textContent = '✕';
                title.textContent = 'Error, Please try again!';
                message.textContent = msg;
            }

            modal.classList.add('show');
            
            document.getElementById('modalCloseBtn').addEventListener('click', function() {
                modal.classList.remove('show');
                if (isSuccess) {
                    window.location.href = '?mode=list';
                }
            });

            // Close on backdrop click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
    });

    // Confirm delete
    function confirmDelete(articleId) {
        return confirm('Are you sure you want to delete this article? This action cannot be undone.');
    }
    
    // Ensure TinyMCE content is saved and prevent double submit
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[FORM DEBUG] Form init script running');
        var form = document.getElementById('articleForm');
        if (!form) {
            console.warn('[FORM DEBUG] Form element not found!');
            return;
        }
        console.log('[FORM DEBUG] Form found, ID: articleForm');
        var btn = document.getElementById('saveArticleBtn');
        console.log('[FORM DEBUG] Submit button found:', btn ? 'yes' : 'no');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[FORM DEBUG] Form submission started');
            
            // Disable submit to prevent double submits immediately
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Saving...';
            }
            
            // Sync TinyMCE content to textarea if it's loaded
            var syncAttempt = 0;
            var maxAttempts = 5;
            
            function syncAndValidate() {
                syncAttempt++;
                console.log('[FORM DEBUG] Sync attempt ' + syncAttempt + '/' + maxAttempts);
                
                try {
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get && tinyMCE.get('content')) {
                        console.log('[FORM DEBUG] TinyMCE editor found, syncing content');
                        tinyMCE.get('content').save();
                    } else if (typeof tinyMCE !== 'undefined') {
                        console.log('[FORM DEBUG] TinyMCE exists but editor not ready, retrying...');
                        if (syncAttempt < maxAttempts) {
                            setTimeout(syncAndValidate, 200);
                            return;
                        }
                    } else {
                        console.log('[FORM DEBUG] TinyMCE not available, proceeding without sync');
                    }
                } catch (err) {
                    console.warn('[FORM DEBUG] TinyMCE sync error (will continue anyway):', err);
                }
                
                // Proceed to validation
                performValidation();
            }
            
            function performValidation() {
                var title = form.querySelector('input[name="title"]').value || '';
                var excerpt = form.querySelector('textarea[name="excerpt"]').value || '';
                var content = form.querySelector('textarea[name="content"]').value || '';
                var category = form.querySelector('select[name="category_id"]').value || '';
                var author = form.querySelector('select[name="author_id"]').value || '';
                
                console.log('[FORM DEBUG] Validation check - Title:', title.length, 'Excerpt:', excerpt.length, 'Content:', content.length, 'Category:', category, 'Author:', author);
                
                var errors = [];
                if (title.trim().length < 10) errors.push('Title must be at least 10 characters');
                if (excerpt.trim().length < 50) errors.push('Excerpt must be at least 50 characters');
                if (content.trim().length < 100) errors.push('Content must be at least 100 characters');
                if (!category || category === '') errors.push('Please select a category');
                if (!author || author === '') errors.push('Please select an author');
                
                if (errors.length > 0) {
                    console.log('[FORM DEBUG] Validation failed:', errors);
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Save Article';
                    }
                    alert('Please fix the following issues:\n\n• ' + errors.join('\n• '));
                    return false;
                }
                
                console.log('[FORM DEBUG] Validation passed, submitting form NOW');
                
                // Submit the form with timeout protection
                submitFormWithTimeout();
            }
            
            function submitFormWithTimeout() {
                console.log('[FORM DEBUG] About to submit form via AJAX');
                
                // Gather form data
                var formData = new FormData(form);
                
                // Submit via AJAX instead of traditional form.submit()
                fetch('?mode=form', {
                    method: 'POST',
                    body: formData,
                    timeout: 30000
                })
                .then(function(response) {
                    console.log('[FORM DEBUG] Server responded with status:', response.status);
                    return response.text();
                })
                .then(function(html) {
                    console.log('[FORM DEBUG] Response received, length:', html.length);
                    
                    // Log a sample of the response for debugging
                    console.log('[FORM DEBUG] Response preview:', html.substring(0, 500));
                    
                    // Extract the message from the response
                    // Look for: const msg = 'MESSAGE HERE';
                    var msgMatch = html.match(/const msg = '([^']*(?:\\'[^']*)*)'/);
                    var msg = msgMatch ? msgMatch[1] : '';
                    
                    // Unescape the message
                    if (msg) {
                        msg = msg.replace(/\\'/g, "'");
                        msg = msg.replace(/\\\"/g, '"');
                        msg = msg.replace(/\\\\/g, '\\');
                    }
                    
                    console.log('[FORM DEBUG] Extracted message:', msg);
                    
                    if (msg) {
                        // Show modal with the message
                        var isSuccess = msg.includes('✓');
                        var modal = document.getElementById('resultModal');
                        var icon = document.getElementById('modalIcon');
                        var title = document.getElementById('resultModalLabel');
                        var message = document.getElementById('resultMessage');
                        var closeBtn = document.getElementById('modalCloseBtn');
                        
                        if (isSuccess) {
                            icon.className = 'modal-icon success';
                            icon.textContent = '✓';
                            title.textContent = 'Success!';
                            message.textContent = msg;
                            console.log('[FORM DEBUG] Showing success modal');
                        } else {
                            icon.className = 'modal-icon error';
                            icon.textContent = '✕';
                            title.textContent = 'Error!';
                            message.textContent = msg;
                            console.log('[FORM DEBUG] Showing error modal');
                        }
                        
                        modal.classList.add('show');
                        
                        // Attach close button handler
                        closeBtn.onclick = function() {
                            modal.classList.remove('show');
                            if (isSuccess) {
                                location.href = '?mode=list';
                            }
                        };
                        
                        // Reset button state
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = 'Save Article';
                        }
                    } else {
                        console.log('[FORM DEBUG] No message in response - showing debug alert');
                        alert('Form submitted but no confirmation message received. The form may have worked. Please refresh the page to check.');
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = 'Save Article';
                        }
                    }
                })
                .catch(function(error) {
                    console.error('[FORM DEBUG] AJAX Error:', error);
                    alert('Error submitting form: ' + error);
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Save Article';
                    }
                });
            }
            
            syncAndValidate();
        });
        console.log('[FORM DEBUG] Form event listener attached');
    });
</script>
</body>
</html>

