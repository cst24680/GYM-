<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "You are not logged in.";
    exit();
}

include('db.php');
$email = $_SESSION['email'];

$conn = mysqli_connect("localhost", "root", "", "gym_db");

$member_query = "SELECT * FROM member_registration WHERE Mem_email = '$email'";
$member_result = mysqli_query($conn, $member_query);
$member = mysqli_fetch_assoc($member_result);

if (!$member) {
    echo "Member not found.";
    exit;
}

$trainer = null;
if (!empty($member['Trainer_id'])) {
    $trainer_query = "SELECT * FROM trainer WHERE Trainer_id = " . $member['Trainer_id'];
    $trainer_result = mysqli_query($conn, $trainer_query);
    $trainer = mysqli_fetch_assoc($trainer_result);
}

$dietician = null;
if (!empty($member['Dietician_id'])) {
    $dietician_query = "SELECT * FROM dietician_login WHERE Dietician_id = " . $member['Dietician_id'];
    $dietician_result = mysqli_query($conn, $dietician_query);
    $dietician = mysqli_fetch_assoc($dietician_result);
}

$diet = null;
$diet_query = "SELECT * FROM diet_plans WHERE Mem_id = " . $member['Mem_id'];
$diet_result = mysqli_query($conn, $diet_query);
$diet = mysqli_fetch_assoc($diet_result);

$workout = null;
$plan_query = "SELECT * FROM plan_type WHERE Mem_id = " . $member['Mem_id'];
$workout_result = mysqli_query($conn, $plan_query);
$workout = mysqli_fetch_assoc($workout_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GymX Member Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0d0d0d;
            margin: 0;
            padding: 0;
            color: #ffffff;
        }
        header {
            background-color: #121212;
            color: #ffffff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e50914;
        }
        header h1 {
            font-size: 26px;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        nav ul li a {
            color: #cccccc;
            text-decoration: none;
            font-weight: bold;
        }
        nav ul li a:hover {
            color: #e50914;
        }
        .hero {
            display: flex;
            justify-content: space-between;
            padding: 40px;
            background-color: #1e1e1e;
            align-items: center;
            flex-wrap: wrap;
        }
        .hero-text {
            max-width: 500px;
        }
        .hero-text h2 {
            font-size: 36px;
            color: #f5f5f5;
        }
        .hero-text p {
            font-size: 16px;
            margin-top: 10px;
            color: #cccccc;
        }
        .hero-text a {
            display: inline-block;
            margin-top: 20px;
            background-color: #e50914;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .hero-text a:hover {
            background-color: #c40811;
        }
        .hero img {
            max-width: 350px;
            border-radius: 10px;
        }
        .dashboard {
            padding: 40px;
        }
        .dashboard h3 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 30px;
            color: #f5f5f5;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        .card {
            background-color: #2b2b2b;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .card h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #e50914;
        }
        .card p {
            margin: 5px 0;
            font-size: 15px;
            color: #dddddd;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: #aaa;
            background-color: #121212;
            margin-top: 40px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <h1>🏋️‍♂️ GymX</h1>
    <nav>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#plans">Workout</a></li>
            <li><a href="#plans">Diet Plan</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-text">
        <h2>Welcome, <?php echo htmlspecialchars($member['Mem_name']); ?>!</h2>
        <p>This is your personalized fitness dashboard. Track your progress and stay committed!</p>
        <a href="#plans">View Your Plans</a>
    </div>
   <img src="images/gym.png" alt="Gym Image" style="width: 100%; max-width: 400px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);">
        

</section>

<section class="dashboard" id="plans">
    <h3>Your Dashboard</h3>
    <div class="grid">
        <div class="card">
            <h4>Personal Info</h4>
            <p><strong>Age:</strong> <?= $member['Mem_age'] ?></p>
            <p><strong>DOB:</strong> <?= $member['Mem_dob'] ?></p>
            <p><strong>Phone:</strong> <?= $member['Mem_phno'] ?></p>
            <p><strong>Height:</strong> <?= $member['Height'] ?> cm</p>
            <p><strong>Weight:</strong> <?= $member['Weight'] ?> kg</p>
            <p><strong>BMI:</strong> <?= $member['BMI'] ?></p>
            <p><strong>Plan:</strong> <?= $member['Plan_name'] ?></p>
        </div>

        <div class="card">
            <h4>Your Trainer</h4>
            <p><strong>Name:</strong> <?= $trainer['Trainer_name'] ?? 'N/A' ?></p>
            <p><strong>Email:</strong> <?= $trainer['Trainer_email'] ?? 'N/A' ?></p>
            <p><strong>Phone:</strong> <?= $trainer['Trainer_phno'] ?? 'N/A' ?></p>
        </div>

        <div class="card">
            <h4>Your Dietician</h4>
            <p><strong>Name:</strong> <?= $dietician['Dietician_name'] ?? 'N/A' ?></p>
            <p><strong>Email:</strong> <?= $dietician['Dietician_email'] ?? 'N/A' ?></p>
            <p><strong>Phone:</strong> <?= $dietician['Dietician_phno'] ?? 'N/A' ?></p>
        </div>

        <div class="card">
            <h4>Plans</h4>
            <p><strong>Workout:</strong> <?= $workout['Workout_type'] ?? 'Not Assigned' ?></p>
            <p><strong>Diet:</strong> <?= $diet['Diet'] ?? 'No diet assigned' ?></p>
        </div>
    </div>
</section>

<footer>
    &copy; <?= date('Y') ?> GymX Fitness. Stay strong 💪.
</footer>

</body>
</html>
