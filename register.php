<?php
include "db.php"; 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = trim(mysqli_real_escape_string($conn, $_POST['Mem_name']));
    $age        = (int) $_POST['Mem_age'];
    $email      = trim(mysqli_real_escape_string($conn, $_POST['Mem_email']));
    $height     = (int) $_POST['Height'];
    $weight     = (int) $_POST['Weight'];
    $dob        = $_POST['Mem_dob'];
    $phone      = trim(mysqli_real_escape_string($conn, $_POST['Mem_phno']));
    $gender     = mysqli_real_escape_string($conn, $_POST['Gender']);
    $goal_type  = mysqli_real_escape_string($conn, $_POST['Goal_type']);
    $status     = "Active";
    $plain_pass = $_POST['Mem_pass']; 

    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $message = "<p style='color:red;'>Name should only contain letters and spaces.</p>";
    } elseif ($age < 1 || $age > 120) {
        $message = "<p style='color:red;'>Age must be between 1 and 120.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Invalid email format.</p>";
    } elseif ($height < 50 || $height > 300) {
        $message = "<p style='color:red;'>Height must be between 50cm and 300cm.</p>";
    } elseif ($weight < 10 || $weight > 500) {
        $message = "<p style='color:red;'>Weight must be between 10kg and 500kg.</p>";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "<p style='color:red;'>Phone number must be exactly 10 digits.</p>";
    } elseif (strlen($plain_pass) < 6) {
        $message = "<p style='color:red;'>Password must be at least 6 characters long.</p>";
    } else {
        $password = password_hash($plain_pass, PASSWORD_DEFAULT);
        $height_m = $height / 100;
        $bmi = ($height_m > 0) ? round($weight / ($height_m * $height_m), 2) : 0;

        if ($goal_type === "Weight Loss") {
            $plan_name = "Weight Loss Plan";
        } elseif ($goal_type === "Muscle Gain") {
            $plan_name = "Muscle Gain Plan";
        } else {
            $plan_name = "General Fitness Plan";
        }

        $trainer_id = null;
        $trainerQuery = mysqli_query($conn, "SELECT Trainer_id FROM trainer WHERE Speciality = '$goal_type' LIMIT 1");
        if ($trainerQuery && mysqli_num_rows($trainerQuery) > 0) {
            $trainerData = mysqli_fetch_assoc($trainerQuery);
            $trainer_id = (int)$trainerData['Trainer_id'];
        }

        $dietician_id = null;
        $dieticianQuery = mysqli_query($conn, "SELECT Dietician_id FROM dietician ORDER BY RAND() LIMIT 1");
        if ($dieticianQuery && mysqli_num_rows($dieticianQuery) > 0) {
            $dieticianData = mysqli_fetch_assoc($dieticianQuery);
            $dietician_id = (int)$dieticianData['Dietician_id'];
        }

        $trainer_id_sql   = isset($trainer_id) ? $trainer_id : "NULL";
        $dietician_id_sql = isset($dietician_id) ? $dietician_id : "NULL";

        $sql = "INSERT INTO member_registration 
            (Mem_name, Mem_age, Mem_email, Height, Weight, Mem_dob, Mem_phno, Gender, Goal_type, Trainer_id, Dietician_id, BMI, Plan_name, Mem_status, Mem_pass) 
            VALUES 
            ('$name', $age, '$email', $height, $weight, '$dob', '$phone', '$gender', '$goal_type', 
            $trainer_id_sql, $dietician_id_sql, $bmi, '$plan_name', '$status', '$password')";

        if (mysqli_query($conn, $sql)) {
            $login_sql = "INSERT INTO login (Email, Password, User_type) 
                          VALUES ('$email', MD5('$plain_pass'), 'member')";
            mysqli_query($conn, $login_sql);

            header("Location: login.php");
            exit;
        } else {
            $message = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Member Registration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                    url("images/gym-bg.jpg") center/cover no-repeat;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .container {
     background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(12px);
       border-radius: 16px;
        box-shadow: 0px 8px 25px rgba(0,0,0,0.5);
         padding: 40px; width: 350px;
          color: #fff;
    } 
    .container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #ffffffff;
    }
    .form-step {
        display: none;
    }
    .form-step.active {
        display: block;
    }
    .form-group {
        margin-bottom: 15px;
    }
    input, select {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #dadce0;
        font-size: 16px;
        box-sizing: border-box;
    }
    input:focus, select:focus {
        border-color: #1a73e8;
        box-shadow: 0 1px 2px rgba(26, 115, 232, 0.2);
    }
    .btn {
        background: linear-gradient(90deg, #ff8800ff, #fc5000ff);
        color: white;
        padding: 12px;
        border: none;
        border-radius: 10px;
        width: 100%;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s ease-in-out;
        margin-top: 10px;
    }
    .btn:hover {
        background: linear-gradient(90deg, #fc5000ff, #ff8800ff);
        transform: scale(1.02);
    }
    .btn-secondary {
        background: #ddd;
        color: #333;
    }
    .password-container {
        position: relative;
    }
    .password-container input {
        padding-right: 40px;
    }
    .password-container i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: gray;
    }
    .footer {
        text-align: center;
        font-size: 12px;
        color: #5f6368;
        margin-top: 20px;
    }
    .footer a {
        color: #1a73e8;
        text-decoration: none;
        margin: 0 5px;
    }
    .error-message {
        color: red;
        text-align: center;
        margin-bottom: 15px;
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

<div class="container">
    <h2>Member Registration</h2>
    <?php echo $message ? "<div class='error-message'>{$message}</div>" : ""; ?>
    <form method="post" action="" id="regForm">

        <!-- Step 1 -->
        <div class="form-step active" id="step-1">
            <div class="form-group">
                <input type="text" name="Mem_name" placeholder="Full Name" required pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
            </div>
            <div class="form-group">
                <input type="number" name="Mem_age" placeholder="Age" required min="1" max="120">
            </div>
            <div class="form-group">
                <input type="number" name="Height" placeholder="Height (cm)" required min="50" max="300">
            </div>
            <div class="form-group">
                <input type="number" name="Weight" placeholder="Weight (kg)" required min="10" max="500">
            </div>
            <div class="form-group">
                <input type="tel" name="Mem_phno" placeholder="Phone Number" required pattern="[0-9]{10}" title="Enter a 10-digit phone number">
            </div>
            <button type="button" class="btn" onclick="nextStep(1)">Next</button>
        </div>

        <!-- Step 2 -->
        <div class="form-step" id="step-2">
            <div class="form-group">
                <input type="email" name="Mem_email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <select name="Gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <input type="date" name="Mem_dob" required>
            </div>
            <div class="form-group">
                <select name="Goal_type" required>
                    <option value="">Select Goal</option>
                    <option value="Weight Loss">Weight Loss</option>
                    <option value="Muscle Gain">Muscle Gain</option>
                    <option value="General Fitness">General Fitness</option>
                </select>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
            <button type="button" class="btn" onclick="nextStep(2)">Next</button>
        </div>

        <!-- Step 3 -->
        <div class="form-step" id="step-3">
            <div class="form-group password-container">
                <input type="password" name="Mem_pass" id="password" placeholder="Password" required minlength="6">
                <i class="fa-solid fa-eye" id="togglePassword"></i>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
            <button type="submit" class="btn">Register</button>
        </div>

    </form>

     <div class="extra-links">
        <p>Already have an account? <a href="login.php">login</a></p>
    </div>
</div>

 
<script>
    let currentStep = 1;

    function nextStep(step) {
        document.getElementById(`step-${step}`).classList.remove("active");
        currentStep = step + 1;
        document.getElementById(`step-${currentStep}`).classList.add("active");
    }

    function prevStep(step) {
        document.getElementById(`step-${step}`).classList.remove("active");
        currentStep = step - 1;
        document.getElementById(`step-${currentStep}`).classList.add("active");
    }

    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        this.classList.toggle("fa-eye-slash");
    });
</script>

</body>
</html>
