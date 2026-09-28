<?php
/**
 * Blog System Auto-Initialization
 * This script will automatically create required database tables if they don't exist
 */

session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require 'db.php';

// Function to check and create tables
function initializeBlogTables($conn) {
    $errors = [];
    
    // Create categories table
    $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql_categories)) {
        $errors[] = "Categories table: " . $conn->error;
    }

    // Create authors table
    $sql_authors = "CREATE TABLE IF NOT EXISTS authors (
        author_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        credentials VARCHAR(255),
        bio TEXT,
        avatar_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql_authors)) {
        $errors[] = "Authors table: " . $conn->error;
    }

    // Create articles table
    $sql_articles = "CREATE TABLE IF NOT EXISTS articles (
        article_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt VARCHAR(500),
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(255),
        category_id INT,
        author_id INT,
        read_time INT DEFAULT 5,
        is_slider_tip TINYINT DEFAULT 0,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        views_count INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE SET NULL,
        INDEX (slug),
        INDEX (status),
        INDEX (published_at)
    )";
    if (!$conn->query($sql_articles)) {
        $errors[] = "Articles table: " . $conn->error;
    }

    // Create article_views table
    $sql_views = "CREATE TABLE IF NOT EXISTS article_views (
        view_id INT AUTO_INCREMENT PRIMARY KEY,
        article_id INT NOT NULL,
        user_id INT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX (article_id),
        INDEX (viewed_at)
    )";
    if (!$conn->query($sql_views)) {
        $errors[] = "Article views table: " . $conn->error;
    }

    // Create newsletter_subscribers table
    $sql_subs = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        subscriber_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) UNIQUE NOT NULL,
        fullname VARCHAR(100),
        is_active TINYINT DEFAULT 1,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at TIMESTAMP NULL,
        INDEX (email)
    )";
    if (!$conn->query($sql_subs)) {
        $errors[] = "Newsletter subscribers table: " . $conn->error;
    }

    // Insert default categories
    $default_cats = [
        ['Health Tips', 'health-tips', 'General health and wellness tips'],
        ['Medical News', 'medical-news', 'Latest medical and healthcare news'],
        ['First Aid', 'first-aid', 'First aid and emergency procedures'],
        ['Nutrition', 'nutrition', 'Nutrition and diet guidance'],
        ['Mental Health', 'mental-health', 'Mental health and wellbeing'],
        ['Disease Info', 'disease-info', 'Information about diseases and conditions']
    ];

    foreach ($default_cats as $cat) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $cat[0], $cat[1], $cat[2]);
        $stmt->execute();
        $stmt->close();
    }

    // Insert default author
    $author_name = 'System Administrator';
    $author_email = 'admin@telehealth.com';
    $author_cred = 'Healthcare Professional';
    $author_bio = 'Administrator account for system-generated content';
    
    $stmt = $conn->prepare("INSERT IGNORE INTO authors (name, email, credentials, bio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $author_name, $author_email, $author_cred, $author_bio);
    $stmt->execute();
    $stmt->close();

    // Insert sample article
    $title = 'Welcome to TeleHealth Blog';
    $slug = 'welcome-to-telehealth-blog';
    $excerpt = 'Discover our latest health tips and medical insights to improve your wellbeing.';
    $content = '<p>Welcome to the TeleHealth Blog! This is your go-to source for reliable health information, medical news, and wellness tips from healthcare professionals.</p><p>We are committed to providing you with accurate, up-to-date information to help you make informed decisions about your health.</p>';
    $image = 'https://via.placeholder.com/800x400?text=Welcome+to+TeleHealth';
    $cat_id = 1;
    $auth_id = 1;
    $status = 'published';
    $read_time = 5;
    
    $stmt = $conn->prepare("INSERT IGNORE INTO articles (title, slug, excerpt, content, featured_image, category_id, author_id, status, read_time, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssiiis", $title, $slug, $excerpt, $content, $image, $cat_id, $auth_id, $status, $read_time);
    $stmt->execute();
    $stmt->close();

    return $errors;
}

// Process initialization
$init_message = '';
$init_errors = [];

if ($_POST['action'] === 'init') {
    $init_errors = initializeBlogTables($conn);
    if (empty($init_errors)) {
        $init_message = "✓ Blog system initialized successfully! Redirecting to Blog Management...";
        header("Refresh: 2; url=adminBlogs.php");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); border-radius: 12px; }
    </style>
    <title>Blog System Setup</title>
</head>
<body>
    <div class="card" style="max-width: 600px; width: 100%; margin: 20px;">
        <div class="card-body p-5">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1>📰 Blog System Setup</h1>
            </div>

            <?php if ($init_message): ?>
                <div class="alert alert-success"><?= $init_message ?></div>
            <?php elseif (!empty($init_errors)): ?>
                <div class="alert alert-danger">
                    <strong>Initialization Errors:</strong>
                    <ul>
                        <?php foreach ($init_errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p style="font-size: 16px; margin-bottom: 20px;">
                    This setup wizard will create all necessary database tables for the Blog Management system. Click the button below to initialize.
                </p>

                <div class="alert alert-info">
                    <strong>✓ Tables to be created:</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                        <li>categories</li>
                        <li>authors</li>
                        <li>articles</li>
                        <li>article_views</li>
                        <li>newsletter_subscribers</li>
                    </ul>
                </div>

                <form method="post" style="margin-top: 30px;">
                    <input type="hidden" name="action" value="init">
                    <button type="submit" class="btn btn-lg btn-primary w-100" style="font-size: 16px; padding: 12px;">
                        🚀 Initialize Blog System
                    </button>
                </form>
            <?php endif; ?>

            <hr style="margin: 30px 0;">
            <p style="text-align: center; font-size: 14px; color: #666;">
                <a href="admin_dashboard.php">← Back to Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
