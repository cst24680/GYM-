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
        :root {
            --bg-color: #fdfbf6;
            --text-color: #222;
            --card-bg: white;
            --header-bg: #e50914;
            --link-color: #e50914;
            --btn-bg: #333;
            --btn-hover: #555;
        }

        body.dark-theme {
            --bg-color: #0d0d0d;
            --text-color: #f5f5f5;
            --card-bg: #2b2b2b;
            --header-bg: #121212;
            --link-color: #e50914;
            --btn-bg: #e50914;
            --btn-hover: #c40811;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            transition: background 0.3s ease, color 0.3s ease;
        }

        header {
            background-color: var(--header-bg);
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--link-color);
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
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            color: var(--link-color);
        }

        .theme-btn {
            background-color: var(--btn-bg);
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 20px;
        }

        .theme-btn:hover {
            background-color: var(--btn-hover);
        }

        .hero {
            display: flex;
            justify-content: space-between;
            padding: 40px;
            background-color: var(--card-bg);
            align-items: center;
            flex-wrap: wrap;
        }

        .hero-text {
            max-width: 500px;
        }

        .hero-text h2 {
            font-size: 36px;
        }

        .hero-text p {
            font-size: 16px;
            margin-top: 10px;
        }

        .hero-text a {
            display: inline-block;
            margin-top: 20px;
            background-color: var(--link-color);
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .hero-text a:hover {
            background-color: var(--btn-hover);
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
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .card h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--link-color);
        }

        .card p {
            margin: 5px 0;
            font-size: 15px;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #aaa;
            background-color: var(--header-bg);
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
    <button class="theme-btn" id="themeToggle" onclick="toggleTheme()">🌙 Dark Mode</button>
</header>

<section class="hero">
    <div class="hero-text">
        <h2>Welcome, <?php echo htmlspecialchars($member['Mem_name']); ?>!</h2>
        <p>This is your personalized fitness dashboard. Track your progress and stay committed!</p>
        <a href="#plans">View Your Plans</a>
    </div>
    <img src="images/g.png.png" alt="Gym Image" >
    
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const theme = localStorage.getItem("theme");
        if (theme === "dark") {
            document.body.classList.add("dark-theme");
            document.getElementById("themeToggle").innerText = "☀️ Light Mode";
        }
    });

    function toggleTheme() {
        document.body.classList.toggle("dark-theme");
        const isDark = document.body.classList.contains("dark-theme");
        document.getElementById("themeToggle").innerText = isDark ? "☀️ Light Mode" : "🌙 Dark Mode";
        localStorage.setItem("theme", isDark ? "dark" : "light");
    }
</script>

</body>
</html>
