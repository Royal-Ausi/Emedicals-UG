<?php
/**
 * Test script to simulate an actual HTTP POST request
 * This helps verify if the server-side logging is working
 */

$post_data = [
    'id' => '',
    'title' => 'HTTP Test Article - Testing Form Submission',
    'excerpt' => 'This is a test excerpt for the HTTP POST test. It must be at least 50 characters long.',
    'content' => 'This is test content for the HTTP POST request. It must be at least 100 characters long. Let me add more content to ensure we meet the requirement. The form submission is being tested.',
    'category_id' => '1',
    'author_id' => '1',
    'featured_image' => 'https://example.com/test.jpg',
    'read_time' => '5',
    'published_at' => date('Y-m-d\TH:i'),
    'is_slider_tip' => '0',
    'status' => 'draft'
];

echo "Testing HTTP POST to adminBlogs.php\n";
echo "====================================\n\n";

// Use file_get_contents with stream context to make a POST request
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($post_data),
        'timeout' => 10
    ]
]);

$url = 'http://localhost/sys4/adminBlogs.php?mode=form';
echo "POST to: $url\n";
echo "Data: " . json_encode($post_data) . "\n\n";

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "✗ POST request failed\n";
    if (isset($http_response_header)) {
        echo "Response headers:\n";
        print_r($http_response_header);
    }
} else {
    echo "✓ POST request succeeded\n";
    echo "Response length: " . strlen($response) . " bytes\n";
    
    // Check if response contains success or error message
    if (strpos($response, '✓') !== false) {
        echo "✓ Success message found in response\n";
    } elseif (strpos($response, '❌') !== false) {
        echo "✗ Error message found in response\n";
    } else {
        echo "? No success/error message found in response\n";
    }
    
    // Check if it's a redirect (session not valid)
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Location:') !== false) {
                echo "! Page redirected: $header\n";
                echo "! This suggests the session check failed\n";
            }
        }
    }
    
    // Save response to file for inspection
    file_put_contents(__DIR__ . '/test_response.html', $response);
    echo "Response saved to test_response.html\n";
}

echo "\n--- Debug Log Check ---\n";
if (file_exists(__DIR__ . '/blog_debug.log')) {
    $log_content = file_get_contents(__DIR__ . '/blog_debug.log');
    $log_lines = explode("\n", trim($log_content));
    echo "Last 10 log entries:\n";
    $last_lines = array_slice($log_lines, -10);
    foreach ($last_lines as $line) {
        if (!empty($line)) {
            echo "  $line\n";
        }
    }
} else {
    echo "Debug log file not found\n";
}
?>
