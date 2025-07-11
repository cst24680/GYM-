<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if(is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $membership_type = sanitize_input($_POST['membership_type']);
    $height = sanitize_input($_POST['height']);
    $weight = sanitize_input($_POST['weight']);
    $fitness_goals = sanitize_input($_POST['fitness_goals']);

    // Validation
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Please fill all required fields.';
    } elseif($password != $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif(strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if($stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            try {
                $conn->beginTransaction();
                
                // Insert into users table
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, USER_MEMBER]);
                $user_id = $conn->lastInsertId();
                
                // Insert into member_details
                $stmt = $conn->prepare("INSERT INTO member_details (user_id, membership_type, join_date, expiry_date, height, weight, fitness_goals) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?, ?, ?)");
                $stmt->execute([$user_id, $membership_type, $height, $weight, $fitness_goals]);
                
                $conn->commit();
                $success = 'Registration successful! You can now login.';
                header("refresh:2;url=login.php");
            } catch(PDOException $e) {
                $conn->rollBack();
                $error = 'Registration failed. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Registration form remains similar but with added fields for height, weight, fitness goals -->