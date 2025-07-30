<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $type = $_GET['type'];
    $id = $_GET['id'];

    if ($type === 'member') {
        mysqli_query($conn, "DELETE FROM member_registration WHERE Mem_id = $id");
    } elseif ($type === 'trainer') {
        mysqli_query($conn, "DELETE FROM trainer WHERE Trainer_id = $id");
    } elseif ($type === 'dietician') {
        mysqli_query($conn, "DELETE FROM dietician WHERE Dietician_id = $id");
    }
    header("Location: admin_dashboard.php");
    exit();
}

// Handle Update
if (isset($_POST['update_user'])) {
    $type = $_POST['user_type'];
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    if ($type === 'member') {
        $dob = $_POST['dob'];
        $age = $_POST['Mem_age'];
        $height = $_POST['Height'];
        $weight = $_POST['Weight'];
        $goal = $_POST['Plan_name'];

        mysqli_query($conn, "UPDATE member_registration SET 
            Mem_name='$name', 
            Mem_phno='$phone',
            Mem_dob='$dob',
            Mem_age='$age',
            Height='$height',
            Weight='$weight',
            Plan_name='$goal'
            WHERE Mem_id=$id
        ");
    } elseif ($type === 'trainer') {
        mysqli_query($conn, "UPDATE trainer SET Trainer_name='$name', Trainer_phno='$phone' WHERE Trainer_id=$id");
    } elseif ($type === 'dietician') {
        mysqli_query($conn, "UPDATE dietician SET Dietician_name='$name', Dietician_phno='$phone' WHERE Dietician_id=$id");
    }

    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
  <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #000000; /* pure black background */
        color: white;
        margin: 0;
        padding: 0; /* remove default spacing */;
    }

    h2, h3 {
      text-align: center;
        margin: 0;
        padding: 20px 0;
        background-color: #000000; /* ensure heading background is black too */
        color: white         
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
        color: white;           /* table text white */
        background-color: #222 ; /* dark background for table */
    }

    th, td {
        border: 1px solid #555;
        padding: 10px;
        text-align: center;
    }

    th {
        background-color: #333; /* darker for table headers */
    }

     a {
        margin: 0 5px;
        color: #f50707ff; /* red links for actions */
        text-decoration: none;
    }

    

    .form-container {
        background: #111;       /* dark background for edit form */
        padding: 20px;
        border: 1px solid #444;
        margin-bottom: 30px;
        color: white;
    }

    input[type="text"], button {
        background-color: #222;
        color: white;
        border: 1px solid #666;
        padding: 8px;
        margin-top: 5px;
    }

    button {
      grid-column: 2;
      padding: 10px 20px;
      background-color: #28a745;
      border: none;
      border-radius: 6px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #218838;
    }

</style>
</head>
<body>
<h2>Welcome Admin</h2>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $type = $_GET['type'];
    $id = $_GET['id'];
    $result = null;

    if ($type === 'member') {
        $result = mysqli_query($conn, "SELECT * FROM member_registration WHERE Mem_id = $id");
        $row = mysqli_fetch_assoc($result);
        $name = $row['Mem_name'];
        $phone = $row['Mem_phno'];
        $dob = $row['Mem_dob'];
        $age = $row['Mem_age'];
        $Height = $row['Height'];
        $Weight = $row['Weight'];
        $Plan_name = $row['Plan_name'];
    } elseif ($type === 'trainer') {
        $result = mysqli_query($conn, "SELECT * FROM trainer WHERE Trainer_id = $id");
        $row = mysqli_fetch_assoc($result);
        $name = $row['Trainer_name'];
        $phone = $row['Trainer_phno'];
    } elseif ($type === 'dietician') {
        $result = mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_id = $id");
        $row = mysqli_fetch_assoc($result);
        $name = $row['Dietician_name'];
        $phone = $row['Dietician_phno'];
    }
?>
<div class="form-container">
    <h3>Edit <?= ucfirst($type) ?> Info</h3>
    <form method="POST">
        <input type="hidden" name="user_type" value="<?= $type ?>">
        <input type="hidden" name="user_id" value="<?= $id ?>">
        <label>Name: <input type="text" name="name" value="<?= $name ?>" required></label><br><br>
        <label>Phone: <input type="text" name="phone" value="<?= $phone ?>" required></label><br><br>

        <?php if ($type === 'member') { ?>
            <label>Date of Birth: <input type="text" name="dob" value="<?= $dob ?>" required></label><br><br>
            <label>Height: <input type="text" name="Height" value="<?= $Height ?>" required></label><br><br>
            <label>Weight: <input type="text" name="Weight" value="<?= $Weight ?>" required></label><br><br>
            <label>Age: <input type="text" name="Mem_age" value="<?= $age ?>" required></label><br><br>
            <label>Goal: <input type="text" name="Plan_name" value="<?= $Plan_name ?>" required></label><br><br>
        <?php } ?>

        <button type="submit" name="update_user">Update</button>
    </form>
</div>
<?php } ?>

<h3>Members</h3>
<div class="card">
<table>
<tr><th>Name</th><th>Phone</th><th>Goal</th><th>Actions</th></tr>
<?php
$members = mysqli_query($conn, "SELECT * FROM member_registration");
while ($row = mysqli_fetch_assoc($members)) {
    echo "<tr><td>{$row['Mem_name']}</td><td>{$row['Mem_phno']}</td><td>{$row['Plan_name']}</td><td>
        <a href='?action=edit&type=member&id={$row['Mem_id']}'>Edit</a>
        <a href='?action=delete&type=member&id={$row['Mem_id']}' onclick=\"return confirm('Delete?')\">Delete</a>
    </td></tr>";
}
?>
</table>
</div>

<h3>Trainers</h3>
<div class="card">
<table>
<tr><th>Name</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
<?php
$trainers = mysqli_query($conn, "SELECT * FROM trainer");
while ($row = mysqli_fetch_assoc($trainers)) {
    echo "<tr><td>{$row['Trainer_name']}</td><td>{$row['Trainer_phno']}</td><td>{$row['Trainer_status']}</td><td>
        <a href='?action=edit&type=trainer&id={$row['Trainer_id']}'>Edit</a>
        <a href='?action=delete&type=trainer&id={$row['Trainer_id']}' onclick=\"return confirm('Delete?')\">Delete</a>
    </td></tr>";
}
?>
</table>
</div>

<h3>Dieticians</h3>
<div class="card">
<table>
<tr><th>Name</th><th>Phone</th><th>Actions</th></tr>
<?php
$dieticians = mysqli_query($conn, "SELECT * FROM dietician");
while ($row = mysqli_fetch_assoc($dieticians)) {
    echo "<tr><td>{$row['Dietician_name']}</td><td>{$row['Dietician_phno']}</td><td>
        <a href='?action=edit&type=dietician&id={$row['Dietician_id']}'>Edit</a>
        <a href='?action=delete&type=dietician&id={$row['Dietician_id']}' onclick=\"return confirm('Delete?')\">Delete</a>
    </td></tr>";
}
?>
</table>
</div>
</body>
</html>
