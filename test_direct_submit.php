<?php
/**
 * Direct form submission test - bypasses browser validation
 * This tests if the server-side handler works without TinyMCE
 */

session_start();
require 'db.php';

// Check if already logged in, if not set a test session
if (!isset($_SESSION['user_id'])) {
    // Get admin user from database
    $admin = $conn->query("SELECT * FROM users WHERE user_type='admin' LIMIT 1");
    if ($admin && $admin->num_rows > 0) {
        $user = $admin->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['fullname'] = $user['fullname'];
        echo "✓ Session set for admin user: " . $user['fullname'] . "\n";
    } else {
        die("✗ No admin user found in database\n");
    }
}

echo "Testing Direct Form Submission\n";
echo "====================================\n\n";

// Prepare test data
$timestamp = time();
$test_data = [
    'id' => '',
    'title' => 'Direct Submit Test ' . $timestamp . ' - No TinyMCE Required',
    'excerpt' => 'This article was submitted directly without using TinyMCE editor. Testing if server handler works correctly.',
    'content' => 'This is the main content of the test article submitted directly. It bypasses any browser-based form validation or TinyMCE transformation issues. The form submission goes directly to PHP code for processing.',
    'category_id' => '1',
    'author_id' => '1',
    'featured_image' => 'https://example.com/test.jpg',
    'read_time' => '5',
    'published_at' => date('Y-m-d\TH:i'),
    'is_slider_tip' => '0',
    'status' => 'draft'
];

echo "Test Data:\n";
echo "  Title: " . $test_data['title'] . "\n";
echo "  Excerpt: " . substr($test_data['excerpt'], 0, 50) . "...\n";
echo "  Content: " . substr($test_data['content'], 0, 50) . "...\n";
echo "  Category ID: " . $test_data['category_id'] . "\n";
echo "  Author ID: " . $test_data['author_id'] . "\n\n";

// Simulate the form POST by setting $_POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = $test_data;

// Now include and run the form handler logic from adminBlogs.php
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/','-',$text);
    return trim($text,'-');
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    echo "Processing Form Data:\n";
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
        echo "✗ Validation failed:\n";
        foreach ($val_errors as $err) {
            echo "  - $err\n";
        }
    } else {
        echo "✓ Validation passed\n";
        
        if ($id) {
            // This would be UPDATE - skip for now
            $msg = "❌ Update mode not tested";
        } else {
            // INSERT
            echo "Attempting INSERT into database...\n";
            error_log("[BLOG DEBUG] Attempting INSERT with: Title=$title, Slug=$slug, Cat=$cat, Author=$author");
            
            $stmt = $conn->prepare(
                "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
                category_id, author_id, is_slider_tip, status, read_time, published_at, created_at) 
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            
            if (!$stmt) {
                error_log("[BLOG DEBUG] First INSERT attempt failed, trying without created_at: " . $conn->error);
                $stmt = $conn->prepare(
                    "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
                    category_id, author_id, is_slider_tip, status, read_time, published_at) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
            }
            
            if (!$stmt) {
                $msg = "❌ Database error (prepare): " . $conn->error;
                error_log("[BLOG DEBUG] $msg");
                echo "✗ $msg\n";
            } else {
                error_log("[BLOG DEBUG] Binding params with types: sssssiiisis");
                $stmt->bind_param("sssssiiisis", $title, $slug, $excerpt, $content, $img, 
                    $cat, $author, $slider, $status, $read, $pub);
                if (!$stmt->execute()) {
                    $msg = "❌ Database error (insert): " . $stmt->error;
                    error_log("[BLOG DEBUG] Insert failed: $msg");
                    echo "✗ $msg\n";
                } else {
                    $insert_id = $stmt->insert_id;
                    $msg = "✓ Article created successfully!";
                    error_log("[BLOG DEBUG] New article created successfully with ID: $insert_id");
                    echo "✓ Article created successfully with ID: $insert_id\n";
                    
                    // Verify it was created
                    $verify = $conn->query("SELECT article_id, title FROM articles WHERE article_id = $insert_id");
                    if ($verify && $verify->num_rows > 0) {
                        $row = $verify->fetch_assoc();
                        echo "✓ Verified in database: ID=" . $row['article_id'] . ", Title=" . $row['title'] . "\n";
                    }
                }
                $stmt->close();
            }
        }
    }
    error_log("[BLOG DEBUG] Final message: $msg");
}

echo "\n=== Result ===\n";
echo ($msg ?: "No message") . "\n";
?>
