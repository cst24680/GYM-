<?php
session_start();
include "db.php";

// Check if trainer is logged in
if (!isset($_SESSION['Trainer_id'])) {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['Trainer_id'];

// Fetch trainer details
$trainer_sql = "SELECT * FROM trainer WHERE Trainer_id = $trainer_id";
$trainer_res = mysqli_query($conn, $trainer_sql);
$trainer = mysqli_fetch_assoc($trainer_res);

// Fetch members assigned to this trainer
$members_sql = "
    SELECT m.Mem_id, m.Mem_name, m.Mem_email, m.Goal_type, m.BMI, m.Plan_name
    FROM member_registration m
    WHERE m.Trainer_id = $trainer_id
";
$members_res = mysqli_query($conn, $members_sql);

// Fetch workout plans created by this trainer
$plans_sql = "SELECT Plan_type_id, Workout_type FROM plan_type WHERE Trainer_id = $trainer_id";
$plans_res = mysqli_query($conn, $plans_sql); // FIX: Changed 'conn' to '$conn'

// Fetch feedback given to this trainer
$feedback_sql = "
    SELECT f.feedback_id, m.Mem_name, f.rating, f.comments, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    WHERE f.target_type = 'Trainer' AND f.target_id = $trainer_id
    ORDER BY f.created_at DESC
";
$feedback_res = mysqli_query($conn, $feedback_sql);

// Set the default active section ID
$active_section = 'profile'; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="trainer.css">
    <!-- CRITICAL FIX: Inline CSS to ensure content sections are hidden by default -->
    <style>
        .box {
            display: none !important; /* Hide all sections */
            flex-direction: column; /* Ensure original box flex layout is maintained when visible */
        }
        .box.active {
            display: block !important; /* Show only the active section */
        }
        /* New Header Style for Dashboard (mimicking member dashboard) */
        .header {
            background-color: #1A1A1A;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px; /* Space below header */
        }
        .header h1 {
            font-family: 'Montserrat', sans-serif;
            color: #FFD166; /* Gold/Yellow accent */
            font-size: 2rem;
            margin: 0;
        }
        .header p {
            color: #999;
            font-size: 0.9rem;
        }
    </style>
</head>
<body onload="setActiveSection('<?php echo $active_section; ?>')">

<!-- Sidebar -->
<div class="sidebar">
    <h2>Trainer Panel</h2>
    <!-- Note: Added class="active" to Dashboard link -->
    <a class="menu-link active" data-target="profile" onclick="showSection(this)">Dashboard</a>
    <a class="menu-link" data-target="members" onclick="showSection(this)">Assigned Members</a>
    <a class="menu-link" data-target="assign" onclick="showSection(this)">Assign Workout</a>
    <a class="menu-link" data-target="manage" onclick="showSection(this)">Manage Workouts</a>
    <a class="menu-link" data-target="feedback" onclick="showSection(this)">View Feedback</a>
    <a class="logout-btn" href="logout.php">Logout</a>
</div>

<!-- Main content -->
<div class="content">
    
    <!-- NEW HEADER SECTION (Matching Member Dashboard Greeting) -->
    <div class="header" id="welcomeHeader">
        <div class="greeting">
            <h1>Welcome, <?php echo htmlspecialchars($trainer['Trainer_name']); ?>!</h1>
            <p><?php echo date("l, M j, Y"); ?></p>
        </div>
    </div>
    
    <!-- Profile Card (Now structured with list and icons) -->
    <div class="box active" id="profile">
        <h2>Your Profile Details</h2>
        
        <div class="profile-details-list">
            <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo $trainer['Email']; ?></p>
            <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo $trainer['Trainer_phno']; ?></p>
            <p><strong><i class="fas fa-venus-mars"></i> Gender:</strong> <?php echo $trainer['Trainer_gender']; ?></p>
            <p><strong><i class="fas fa-check-circle"></i> Status:</strong> <?php echo $trainer['Trainer_status']; ?></p>
            <p><strong><i class="fas fa-dumbbell"></i> Speciality:</strong> <?php echo $trainer['Speciality']; ?></p>
        </div>
    </div>

    <!-- Members -->
    <div class="box" id="members">
        <h2>Members Assigned to You</h2>
        <?php if (mysqli_num_rows($members_res) > 0): ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Goal</th>
                    <th>BMI</th>
                    <th>Plan Name</th>
                </tr>
                <?php 
                mysqli_data_seek($members_res, 0);
                while ($row = mysqli_fetch_assoc($members_res)): ?>
                    <tr>
                        <td><?php echo $row['Mem_name']; ?></td>
                        <td><?php echo $row['Mem_email']; ?></td>
                        <td><?php echo $row['Goal_type']; ?></td>
                        <td><?php echo $row['BMI']; ?></td>
                        <td><?php echo $row['Plan_name'] ? $row['Plan_name'] : 'No plan assigned'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No members assigned yet.</p>
        <?php endif; ?>
    </div>

    <!-- Assign Workout -->
    <div class="box" id="assign">
        <h2>Assign Workout</h2>
        <form action="assign_workout.php" method="POST" class="assign-form">
            <label>Member:</label>
            <select name="Mem_id" required>
                <option value="">-- Select Member --</option>
                <?php
                mysqli_data_seek($members_res, 0);
                while ($m = mysqli_fetch_assoc($members_res)) {
                    echo "<option value='{$m['Mem_id']}'>{$m['Mem_name']}</option>";
                }
                ?>
            </select>

            <label>Workout Plan:</label>
            <select name="Plan_type_id" required>
                <option value="">-- Select Workout Plan --</option>
                <?php 
                $plans_sql = "SELECT Plan_type_id, Workout_type FROM plan_type";
                $plans_res = mysqli_query($conn, $plans_sql);

                if ($plans_res && mysqli_num_rows($plans_res) > 0) {
                    while ($p = mysqli_fetch_assoc($plans_res)) {
                        echo "<option value='{$p['Plan_type_id']}'>{$p['Workout_type']}</option>";
                    }
                } else {
                    echo "<option value=''>No workouts available</option>";
                }
                ?>
            </select>

            <label>Date:</label>
            <input type="date" name="Workout_date" required>

            <label>Description:</label>
            <textarea name="Workout_description" placeholder="Enter workout details..." required></textarea>

            <button type="submit">Assign Workout</button>
        </form>
    </div>

    <!-- Manage Workouts -->
    <div class="box" id="manage">
        <h2>Manage Workouts</h2>
        <a class="link" href="view_assigned_workouts.php">View / Edit / Delete Workouts</a>
    </div>

    <!-- View Feedback -->
    <div class="box" id="feedback">
        <h2>Feedback from Members</h2>
        <?php if (mysqli_num_rows($feedback_res) > 0): ?>
            <table>
                <tr>
                    <th>Member Name</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
                <?php while ($fb = mysqli_fetch_assoc($feedback_res)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['Mem_name']); ?></td>
                        <td><?php echo $fb['rating']; ?>/5</td>
                        <td><?php echo htmlspecialchars($fb['comments']); ?></td>
                        <td><?php echo $fb['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No feedback received yet.</p>
        <?php endif; ?>
    </div>

</div>

<script>
    // JavaScript function to switch sections
    function showSection(clickedLink) {
        const targetId = clickedLink.dataset.target;
        
        // Remove 'active' class from all links and sections
        document.querySelectorAll(".menu-link").forEach(link => link.classList.remove("active"));
        document.querySelectorAll(".box").forEach(section => section.classList.remove("active"));

        // Add 'active' class to the clicked link
        clickedLink.classList.add("active");
        
        // Add 'active' class to the corresponding section
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add("active");
            
            // Scroll to the top of the content area if necessary (optional improvement)
            const contentArea = document.querySelector('.content');
            if (contentArea) contentArea.scrollTop = 0;
        }
        
        // Ensure the header is only shown on the dashboard (profile)
        const header = document.getElementById('welcomeHeader');
        if (targetId === 'profile') {
            header.style.display = 'block';
        } else {
            header.style.display = 'none';
        }
    }

    // Function to set the active section on page load
    function setActiveSection(defaultId) {
        // Use the initial state set in PHP (default is 'profile')
        const initialSection = document.getElementById(defaultId);
        const initialLink = document.querySelector(`.menu-link[data-target="${defaultId}"]`);

        if (initialSection) {
            // Ensure only the initial section and link are active on load
            document.querySelectorAll(".box").forEach(section => section.classList.remove("active"));
            document.querySelectorAll(".menu-link").forEach(link => link.classList.remove("active"));

            initialSection.classList.add("active");
        }
        if (initialLink) {
            initialLink.classList.add("active");
        }
        
        // Ensure the header is visible on load if we start at 'profile'
        const header = document.getElementById('welcomeHeader');
        if (defaultId === 'profile') {
            header.style.display = 'block';
        } else {
            header.style.display = 'none';
        }
    }

    // Attach click listeners after the DOM is fully loaded (alternative to onload attribute)
    document.addEventListener('DOMContentLoaded', () => {
        // The setActiveSection is called via the <body> onload attribute for early visibility
        // but this ensures the click listeners are attached reliably.
        
        // Note: The click listeners are already set via inline 'onclick' attributes in the HTML.
        // We ensure that the initial state is correctly set on the body element.
    });
</script>

</body>
</html>
