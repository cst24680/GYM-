<?php
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['User_type'];

// -------------------- Predefined Admin ---------------------
if ($email === 'admin@gmail.com' && $password === 'admin' && $role === 'admin') {
    session_start();
    $_SESSION['email'] = $email;
    $_SESSION['user_type'] = 'admin';

    header("Location: admin_dashboard.php");
    exit();
}

// -------------------- General Login for member/trainer/dietician ---------------------
$sql = "SELECT * FROM login WHERE Email = '$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['Password'])) {
        if ($role === $user['User_type']) {
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $role;

            // Redirect based on role
            if ($role === 'member') {
                header("Location: member_dashboard.php");
            } elseif ($role === 'trainer') {
                header("Location: trainer_dashboard.php");
            } elseif ($role === 'dietician') {
                header("Location: dietician_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('User type mismatch.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Incorrect password.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No user found with this email.'); window.history.back();</script>";
}

mysqli_close($conn);
?>
