<?php
session_start();
include "db.php";

if (!isset($_SESSION['Trainer_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_SESSION['Trainer_id'];
    $mem_id = $_POST['Mem_id'];
    $plan_type_id = $_POST['Plan_type_id'];
    $workout_date = $_POST['Workout_date'];
    $description = $_POST['Workout_description'];

    $sql = "INSERT INTO member_schedule (Trainer_id, Mem_id, Plan_type_id, Workout_date, Notes) 
            VALUES ('$trainer_id', '$mem_id', '$plan_type_id', '$workout_date', '$description')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Workout assigned successfully!'); window.location.href='trainer_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
