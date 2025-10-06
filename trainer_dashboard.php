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
$plans_res = mysqli_query($conn, $plans_sql);

// Fetch feedback given to this trainer
$feedback_sql = "
    SELECT f.feedback_id, m.Mem_name, f.rating, f.comments, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    WHERE f.target_type = 'Trainer' AND f.target_id = $trainer_id
    ORDER BY f.created_at DESC
";
$feedback_res = mysqli_query($conn, $feedback_sql);

// Fetch attendance records of assigned members
$attendance_sql = "
    SELECT a.Mem_id, m.Mem_name, a.check_in_time
    FROM attendance a
    JOIN member_registration m ON a.Mem_id = m.Mem_id
    WHERE m.Trainer_id = $trainer_id
    ORDER BY a.check_in_time DESC
";
$attendance_res = mysqli_query($conn, $attendance_sql);

// Set the default active section ID
$active_section = 'profile';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="trainer.css">
    <style>
        .box {
            display: none !important; /* Hide all sections by default */
            flex-direction: column;
        }
        .box.active {
            display: block !important;
        }
        .header {
            background-color: #1A1A1A;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            margin-bottom: 30px;
        }
        .header h1 {
            font-family: 'Montserrat', sans-serif;
            color: #FFD166;
            font-size: 2rem;
            margin: 0;
        }
        .header p {
            color: #999;
            font-size: 0.9rem;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        table th {
            background: #2A2A2A;
            color: #06D6A0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        table tr:nth-child(even) {
            background-color: #1E1E1E;
        }
        table tr:hover {
            background-color: #2A2A2A;
            transition: background-color 0.2s;
        }
    </style>
</head>
<body onload="setActiveSection('<?php echo $active_section; ?>')">

<!-- Sidebar -->
<div class="sidebar">
    <h2>Trainer Panel</h2>
    <a class="menu-link active" data-target="profile" onclick="showSection(this)">Dashboard</a>
    <a class="menu-link" data-target="members" onclick="showSection(this)">Assigned Members</a>
    <a class="menu-link" data-target="assign" onclick="showSection(this)">Assign Workout</a>
    <a class="menu-link" data-target="manage" onclick="showSection(this)">Manage Workouts</a>
    <a class="menu-link" data-target="attendance" onclick="showSection(this)">Attendance</a>
    <a class="menu-link" data-target="feedback" onclick="showSection(this)">View Feedback</a>
    <a class="logout-btn" href="logout.php">Logout</a>
</div>

<!-- Main content -->
<div class="content">
    
    <div class="header" id="welcomeHeader">
        <div class="greeting">
            <h1>Welcome, <?php echo htmlspecialchars($trainer['Trainer_name']); ?>!</h1>
            <p><?php echo date("l, M j, Y"); ?></p>
        </div>
    </div>

    <!-- Profile Section -->
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

    <!-- Members Section -->
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
                <?php mysqli_data_seek($members_res,0); while($row = mysqli_fetch_assoc($members_res)): ?>
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

    <!-- Assign Workout Section -->
    <div class="box" id="assign">
        <h2>Assign Workout</h2>
        <form action="assign_workout.php" method="POST" class="assign-form">
            <label>Member:</label>
            <select name="Mem_id" required>
                <option value="">-- Select Member --</option>
                <?php mysqli_data_seek($members_res,0); while($m = mysqli_fetch_assoc($members_res)) {
                    echo "<option value='{$m['Mem_id']}'>{$m['Mem_name']}</option>";
                } ?>
            </select>

            <label>Workout Plan:</label>
            <select name="Plan_type_id" required>
                <option value="">-- Select Workout Plan --</option>
                <?php 
                $plans_sql = "SELECT Plan_type_id, Workout_type FROM plan_type WHERE Trainer_id = $trainer_id";
                $plans_res = mysqli_query($conn, $plans_sql);
                if($plans_res && mysqli_num_rows($plans_res) > 0) {
                    while($p = mysqli_fetch_assoc($plans_res)) {
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

    <!-- Manage Workouts Section -->
    <div class="box" id="manage">
        <h2>Manage Workouts</h2>
        <a class="link" href="view_assigned_workouts.php">View / Edit / Delete Workouts</a>
    </div>

    <!-- Attendance Section -->
    <div class="box" id="attendance">
        <h2>Attendance Records</h2>
        <?php if(mysqli_num_rows($attendance_res) > 0): ?>
            <table>
                <tr>
                    <th>Member Name</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
                <?php while($att = mysqli_fetch_assoc($attendance_res)): 
                    $dt = strtotime($att['check_in_time']);
                    $date = date("Y-m-d", $dt);
                    $time = date("h:i A", $dt);
                ?>
                    <tr>
                        <td><?php echo $att['Mem_name']; ?></td>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $time; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No attendance records yet.</p>
        <?php endif; ?>
    </div>

    <!-- Feedback Section -->
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
function showSection(clickedLink) {
    const targetId = clickedLink.dataset.target;
    document.querySelectorAll(".menu-link").forEach(link => link.classList.remove("active"));
    document.querySelectorAll(".box").forEach(section => section.classList.remove("active"));
    clickedLink.classList.add("active");
    const targetSection = document.getElementById(targetId);
    if(targetSection) targetSection.classList.add("active");
    const contentArea = document.querySelector('.content');
    if(contentArea) contentArea.scrollTop = 0;
    const header = document.getElementById('welcomeHeader');
    header.style.display = (targetId === 'profile') ? 'block' : 'none';
}

function setActiveSection(defaultId) {
    const initialSection = document.getElementById(defaultId);
    const initialLink = document.querySelector(`.menu-link[data-target="${defaultId}"]`);
    document.querySelectorAll(".box").forEach(section => section.classList.remove("active"));
    document.querySelectorAll(".menu-link").forEach(link => link.classList.remove("active"));
    if(initialSection) initialSection.classList.add("active");
    if(initialLink) initialLink.classList.add("active");
    const header = document.getElementById('welcomeHeader');
    header.style.display = (defaultId === 'profile') ? 'block' : 'none';
}
</script>

</body>
</html>
