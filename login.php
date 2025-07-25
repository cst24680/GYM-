<?php
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Query to find the user by email
$sql = "SELECT * FROM login WHERE Email = '$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verify the hashed password
    if (password_verify($password, $user['Password'])) {
        session_start();
        $_SESSION['email'] = $email; // ✅ Email will be used to link with member_registration
        $_SESSION['user_type'] = $user['User_type'];

        // ✅ No need to store Login_id anymore
        // $_SESSION['Login_id'] = $user['Login_id']; // ❌ remove this

        // Redirect to member dashboard
        header('Location: member_dashboard.php');
        exit();
    } else {
        echo "<script>alert('Incorrect password!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No user found with that email!'); window.history.back();</script>";
}

mysqli_close($conn);
?>
