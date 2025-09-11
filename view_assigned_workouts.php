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
    <style>
        body { font-family: Arial, sans-serif; background: #000; color: white; padding: 20px; }
        h1 { color: orange; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid orange; text-align: left; vertical-align: top; }
        a.manage { padding: 6px 10px; background: orange; color: black; text-decoration: none; border-radius: 4px; }
        a.manage:hover { background: darkorange; }
        a.back { display:inline-block; margin-top:15px; padding:8px 12px; background:grey; color:white; text-decoration:none; border-radius:4px; }
    </style>
</head>
<body>

<h1>Workouts You Have Assigned</h1>

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
                <a class="manage" href="manage_workout.php?id=<?php echo $row['Schedule_id']; ?>">Manage</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No workouts assigned yet.</p>
<?php endif; ?>

<a class="back" href="trainer_dashboard.php">⬅ Back</a>

</body>
</html>
