<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$action = $_POST['action'] ?? '';

switch ($action) {
    // =======================
    // REGISTER
    // =======================
    case 'register':
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $user_type = $_POST['user_type'] ?? 'patient';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if ($password !== $confirm_password) {
            die("Passwords do not match.");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            die("Email already registered.");
        }
        $check->close();

        // Set status
        $status = ($user_type === 'doctor') ? 'pending' : 'active';

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, user_type, password, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $fullname, $email, $phone, $user_type, $hashed_password, $status);
        if (!$stmt->execute()) {
            die("Error: " . $stmt->error);
        }
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Doctor-specific info
        if ($user_type === 'doctor') {
            $specialization = $_POST['specialization'] ?? '';
            $license_number = $_POST['license_number'] ?? '';
            $hospital = $_POST['hospital'] ?? '';
            $experience = $_POST['years_of_experience'] ?? 0;
            $fee = $_POST['consultation_fee'] ?? 0.00;
            $bio = $_POST['bio'] ?? '';

            $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization, license_number, hospital, years_of_experience, consultation_fee, bio) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssiis', $user_id, $specialization, $license_number, $hospital, $experience, $fee, $bio);
            if (!$stmt->execute()) {
                die("Error inserting doctor info: " . $stmt->error);
            }
            $stmt->close();

            $_SESSION['message'] = "Successfully registered! Waiting for admin approval.";
            $_SESSION['message_type'] = "pending";
            header("Location: index.php#register");
            exit();
        }

        // Set session for normal users
        $_SESSION['user_id'] = $user_id;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['user_type'] = $user_type;

        $_SESSION['message'] = "Successfully registered!";
        $_SESSION['message_type'] = "success";

        if ($user_type === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();

    // =======================
    // LOGIN
    // =======================
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, fullname, email, password, user_type, status FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            die("No account found with this email.");
        }

        $stmt->bind_result($user_id, $fullname, $db_email, $db_password, $user_type, $status);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($password, $db_password)) {
            die("Invalid credentials. Please try again.");
        }

        // Check doctor status
        if ($user_type === 'doctor') {
            if ($status === 'pending') die("Your account is pending admin approval.");
            if ($status === 'rejected') die("Your registration was rejected. Contact admin.");
        }

        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $db_email;
        $_SESSION['user_type'] = $user_type;

        // Redirect
        switch ($user_type) {
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'doctor':
            case 'patient':
            default:
                header("Location: dashboard.php");
                break;
        }
        exit();

    default:
        die("Invalid action.");
}
?>
