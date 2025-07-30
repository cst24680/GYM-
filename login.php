<?php
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['User_type'];

// -------------------- Predefined Trainer ---------------------
if ($email === 'trainer@gmail.com' && $password === 'trainer' && $role === 'trainer') {
    session_start();
    $_SESSION['email'] = $email;
    $_SESSION['user_type'] = 'trainer';

    // Ensure trainer exists
    $check = mysqli_query($conn, "SELECT * FROM trainer WHERE Trainer_email = '$email'");
    if (mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "INSERT INTO trainer (Trainer_id, Trainer_name, Trainer_email)
                             VALUES (1, 'trainer', '$email')");
    }

    header("Location: trainer_dashboard.php");
    exit();
}

// -------------------- Predefined Dietician ---------------------
if ($email === 'dietician@gmail.com' && $password === 'dietician' && $role === 'dietician') {
    session_start();
    $_SESSION['email'] = $email;
    $_SESSION['user_type'] = 'dietician';

    // Ensure dietician exists
    $check = mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_email = '$email'");
    if (mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "INSERT INTO dietician (Dietician_id, Dietician_name, Dietician_email)
                             VALUES (1, 'dietician', '$email')");
    }

    header("Location: dietician_dashboard.php");
    exit();
}

// -------------------- Predefined Admin ---------------------
if ($email === 'admin@gmail.com' && $password === 'admin' && $role === 'admin') {
    session_start();
    $_SESSION['email'] = $email;
    $_SESSION['user_type'] = 'admin';

    // No need to insert into any table, admin is predefined
    header("Location: admin_dashboard.php");
    exit();
}

// -------------------- General Login ---------------------
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
            } elseif ($role === 'admin') {
                header("Location: admin_dashboard.php");
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
