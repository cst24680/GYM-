<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mem_id = $_POST['Mem_id'];
    $workout = $_POST['Workout_type'];

    $conn = mysqli_connect("localhost", "root", "", "gym_db");

    // check if a plan already exists
    $check = mysqli_query($conn, "SELECT * FROM plan_type WHERE Mem_id = $mem_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE plan_type SET Workout_type = '$workout' WHERE Mem_id = $mem_id");
    } else {
        mysqli_query($conn, "INSERT INTO plan_type (Mem_id, Workout_type) VALUES ($mem_id, '$workout')");
    }

    header("Location: trainer_dashboard.php");
    exit();
}
?>
