<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "You are not logged in.";
    exit();
}

include('db.php');

$email = $_SESSION['email'];
$conn = mysqli_connect("localhost", "root", "", "gym_db");

// Get trainer info
$trainer_query = "SELECT * FROM trainer WHERE Trainer_email = '$email'";
$trainer_result = mysqli_query($conn, $trainer_query);
$trainer = mysqli_fetch_assoc($trainer_result);

if (!$trainer) {
    echo "Trainer not found.";
    exit();
}

$trainer_id = $trainer['Trainer_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_workout'])) {
    $mem_id = $_POST['mem_id'];
    $workout_type = $_POST['workout_type'];

    // Check if already exists
    $check_query = "SELECT * FROM plan_type WHERE Mem_id = $mem_id";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update
        $update_query = "UPDATE plan_type SET Workout_type = '$workout_type' WHERE Mem_id = $mem_id";
        mysqli_query($conn, $update_query);
    } else {
        // Insert
        $insert_query = "INSERT INTO plan_type (Mem_id, Workout_type) VALUES ($mem_id, '$workout_type')";
        mysqli_query($conn, $insert_query);
    }
}

// Get all members assigned to this trainer
$members_query = "SELECT * FROM member_registration WHERE Trainer_id = $trainer_id";
$members_result = mysqli_query($conn, $members_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        header {
            background: #e50914;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            padding: 30px;
        }
        h2 {
            color: #333;
        }
        .member-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .member-card h3 {
            margin-top: 0;
        }
        .member-card form {
            margin-top: 10px;
        }
        input[type="text"], select {
            padding: 8px;
            width: 70%;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #e50914;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #c40811;
        }
    </style>
</head>
<body>

<header>
    <h1>Trainer Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($trainer['Trainer_name']) ?></p>
</header>

<div class="container">
    <h2>Assign Workouts to Members</h2>

    <?php while ($member = mysqli_fetch_assoc($members_result)) { 
        // Check current workout
        $plan_query = "SELECT * FROM plan_type WHERE Mem_id = " . $member['Mem_id'];
        $plan_result = mysqli_query($conn, $plan_query);
        $plan = mysqli_fetch_assoc($plan_result);
    ?>
        <div class="member-card">
            <h3><?= htmlspecialchars($member['Mem_name']) ?> (<?= $member['Mem_email'] ?>)</h3>
            <p><strong>Current Workout:</strong> <?= $plan['Workout_type'] ?? 'Not Assigned' ?></p>
            <form method="POST">
                <input type="hidden" name="mem_id" value="<?= $member['Mem_id'] ?>">
                <select name="workout_type" required>
                    <option value="">-- Select Workout --</option>
                    <option value="Weight Loss">Weight Loss</option>
                    <option value="Muscle Gain">Muscle Gain</option>
                    <option value="General Fitness">General Fitness</option>
                    <option value="Cardio">Cardio</option>
                </select>
                <button type="submit" name="assign_workout">Assign Workout</button>
            </form>
        </div>
    <?php } ?>
</div>

</body>
</html>
