<?php
session_start();
include "db.php"; 

$flash = "";

// Check if the user is a logged-in Trainer
if (!isset($_SESSION['Trainer_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_SESSION['Trainer_id'];
    
    // Sanitize inputs
    $mem_id         = filter_input(INPUT_POST, 'Mem_id', FILTER_SANITIZE_NUMBER_INT) ?? 0;
    $plan_type_id   = filter_input(INPUT_POST, 'Plan_type_id', FILTER_SANITIZE_NUMBER_INT) ?? 0;
    $workout_date   = filter_input(INPUT_POST, 'Workout_date', FILTER_SANITIZE_SPECIAL_CHARS);
    $description    = filter_input(INPUT_POST, 'Workout_description', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validation
    if ($mem_id <= 0) {
        $flash = "❌ Error: Member ID is missing.";
    } elseif ($plan_type_id <= 0) {
        $flash = "❌ Error: Workout Plan ID is missing or invalid. Please select a valid plan.";
    } elseif (empty($workout_date)) {
        $flash = "❌ Error: Workout date is required.";
    } elseif (empty($description)) {
        $flash = "❌ Error: Workout description is required.";
    } else {
        // Convert to proper YYYY-MM-DD format
        $formatted_date = date('Y-m-d', strtotime($workout_date));

        // Prepared Statement
        $sql = "INSERT INTO member_schedule (Trainer_id, Mem_id, Plan_type_id, Workout_date, Notes) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // 'iiiss' → last two are strings (date + description)
            mysqli_stmt_bind_param($stmt, 'iiiss', $trainer_id, $mem_id, $plan_type_id, $formatted_date, $description);

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>
                    alert('✅ Workout assigned successfully!');
                    window.location.href='trainer_dashboard.php';
                </script>";
                exit;
            } else {
                $flash = "❌ Database Error: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        } else {
            $flash = "❌ Preparation Error: " . mysqli_error($conn);
        }
    }

    $_SESSION['flash'] = $flash;
    header("Location: trainer_dashboard.php");
    exit();
}
?>
