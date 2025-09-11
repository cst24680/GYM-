<?php
session_start();
include "db.php";

if (!isset($_SESSION['Trainer_id'])) {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['Trainer_id'];
$id = $_GET['id'];

// Fetch existing workout
$sql = "
    SELECT ms.*, m.Mem_name, p.Workout_type 
    FROM member_schedule ms
    JOIN member_registration m ON ms.Mem_id = m.Mem_id
    JOIN plan_type p ON ms.Plan_type_id = p.Plan_type_id
    WHERE ms.Schedule_id = $id AND ms.Trainer_id = $trainer_id
";
$res = mysqli_query($conn, $sql);
$workout = mysqli_fetch_assoc($res);

if (!$workout) {
    die("Workout not found or you don’t have permission.");
}

// Fetch all workout plans
$plans = mysqli_query($conn, "SELECT * FROM plan_type");

// Handle Update/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $plan = $_POST['Plan_type_id'];
        $date = $_POST['Workout_date'];

        $update = "UPDATE member_schedule 
                   SET Plan_type_id='$plan', Workout_date='$date' 
                   WHERE Schedule_id=$id AND Trainer_id=$trainer_id";

        if (mysqli_query($conn, $update)) {
            header("Location: view_assigned_workouts.php?msg=updated");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['delete'])) {
        $delete = "DELETE FROM member_schedule 
                   WHERE Schedule_id=$id AND Trainer_id=$trainer_id";

        if (mysqli_query($conn, $delete)) {
            header("Location: view_assigned_workouts.php?msg=deleted");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Workout</title>
    <style>
        body { font-family: Arial, sans-serif; background: #000; color: white; padding: 20px; }
        h1 { color: orange; }
        form { margin-top: 20px; }
        select, input { padding: 8px; margin: 5px; }
        button { padding: 8px 12px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .update { background: orange; color: black; }
        .update:hover { background: darkorange; }
        .delete { background: red; color: white; }
        .delete:hover { background: darkred; }
        a.back { display:inline-block; margin-top:15px; padding:8px 12px; background:grey; color:white; text-decoration:none; border-radius:4px; }
    </style>
</head>
<body>

<h1>Manage Workout for <?php echo $workout['Mem_name']; ?></h1>

<form method="POST">
    <label>Workout Plan:</label>
    <select name="Plan_type_id" required>
        <?php while ($p = mysqli_fetch_assoc($plans)): ?>
            <option value="<?php echo $p['Plan_type_id']; ?>" 
                <?php if ($p['Plan_type_id'] == $workout['Plan_type_id']) echo "selected"; ?>>
                <?php echo $p['Workout_type']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Date:</label>
    <input type="date" name="Workout_date" value="<?php echo $workout['Workout_date']; ?>" required>

    <br><br>
    <button type="submit" name="update" class="update">Update Workout</button>
    <button type="submit" name="delete" class="delete" onclick="return confirm('Are you sure you want to delete this workout?');">Delete Workout</button>
</form>

<a class="back" href="view_assigned_workouts.php">⬅ Back</a>

</body>
</html>
