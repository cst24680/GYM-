<?php
// Start session
session_start();
include "db.php";

$msg = "";

// Predefined admin credentials
$admin_email = "admin@gmail.com";
$admin_password = "admin123"; // Change to your secure password

// Handle login form submission
if (isset($_POST['login'])) {
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];

    // --- Check predefined admin first ---
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['User_type'] = 'admin';
        $_SESSION['email'] = $admin_email;
        header("Location: admin_dashboard.php");
        exit();
    }

    // --- Otherwise, check login table ---
    $sql = "SELECT * FROM login WHERE Email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Verify MD5 password
        if ($row['Password'] === md5($password)) {
            $_SESSION['user_id'] = $row['Login_id'];
            $_SESSION['role'] = strtolower($row['User_type']);
            $_SESSION['email'] = $row['Email'];

            // Fetch IDs from respective tables for specific roles
            if ($_SESSION['role'] === 'member') {
                $memRes = mysqli_query($conn, "SELECT Mem_id FROM member_registration WHERE Mem_email='$email' LIMIT 1");
                if ($memRes && mysqli_num_rows($memRes) === 1) {
                    $memRow = mysqli_fetch_assoc($memRes);
                    $_SESSION['Mem_id'] = $memRow['Mem_id'];
                }
            } elseif ($_SESSION['role'] === 'trainer') {
                $trainerRes = mysqli_query($conn, "SELECT Trainer_id FROM trainer WHERE Email='$email' LIMIT 1");
                if ($trainerRes && mysqli_num_rows($trainerRes) === 1) {
                    $trainerRow = mysqli_fetch_assoc($trainerRes);
                    $_SESSION['Trainer_id'] = $trainerRow['Trainer_id'];
                }
            } elseif ($_SESSION['role'] === 'dietician') {
                $dietRes = mysqli_query($conn, "SELECT Dietician_id FROM dietician WHERE Email='$email' LIMIT 1");
                if ($dietRes && mysqli_num_rows($dietRes) === 1) {
                    $dietRow = mysqli_fetch_assoc($dietRes);
                    $_SESSION['Dietician_id'] = $dietRow['Dietician_id'];
                }
            }

            // Redirect based on role
            switch ($_SESSION['role']) {
                case 'trainer':
                    header("Location: trainer_dashboard.php");
                    break;
                case 'dietician':
                    header("Location: dietician_dashboard.php");
                    break;
                case 'member':
                    header("Location: member_dashboard.php");
                    break;
                default:
                    $msg = "Unknown role. Please contact admin.";
                    session_destroy();
                    exit;
            }
            exit;
        } else {
            $msg = "Invalid password!";
        }
    } else {
        $msg = "No account found with that email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Gym Management - Login</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url("images/gym-bg.jpg") center/cover no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0px 8px 25px rgba(0,0,0,0.5);
            padding: 40px;
            width: 350px;
            color: #fff;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #f87800ff;
            font-size: 24px;
            font-weight: bold;
        }
        .login-container label {
            font-weight: 500;
            display: block;
            margin-top: 12px;
        }
        .login-container input {
            width: 100%;
            padding: 13px 3px ; /* leave space for eye icon */
            margin-top: 6px;
            border: none;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .login-container input:focus {
            box-shadow: 0 0 8px #ff4da6;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #ff7f4dff;
        }
        .toggle-password:hover {
            color: #ff4d4d;
        }
        .login-container button {
            width: 100%;
            background: linear-gradient(90deg, #ff8800ff, #fc5000ff);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }
        .login-container button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px #f32a06ff;
        }
        .error {
            background: rgba(255, 0, 0, 0.1);
            color: #ff4d4d;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .extra-links {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        .extra-links a {
            color: #ff7f4dff;
            text-decoration: none;
        }
        .extra-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>LOGIN</h2>
    <?php if ($msg != ""): ?>
        <div class="error"><?php echo $msg; ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <label>Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
            <i class="fa-solid fa-eye toggle-password" id="toggleIcon" onclick="togglePassword()"></i>
        </div>

        <button type="submit" name="login">Login</button>
    </form>
    <div class="extra-links">
        <p>Don’t have an account? <a href="register.php">Sign Up</a></p>
    </div>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash"); // closed eye (font awesome icons used)
    } else {
        pass.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
