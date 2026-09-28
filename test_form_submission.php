<?php
/**
 * Test script to simulate form submission
 * Run from command line: php test_form_submission.php
 */

require 'db.php';

// Clear the log first
@unlink(__DIR__ . '/blog_debug.log');

echo "=== Blog Form Submission Test ===\n\n";

// Simulate data from a new article submission
$post_data = [
    'id' => '',
    'title' => 'Test Article - Complete Guide to PHP',  // 37 chars - valid
    'excerpt' => 'This is a comprehensive test excerpt for a blog post about PHP. It covers all the fundamentals.',  // 97 chars - valid
    'content' => 'This is a test content for the article. It includes multiple paragraphs to ensure we have enough content. The content should be at least 100 characters long to pass validation. Here is some more content to make sure we meet the requirement.',  // 249 chars - valid
    'category_id' => '1',  // Will set appropriately
    'author_id' => '1',    // Will set appropriately
    'featured_image' => 'https://example.com/test.jpg',
    'read_time' => '5',
    'published_at' => date('Y-m-d\TH:i'),
    'is_slider_tip' => '0',
    'status' => 'draft'
];

// Get first category and author for valid IDs
$cat_result = $conn->query("SELECT category_id FROM categories LIMIT 1");
$auth_result = $conn->query("SELECT author_id FROM authors LIMIT 1");

if ($cat_result && $cat_result->num_rows > 0) {
    $cat_row = $cat_result->fetch_assoc();
    $post_data['category_id'] = $cat_row['category_id'];
    echo "✓ Found category ID: " . $post_data['category_id'] . "\n";
} else {
    echo "✗ No categories found\n";
    exit(1);
}

if ($auth_result && $auth_result->num_rows > 0) {
    $auth_row = $auth_result->fetch_assoc();
    $post_data['author_id'] = $auth_row['author_id'];
    echo "✓ Found author ID: " . $post_data['author_id'] . "\n";
} else {
    echo "✗ No authors found\n";
    exit(1);
}

echo "\n--- Simulating Form Submission ---\n";
echo json_encode($post_data, JSON_PRETTY_PRINT) . "\n\n";

// Simulate the validation logic from adminBlogs.php
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/','-',$text);
    return trim($text,'-');
}

$id = $post_data['id'] ?? null;
$title = trim($post_data['title'] ?? '');
$slug = slugify($title);
$excerpt = trim($post_data['excerpt'] ?? '');
$content = trim($post_data['content'] ?? '');
$cat = (int)($post_data['category_id'] ?? 0);
$author = (int)($post_data['author_id'] ?? 0);
$img = trim($post_data['featured_image'] ?? '');
$read = (int)($post_data['read_time'] ?? 5);
$slider = isset($post_data['is_slider_tip']) ? 1 : 0;
$status = $post_data['status'] ?? 'draft';
$pub_input = $post_data['published_at'] ?? '';
$pub = $pub_input ? date('Y-m-d H:i:s', strtotime($pub_input)) : ($status === 'published' ? date('Y-m-d H:i:s') : null);

echo "Parsed values:\n";
echo "  Title: '$title' (length: " . strlen($title) . ")\n";
echo "  Slug: '$slug'\n";
echo "  Excerpt: '$excerpt' (length: " . strlen($excerpt) . ")\n";
echo "  Content length: " . strlen($content) . " chars\n";
echo "  Category: $cat\n";
echo "  Author: $author\n";
echo "  Status: $status\n";
echo "  Published: " . ($pub ? $pub : 'null') . "\n\n";

// Validation
$val_errors = [];
if (strlen($title) < 10) $val_errors[] = "Title too short (min 10)";
if (strlen($excerpt) < 50) $val_errors[] = "Excerpt too short (min 50)";
if (strlen($content) < 100) $val_errors[] = "Content too short (min 100)";
if ($cat <= 0) $val_errors[] = "Invalid category";
if ($author <= 0) $val_errors[] = "Invalid author";

if (!empty($val_errors)) {
    echo "✗ Validation failed:\n";
    foreach ($val_errors as $err) {
        echo "  - $err\n";
    }
    exit(1);
} else {
    echo "✓ Validation passed\n\n";
}

// Now test the INSERT
echo "--- Testing INSERT ---\n";
$stmt = $conn->prepare(
    "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
    category_id, author_id, is_slider_tip, status, read_time, published_at, created_at) 
    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
);

if (!$stmt) {
    echo "✗ Prepare failed (with created_at): " . $conn->error . "\n";
    echo "Trying without created_at...\n";
    $stmt = $conn->prepare(
        "INSERT INTO articles(title, slug, excerpt, content, featured_image, 
        category_id, author_id, is_slider_tip, status, read_time, published_at) 
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        echo "✗ Prepare failed (without created_at): " . $conn->error . "\n";
        exit(1);
    } else {
        echo "✓ Prepare successful (without created_at)\n";
        echo "Binding 11 parameters: sssssiiisis\n";
        $stmt->bind_param("sssssiiisis", $title, $slug, $excerpt, $content, $img, 
            $cat, $author, $slider, $status, $read, $pub);
    }
} else {
    echo "✓ Prepare successful (with created_at)\n";
    echo "Binding 11 parameters: sssssiiisis\n";
    $stmt->bind_param("sssssiiisis", $title, $slug, $excerpt, $content, $img, 
        $cat, $author, $slider, $status, $read, $pub);
}

if (!$stmt->execute()) {
    echo "✗ Execute failed: " . $stmt->error . "\n";
    $stmt->close();
    exit(1);
} else {
    $insert_id = $stmt->insert_id;
    echo "✓ Insert successful! New article ID: $insert_id\n";
    $stmt->close();
}

// Verify the article was created
$verify = $conn->query("SELECT article_id, title FROM articles WHERE article_id = $insert_id");
if ($verify && $verify->num_rows > 0) {
    $row = $verify->fetch_assoc();
    echo "✓ Verification: Article found in database\n";
    echo "  ID: " . $row['article_id'] . "\n";
    echo "  Title: " . $row['title'] . "\n";
    
    // Clean up - delete the test article
    $conn->query("DELETE FROM articles WHERE article_id = $insert_id");
    echo "\n✓ Test article deleted (cleanup)\n";
} else {
    echo "✗ Verification failed: Article not found in database\n";
    exit(1);
}

echo "\n=== All Tests Passed ===\n";
?>
