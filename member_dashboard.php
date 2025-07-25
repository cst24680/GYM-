<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login session
if (!isset($_SESSION['email'])) {
    echo "You are not logged in.";
    exit();
}

include('db.php'); // DB connection

$email = $_SESSION['email'];

// ✅ Get Member details using email
$query = "SELECT * FROM member_registration WHERE Mem_email = '$email'";
$result = mysqli_query($conn, $query);
$member = mysqli_fetch_assoc($result);

if (!$member) {
    echo "Member not found.";
    exit;
}

// ✅ Get Trainer details
$trainer = null;
if (!empty($member['Trainer_id'])) {
    $trainer_query = "SELECT * FROM trainer WHERE Trainer_id = " . $member['Trainer_id'];
    $trainer_result = mysqli_query($conn, $trainer_query);
    $trainer = mysqli_fetch_assoc($trainer_result);
}

// ✅ Get Dietician details
$dietician = null;
if (!empty($member['Dietician_id'])) {
    $dietician_query = "SELECT * FROM dietician_login WHERE Dietician_id = " . $member['Dietician_id'];
    $dietician_result = mysqli_query($conn, $dietician_query);
    $dietician = mysqli_fetch_assoc($dietician_result);
}

// ✅ Get Diet Plan
$diet = null;
$diet_query = "SELECT * FROM diet_plans WHERE Mem_id = " . $member['Mem_id'];
$diet_result = mysqli_query($conn, $diet_query);
$diet = mysqli_fetch_assoc($diet_result);

// ✅ Get Workout Plan
$workout = null;
$plan_query = "SELECT * FROM plan_type WHERE Mem_id = " . $member['Mem_id'];
$workout_result = mysqli_query($conn, $plan_query);
$workout = mysqli_fetch_assoc($workout_result);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Member Dashboard</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        background: #f3f3f3;
        padding: 30px;
    }
    .dashboard {
        background: #fff;
        padding: 30px;
        max-width: 700px;
        margin: auto;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #333;
    }
    .section {
        margin-bottom: 25px;
    }
    .section h3 {
        color: #555;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
    .section p {
        margin: 6px 0;
    }
  </style>
</head>
<body>
<div class="dashboard">
  <h2>Welcome, <?php echo htmlspecialchars($member['Mem_name']); ?>!</h2>

  <div class="section">
    <h3>Personal Details</h3>
    <p><strong>Age:</strong> <?php echo $member['Mem_age']; ?></p>
    <p><strong>Phone:</strong> <?php echo $member['Mem_phno']; ?></p>
    <p><strong>Date of Birth:</strong> <?php echo $member['Mem_dob']; ?></p>
    <p><strong>Height:</strong> <?php echo $member['Height']; ?> cm</p>
    <p><strong>Weight:</strong> <?php echo $member['Weight']; ?> kg</p>
    <p><strong>BMI:</strong> <?php echo $member['BMI']; ?></p>
    <p><strong>Plan Name:</strong> <?php echo $member['Plan_name']; ?></p>
  </div>

  <div class="section">
    <h3>Trainer Details</h3>
    <p><strong>Name:</strong> <?php echo $trainer['Trainer_name'] ?? 'N/A'; ?></p>
    <p><strong>Email:</strong> <?php echo $trainer['Trainer_email'] ?? 'N/A'; ?></p>
    <p><strong>Phone:</strong> <?php echo $trainer['Trainer_phno'] ?? 'N/A'; ?></p>
  </div>

  <div class="section">
    <h3>Dietician Details</h3>
    <p><strong>Name:</strong> <?php echo $dietician['Dietician_name'] ?? 'N/A'; ?></p>
    <p><strong>Email:</strong> <?php echo $dietician['Dietician_email'] ?? 'N/A'; ?></p>
    <p><strong>Phone:</strong> <?php echo $dietician['Dietician_phno'] ?? 'N/A'; ?></p>
  </div>

  <div class="section">
    <h3>Diet Plan</h3>
    <p><?php echo $diet['Diet'] ?? 'No diet plan assigned yet.'; ?></p>
  </div>

  <div class="section">
    <h3>Workout Plan</h3>
    <p><strong>Workout Type:</strong> <?php echo $workout['Workout_type'] ?? 'Not Assigned'; ?></p>
  </div>
</div>
</body>
</html>
