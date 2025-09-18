<?php
include "db.php";

// Fetch latest 5 feedbacks
$result = mysqli_query($conn, "
    SELECT f.rating, f.comments, m.Mem_name, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    ORDER BY f.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitGym</title>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: black;
        color: white;
    }

    /* Navbar */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: transparent;
        padding: 15px 20px;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 10;
        box-sizing: border-box;
    }
    .navbar .logo {
        font-size: 40px;
        font-weight: bold;
        color: orange;
        white-space: nowrap;
    }
    .navbar ul {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin: 0;
        padding: 0;
    }
    .navbar ul li a {
        text-decoration: none;
        color: white;
        font-weight: bold;
        transition: color 0.3s;
        font-size: 20px;
    }
    .navbar ul li a:hover {
        color: orange;
    }

    /* Hero Section */
    .hero {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                    url("images/gym-bg.jpg") center/cover no-repeat;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 20px;
    }
    .hero h1 {
        font-size: 2.5rem;
        font-weight: 900;
        margin-bottom: 20px;
        color: #fff;
    }
    .hero p {
        font-size: 1rem;
        max-width: 600px;
        margin: 0 auto 30px;
        line-height: 1.6;
        color: #ddd;
    }
    .hero-buttons {
        margin-top: 10px;
    }
    .hero-buttons a {
        display: inline-block;
        background: #ffaf01ff;
        color: black;
        padding: 12px 25px;
        border-radius: 30px;
        font-size: 15px;
        text-decoration: none;
        margin: 5px;
        font-weight: bold;
        transition: background 0.3s, transform 0.2s;
    }
    .hero-buttons a:hover {
        transform: scale(1.05);
        background: #cc0000ff;
        color: white;
    }

    /* Features Section */
    .features {
        background: #000000ff;
        padding: 50px 20px;
    }
    .features h2 {
        text-align: center;
        margin-bottom: 40px;
        font-size: 28px;
        color: orange;
    }
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        max-width: 1000px;
        margin: auto;
    }
    .feature-card {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .feature-card:hover {
        color: white; 
        cursor: pointer;
        background-color: orange;
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(255, 165, 0, 0.5); 
    }
    .feature-card:hover h3,
    .feature-card:hover p {
        color: white;
    }
    .feature-card img {
        height: 40px;
        margin-bottom: 15px;
        transition: filter 0.2s ease-in-out;
    }
    .feature-card:hover img {
        filter: brightness(0) invert(1);
    }
    .feature-card h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333;
    }
    .feature-card p {
        font-size: 14px;
        color: #555;
    }

    /* Feedback Section */
    .feedback-section {
        margin: 40px auto;
        max-width: 800px;
        background: #1a1a1a;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px orange;
    }
    .feedback-section h2 {
        text-align: center;
        color: orange;
    }
    .feedback-card {
        background: #222;
        padding: 15px;
        margin: 10px 0;
        border-radius: 6px;
    }
    .feedback-card strong {
        color: orange;
    }
    .rating {
        color: gold;
    }

    /* Footer */
    footer {
        text-align: center;
        padding: 15px;
        background: #111;
        font-size: 14px;
        color: #ccc;
        margin-top: 30px;
    }

    /* Mobile Fix */
    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            align-items: flex-start;
            padding: 10px 15px;
        }
        .navbar ul {
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
    }
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">FitGym</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
</div>

<!-- Hero -->
<div class="hero">
    <div>
        <h1>Transform Your Body, Empower Your Life</h1>
        <p>Track your workouts, manage your diet, connect with trainers, and crush your fitness goals — all in one smart system.</p>
        <div class="hero-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Join Now</a>
        </div>
    </div>
</div>

<!-- Features -->
<div class="features">
    <h2>Our Features</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <img src="images/calendar1.png" alt="Gym Calendar">
            <h3>Gym Calendar</h3>
            <p>Plan and manage your workout schedules efficiently with our integrated calendar.</p>
        </div>
        <div class="feature-card">
            <img src="images/bmi.png" alt="BMI Analyzer">
            <h3>BMI Analyzer</h3>
            <p>Track your Body Mass Index </p>
        </div>
        <div class="feature-card">
            <img src="images/nutrition.png" alt="Personalized Nutritionist">
            <h3>Personalized Nutritionist</h3>
            <p>Get customized diet plans from professional dieticians for your fitness goals.</p>
        </div>
        <div class="feature-card">
            <img src="images/member.png" alt="Member Management">
            <h3>Member Management</h3>
            <p>Easy management of members, trainers, and dieticians all in one place.</p>
        </div>
        <div class="feature-card">
            <img src="images/workout.png" alt="Workout Plans">
            <h3>Workout Plans</h3>
            <p>Receive personalized workout plans from your assigned trainer.</p>
        </div>
        <div class="feature-card">
            <img src="images/feedback.png" alt="Feedback">
            <h3>Feedback System</h3>
            <p>Share your thoughts and track your improvement with our feedback feature.</p>
        </div>
    </div>
</div>

<!-- Feedback Section -->
<div class="feedback-section">
    <h2>What Our Members Say</h2>
    <?php if (mysqli_num_rows($result) > 0) { ?>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
            <div class="feedback-card">
                <p class="rating">
                    <?php for ($i = 1; $i <= $row['rating']; $i++) echo "⭐"; ?>
                </p>
                <p>"<?= htmlspecialchars($row['comments']); ?>"</p>
                <p><strong>- <?= htmlspecialchars($row['Mem_name']); ?></strong></p>
                <small><?= date("F j, Y", strtotime($row['created_at'])); ?></small>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>No feedback yet. Be the first to share your experience!</p>
    <?php } ?>
</div>

<!-- Footer -->
<footer>
    &copy; 2025 Gym Management System. All rights reserved.
</footer>

</body>
</html>
