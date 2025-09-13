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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="trainer.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Trainer Panel</h2>
    <a class="menu-link" data-target="profile">Dashboard</a>
    <a class="menu-link" data-target="members">Assigned Members</a>
    <a class="menu-link" data-target="assign">Assign Workout</a>
    <a class="menu-link" data-target="manage">Manage Workouts</a>
    <a class="menu-link" data-target="feedback">View Feedback</a>
    <a class="logout-btn" href="logout.php">Logout</a>
</div>

<!-- Main content -->
<div class="content">
    
    <!-- Profile -->
    <div class="box" id="profile">
        <h2>Your Profile</h2>
        <h2>Welcome, <?php echo htmlspecialchars($trainer['Trainer_name']); ?>!</h2>
        <p><strong>Email:</strong> <?php echo $trainer['Email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $trainer['Trainer_phno']; ?></p>
        <p><strong>Gender:</strong> <?php echo $trainer['Trainer_gender']; ?></p>
        <p><strong>Status:</strong> <?php echo $trainer['Trainer_status']; ?></p>
        <p><strong>Speciality:</strong> <?php echo $trainer['Speciality']; ?></p>
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
        <?php if ($feedback_res && mysqli_num_rows($feedback_res) > 0): ?>
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
    // JavaScript to switch sections
    const links = document.querySelectorAll(".menu-link");
    const sections = document.querySelectorAll(".box");

    links.forEach(link => {
        link.addEventListener("click", () => {
            links.forEach(l => l.classList.remove("active"));
            sections.forEach(s => s.classList.remove("active"));

            link.classList.add("active");
            document.getElementById(link.dataset.target).classList.add("active");
        });
    });
</script>

</body>
</html>
