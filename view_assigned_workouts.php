<?php
session_start();
include "db.php";

if (!isset($_SESSION['Trainer_id'])) {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['Trainer_id'];

$sql = "
    SELECT ms.Schedule_id, ms.Workout_date, m.Mem_name, p.Workout_type, ms.Notes
    FROM member_schedule ms
    JOIN member_registration m ON ms.Mem_id = m.Mem_id
    JOIN plan_type p ON ms.Plan_type_id = p.Plan_type_id
    WHERE ms.Trainer_id = $trainer_id
    ORDER BY ms.Workout_date ASC
";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assigned Workouts</title>
    
    <link rel="stylesheet" href="trainer.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Set the main page background to black */
        body {
            background-color: #0E0E0E;
        }
        
        /* This styles the .page-container to look like the .box 
        from your dashboard and centers it on the page.
        */
        .page-container {
            /* Styling (to look like the dashboard) */
            background-color: #1A1A1A;
            padding: 30px; 
            border-radius: 12px;
            border: 1px solid #333;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);

            /* Centering & Sizing */
            max-width: 1200px;  /* Set a max width */
            margin: 40px auto;  /* 40px top/bottom, auto left/right to center it */
        }
    </style>
</head>
<body>

<div class="page-container">

    <a class="back-link" href="trainer_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Workouts You Have Assigned</h2>

    <?php if (mysqli_num_rows($res) > 0): ?>
        <table>
            <tr>
                <th>Member</th>
                <th>Workout</th>
                <th>Date</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['Mem_name']); ?></td>
                <td><?php echo htmlspecialchars($row['Workout_type']); ?></td>
                <td><?php echo htmlspecialchars($row['Workout_date']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['Notes'])); ?></td>
                <td>
                    <a class="action-link edit" href="manage_workout.php?id=<?php echo $row['Schedule_id']; ?>">Manage</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No workouts assigned yet.</p>
    <?php endif; ?>

</div> </body>
</html>