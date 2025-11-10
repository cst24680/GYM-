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
        font-family: Arial, Helvetica, sans-serif;
        font-size: 3em;
        font-weight: bold;
        font-weight: 900;
        margin-bottom: 20px;
        color: #f78528ff;
    }
    .hero p {
        font-size: 1.3rem;
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
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                    url("images/gym1-bg.jpg") center/cover no-repeat;

        padding: 50px 20px;
    }
    .features h2 {
        font-family: Arial, Helvetica, sans-serif;
        text-align: center;
        margin-bottom: 40px;
        font-size: 38px;
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
        background: #ffffffff;
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

    /* Why Choose Us */
    .why-choose {
         background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                    url("images/gym1-bg.jpg") center/cover no-repeat;

        padding: 60px 20px;
        text-align: center;
    }
    .why-choose h2 {
        font-size: 28px;
        margin-bottom: 10px;
        color: white;
    }
    .why-choose h3 {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 40px;
        color: white;
    }
    .choose-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 30px;
        max-width: 1000px;
        margin: auto;
    }
    .choose-card {
        text-align: center;
        padding: 20px;
    }
    .choose-card img {
        height: 50px;
        margin-bottom: 15px;
    }
    .choose-card h4 {
        font-size: 18px;
        margin-bottom: 10px;
        font-weight: bold;
        color: white;
    }
    .choose-card p {
        font-size: 14px;
        color: #bbb;
    }

    /* BMI Section */
    .bmi-section {
         background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                    url("images/gym2-bg.jpg") center/cover no-repeat;

        padding: 50px 20px;
        text-align: center;
    }
    .bmi-section h2 {
        font-family: Arial, Helvetica, sans-serif;
        color: orange;
        margin-bottom: 20px;
    }
    .bmi-form input {
        margin: 5px;
        padding: 10px;
        width: 200px;
    }
    .bmi-form button {
        margin: 5px;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        background: orange;
        color: black;
        font-weight: bold;
        border-radius: 5px;
    }
    .bmi-table {
        margin: 20px auto;
        border-collapse: collapse;
        width: 60%;
    }
    .bmi-table th, .bmi-table td {
        border: 1px solid #444;
        padding: 10px;
        text-align: center;
    }
    .highlight {
        background: orange !important;
        color: black !important;
        font-weight: bold;
    }
    #suggestion {
        margin-top: 15px;
        font-size: 16px;
        color: #ffcc00;
        font-weight: bold;
    }

.feedback-section {
    width: 100%;
    background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)),
                url("images/gym3-bg.jpg") center/cover no-repeat;
    padding: 70px 20px;
    text-align: center;
}

.feedback-section h2 {
    color: orange;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 50px;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 0 0 8px rgba(255, 165, 0, 0.7);
}

/* Grid for feedback */
.feedback-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    max-width: 1000px;
    margin: 0 auto;
}

/* Card Styling */
.feedback-card {
    background: rgba(34, 34, 34, 0.95);
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.6);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: left;
    position: relative;
    overflow: hidden;
}

.feedback-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 25px rgba(255, 165, 0, 0.6);
}

/* Stars */
.rating {
    color: gold;
    margin-bottom: 12px;
    font-size: 1.2rem;
}

/* Feedback text */
.feedback-card p {
    margin: 10px 0;
    line-height: 1.6;
    color: #ddd;
    font-size: 1rem;
}

/* Name */
.feedback-card strong {
    display: block;
    margin-top: 12px;
    font-size: 1rem;
    font-weight: 600;
    color: orange;
}

/* Date */
.feedback-card small {
    display: block;
    margin-top: 4px;
    color: #aaa;
    font-size: 0.85rem;
}

/* Optional: Add avatar circle */
.feedback-card::before {
    content: "👤";
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 1.5rem;
    color: rgba(255,255,255,0.3);
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
    <div class="logo">FitX</div>
    <ul>
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
    <h2>Why Choose Us</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <img src="images/calendar1.png" alt="Gym Calendar">
            <h3>Gym Calendar</h3>
            <p>Plan and manage your workout schedules efficiently with our integrated calendar.</p>
        </div>
        <div class="feature-card">
            <img src="images/bmi.png" alt="BMI Analyzer">
            <h3>BMI Analyzer</h3>
            <p>Quickly calculate your BMI and understand what it means for your health. </p>
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
            <img src="images/feedback1.png" alt="Feedback">
            <h3>Feedback System</h3>
            <p>Share your thoughts and track your improvement with our feedback feature.</p>
        </div>
    </div>
</div>

<!-- BMI Section -->
<div class="bmi-section">
    <h2>BMI CALCULATOR</h2>
    <form class="bmi-form" onsubmit="return false;">
        <input type="number" id="weight" placeholder="Your Weight (kg)" required>
        <input type="number" id="height" placeholder="Your Height (cm)" required>
        <input type="text" id="result" placeholder="Result here" readonly>
        <button type="button" onclick="calculateBMI()">CALCULATE IT</button>
        <button type="reset" onclick="resetBMI()">RESET IT</button>
    </form>
    <table class="bmi-table" id="bmiTable">
        <tr><th>BMI</th><th>CLASSIFICATION</th></tr>
        <tr><td>&lt; 18.5</td><td>Underweight</td></tr>
        <tr><td>18.5 - 24.9</td><td>Normal Weight</td></tr>
        <tr><td>25 - 29.9</td><td>Overweight</td></tr>
        <tr><td>30+</td><td>Obesity</td></tr>
    </table>
    <p id="suggestion"></p>
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

<script>
function calculateBMI() {
    let weight = parseFloat(document.getElementById("weight").value);
    let height = parseFloat(document.getElementById("height").value);

    if (weight > 0 && height > 0) {
        let bmi = (weight / ((height / 100) * (height / 100))).toFixed(1);
        let category = "";
        let suggestion = "";

        if (bmi < 18.5) category = "Underweight";
        else if (bmi < 25) category = "Normal Weight";
        else if (bmi < 30) category = "Overweight";
        else category = "Obesity";

        document.getElementById("result").value = `BMI: ${bmi} (${category})`;

        let rows = document.querySelectorAll("#bmiTable tr");
        rows.forEach(row => row.classList.remove("highlight"));

        if (category === "Underweight") rows[1].classList.add("highlight");
        else if (category === "Normal Weight") rows[2].classList.add("highlight");
        else if (category === "Overweight") rows[3].classList.add("highlight");
        else if (category === "Obesity") rows[4].classList.add("highlight");

        let normalWeight = 22 * ((height / 100) * (height / 100));
        let difference = (normalWeight - weight).toFixed(1);

        if (category === "Normal Weight") {
            suggestion = "You are at a healthy weight. Keep it up!";
        } else if (difference > 0) {
            suggestion = `You may need to gain around ${difference} kg to reach a normal BMI.`;
        } else {
            suggestion = `You may need to lose around ${Math.abs(difference)} kg to reach a normal BMI.`;
        }

        document.getElementById("suggestion").textContent = suggestion;

    } else {
        alert("Please enter valid height and weight!");
    }
}

function resetBMI() {
    document.getElementById("result").value = "";
    document.getElementById("suggestion").textContent = "";
    let rows = document.querySelectorAll("#bmiTable tr");
    rows.forEach(row => row.classList.remove("highlight"));
}
</script>

</body>
</html>
