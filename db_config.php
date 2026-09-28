<?php
require_once __DIR__ . '/includes/env.php';

$servername = env_value('DB_HOST', 'localhost');
$username   = env_value('DB_USERNAME', 'root');
$password   = env_value('DB_PASSWORD', '');
$database   = env_value('DB_DATABASE', 'telehealth_db');

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
