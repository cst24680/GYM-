<?php
// db connection
$conn = new mysqli("localhost", "root", "", "gym_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add Trainer
if (isset($_POST['add_trainer'])) {
    $name = $_POST['trainer_name'];
    $email = $_POST['trainer_email'];
    $password = md5($_POST['trainer_password']);
    $speciality = $_POST['trainer_speciality'];

    // Insert into trainer table
    $sql1 = "INSERT INTO trainer (Name, Email, Speciality) VALUES ('$name', '$email', '$speciality')";
    // Insert into login table
    $sql2 = "INSERT INTO login (Email, Password, User_type) VALUES ('$email', '$password', 'trainer')";

    if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
        $trainer_msg = "Trainer added successfully!";
    } else {
        $trainer_msg = "Error: " . $conn->error;
    }
}

// Add Dietician
if (isset($_POST['add_dietician'])) {
    $name = $_POST['dietician_name'];
    $email = $_POST['dietician_email'];
    $password = md5($_POST['dietician_password']);
    $speciality = $_POST['dietician_speciality'];

    // Insert into dietician table
    $sql1 = "INSERT INTO dietician (Name, Email, Speciality) VALUES ('$name', '$email', '$speciality')";
    // Insert into login table
    $sql2 = "INSERT INTO login (Email, Password, User_type) VALUES ('$email', '$password', 'dietician')";

    if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
        $dietician_msg = "Dietician added successfully!";
    } else {
        $dietician_msg = "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Trainer & Dietician</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { display: flex; gap: 50px; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 10px; width: 300px; }
        input { width: 100%; padding: 8px; margin: 5px 0; }
        .success { color: green; }
        .error { color: red; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h1>Add Trainer & Dietician</h1>
    <div class="container">
        <!-- Trainer Form -->
        <form method="POST" action="">
            <h2>Trainer</h2>
            <input type="text" name="trainer_name" placeholder="Name" required>
            <input type="email" name="trainer_email" placeholder="Email" required>
            <input type="password" name="trainer_password" placeholder="Password" required>
            <input type="text" name="trainer_speciality" placeholder="Speciality" required>
            <input type="submit" name="add_trainer" value="Add Trainer">
            <?php if(isset($trainer_msg)) echo "<p class='success'>$trainer_msg</p>"; ?>
        </form>

        <!-- Dietician Form -->
        <form method="POST" action="">
            <h2>Dietician</h2>
            <input type="text" name="dietician_name" placeholder="Name" required>
            <input type="email" name="dietician_email" placeholder="Email" required>
            <input type="password" name="dietician_password" placeholder="Password" required>
            <input type="text" name="dietician_speciality" placeholder="Speciality" required>
            <input type="submit" name="add_dietician" value="Add Dietician">
            <?php if(isset($dietician_msg)) echo "<p class='success'>$dietician_msg</p>"; ?>
        </form>
    </div>
</body>
</html>
