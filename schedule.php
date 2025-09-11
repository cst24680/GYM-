<?php
session_start();
require_once "db.php"; // DB connection

// Check if logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // 'member', 'trainer', or 'admin'
$user_id = $_SESSION['user_id'];

// ADD schedule - Trainer only
if ($role === 'trainer' && isset($_POST['add_schedule'])) {
    $mem_id = intval($_POST['mem_id']);
    $time = $_POST['time'];
    $day = $_POST['day'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO schedule (Trainer_id, Mem_id, Time, Day, Workout_description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $mem_id, $time, $day, $desc);
    $stmt->execute();
    $stmt->close();
}

// DELETE schedule - Trainer only
if ($role === 'trainer' && isset($_GET['delete'])) {
    $schedule_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM schedule WHERE Schedule_id = ? AND Trainer_id = ?");
    $stmt->bind_param("ii", $schedule_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch schedules based on role
if ($role === 'member') {
    $sql = "SELECT s.Schedule_id, t.Trainer_name, s.Time, s.Day, s.Workout_description
            FROM schedule s
            JOIN trainer t ON s.Trainer_id = t.Trainer_id
            WHERE s.Mem_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

} elseif ($role === 'trainer') {
    $sql = "SELECT s.Schedule_id, m.Mem_name, s.Time, s.Day, s.Workout_description
            FROM schedule s
            JOIN `member registration` m ON s.Mem_id = m.Mem_id
            WHERE s.Trainer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

} elseif ($role === 'admin') {
    $sql = "SELECT s.Schedule_id, m.Mem_name, t.Trainer_name, s.Time, s.Day, s.Workout_description
            FROM schedule s
            JOIN `member registration` m ON s.Mem_id = m.Mem_id
            JOIN trainer t ON s.Trainer_id = t.Trainer_id";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gym Schedule</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; background: #fff; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: center; }
        h1 { color: #333; }
        .form-container { background: white; padding: 15px; border-radius: 5px; width: 400px; margin-bottom: 20px; }
        .btn { padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 3px; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>

<h1>Schedule Management</h1>

<?php if ($role === 'trainer'): ?>
<div class="form-container">
    <h3>Add New Schedule</h3>
    <form method="POST">
        <label>Member ID:</label><br>
        <input type="number" name="mem_id" required><br><br>

        <label>Time:</label><br>
        <input type="time" name="time" required><br><br>

        <label>Day:</label><br>
        <input type="text" name="day" required><br><br>

        <label>Description:</label><br>
        <textarea name="description" required></textarea><br><br>

        <button class="btn" type="submit" name="add_schedule">Add Schedule</button>
    </form>
</div>
<?php endif; ?>

<h2>Schedules</h2>
<table>
    <tr>
        <?php if ($role === 'member'): ?>
            <th>Trainer</th>
        <?php elseif ($role === 'trainer'): ?>
            <th>Member</th>
        <?php else: ?>
            <th>Member</th>
            <th>Trainer</th>
        <?php endif; ?>
        <th>Time</th>
        <th>Day</th>
        <th>Description</th>
        <?php if ($role === 'trainer'): ?><th>Action</th><?php endif; ?>
    </tr>

    <?php foreach ($schedules as $row): ?>
    <tr>
        <?php if ($role === 'member'): ?>
            <td><?= htmlspecialchars($row['Trainer_name']); ?></td>
        <?php elseif ($role === 'trainer'): ?>
            <td><?= htmlspecialchars($row['Mem_name']); ?></td>
        <?php else: ?>
            <td><?= htmlspecialchars($row['Mem_name']); ?></td>
            <td><?= htmlspecialchars($row['Trainer_name']); ?></td>
        <?php endif; ?>

        <td><?= htmlspecialchars($row['Time']); ?></td>
        <td><?= htmlspecialchars($row['Day']); ?></td>
        <td><?= htmlspecialchars($row['Workout_description']); ?></td>

        <?php if ($role === 'trainer'): ?>
            <td>
                <a href="?delete=<?= $row['Schedule_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this schedule?')">Delete</a>
            </td>
        <?php endif; ?>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
