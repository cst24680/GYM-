<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'trainer') {
    header("Location: login.html");
    exit();
}

$trainer_email = $_SESSION['email'];
$conn = mysqli_connect("localhost", "root", "", "gym_db");

// Fetch trainer details
$trainer_query = "SELECT * FROM trainer WHERE Trainer_email = '$trainer_email'";
$trainer_result = mysqli_query($conn, $trainer_query);
$trainer = mysqli_fetch_assoc($trainer_result);

// Fetch members assigned to this trainer
$members_query = "SELECT * FROM member_registration WHERE Trainer_id = " . $trainer['Trainer_id'];
$members_result = mysqli_query($conn, $members_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trainer Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fdfbf6;
      margin: 0;
      padding: 0;
      color: #222;
    }
    header {
      background-color: #e50914;
      color: #fff;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .container {
      padding: 30px;
    }
    .card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2, h3 {
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    table th, table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    form {
      margin-top: 10px;
    }
    input[type="text"], select {
      padding: 5px;
      margin-right: 10px;
    }
    .btn {
      padding: 6px 12px;
      background-color: #e50914;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<header>
  <h1>Trainer Dashboard</h1>
  <a href="logout.php" style="color:white;">Logout</a>
</header>

<div class="container">
  <div class="card">
    <h2>Welcome, <?= $trainer['Trainer_name'] ?></h2>
    <p>Email: <?= $trainer['Trainer_email'] ?></p>
    <p>Phone: <?= $trainer['Trainer_phno'] ?></p>
    <p>DOB: <?= $trainer['Trainer_dob'] ?></p>
    <p>Gender: <?= $trainer['Trainer_gender'] ?></p>
    <p>Status: <?= $trainer['Trainer_status'] ?></p>
  </div>

  <div class="card">
    <h3>Assigned Members</h3>
    <?php if (mysqli_num_rows($members_result) > 0): ?>
      <table>
        <tr>
          <th>Member Name</th>
          <th>Goal</th>
          <th>Workout</th>
          <th>Assign Workout</th>
          <th>Diet Plan</th>
        </tr>
        <?php while ($mem = mysqli_fetch_assoc($members_result)):
          $plan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM plan_type WHERE Mem_id = " . $mem['Mem_id']));
          $diet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM diet_plans WHERE Mem_id = " . $mem['Mem_id']));
        ?>
        <tr>
          <td><?= $mem['Mem_name'] ?></td>
          <td><?= $mem['Goal'] ?></td>
          <td><?= $plan['Workout_type'] ?? 'Not Assigned' ?></td>
          <td>
            <form method="post" action="assign_workout.php">
              <input type="hidden" name="Mem_id" value="<?= $mem['Mem_id'] ?>">
              <select name="Workout_type" required>
                <option value="">Select</option>
                <option value="Weight Loss">Weight Loss</option>
                <option value="Muscle Gain">Muscle Gain</option>
                <option value="Cardio">Cardio</option>
              </select>
              <button type="submit" class="btn">Assign</button>
            </form>
          </td>
          <td><?= $diet['Diet'] ?? 'No diet plan' ?></td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No members assigned yet.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
