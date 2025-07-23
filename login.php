<?php
include 'db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Look up user by email
$sql = "SELECT * FROM Login WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // Password matches
        session_start();
        $_SESSION['email'] = $email;
        $_SESSION['user_type'] = $user['User_type'];

        echo "<script>alert('Login Successful as {$user['User_type']}'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Incorrect password!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No user found with that email!'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>

