<?php
require_once 'db.php';

echo "<!DOCTYPE html><html><head><title>Blog Setup Test</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body>";
echo "<div class='container my-5'><h1>🔍 Blog Setup Verification</h1><hr>";

// Test 1: Database Connection
echo "<h3>1️⃣ Database Connection</h3>";
if ($conn->ping()) {
    echo "<div class='alert alert-success'>✓ Connected to database: " . $conn->get_server_info() . "</div>";
} else {
    echo "<div class='alert alert-danger'>✗ Database connection failed</div>";
}

// Test 2: Check tables
echo "<h3>2️⃣ Required Tables</h3>";
$tables = ['articles', 'categories', 'authors', 'article_views', 'newsletter_subscribers'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-success'>✓ Table '$table' exists</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠ Table '$table' missing - Run create_blog_tables.sql</div>";
    }
}

// Test 3: Check published articles
echo "<h3>3️⃣ Articles Status</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM articles");
$row = $result->fetch_assoc();
echo "<div class='alert alert-info'>📊 Total articles: <strong>" . $row['total'] . "</strong></div>";

$result = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status='published'");
$row = $result->fetch_assoc();
echo "<div class='alert alert-info'>📢 Published articles: <strong>" . $row['total'] . "</strong></div>";

// Test 4: Sample article
echo "<h3>4️⃣ Sample Articles</h3>";
$result = $conn->query("SELECT article_id, title, status, published_at FROM articles ORDER BY article_id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table class='table table-striped'><thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Published</th></tr></thead><tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['article_id'] . "</td><td>" . htmlspecialchars($row['title']) . "</td><td><span class='badge bg-" . ($row['status']=='published'?'success':'warning') . "'>" . $row['status'] . "</span></td><td>" . ($row['published_at'] ?? 'NULL') . "</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='alert alert-warning'>⚠ No articles in database - Create one in adminBlogs.php</div>";
}

// Test 5: Check categories
echo "<h3>5️⃣ Categories</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM categories");
$row = $result->fetch_assoc();
echo "<div class='alert alert-info'>📂 Total categories: <strong>" . $row['total'] . "</strong></div>";

// Test 6: Check authors
echo "<h3>6️⃣ Authors</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM authors");
$row = $result->fetch_assoc();
echo "<div class='alert alert-info'>👤 Total authors: <strong>" . $row['total'] . "</strong></div>";

echo "<hr><div class='alert alert-info'><strong>Next Steps:</strong><br>";
echo "1. Visit <a href='blogs.php'>blogs.php</a> to see published articles<br>";
echo "2. Visit <a href='adminBlogs.php'>adminBlogs.php</a> to create new articles<br>";
echo "3. Add <strong>?debug=1</strong> to blogs.php URL for debugging info<br>";
echo "</div>";
echo "</div></body></html>";
?>
