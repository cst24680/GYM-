<?php
session_start();
include "db.php";
include "helpers.php"; // (we’ll create this for BMI + Age categorization)

// Check dietician login
if (!isset($_SESSION['Dietician_id'])) {
    header("Location: login.php");
    exit();
}

$dietician_id = (int)$_SESSION['Dietician_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_template'])) {
    $member_id = (int)$_POST['member_id'];
    $diet_type = mysqli_real_escape_string($conn, $_POST['diet_type']);

    // Fetch member details
    $res = mysqli_query($conn, "SELECT Goal_type, BMI, Mem_age FROM member_registration WHERE Mem_id = $member_id LIMIT 1");
    $member = mysqli_fetch_assoc($res);

    $goal = $member['Goal_type'];
    $bmi = (float)$member['BMI'];
    $age = (int)$member['Mem_age'];

    $bmiCat = getBMICategory($bmi);
    $ageCat = getAgeCategory($age);

    // Try to find matching template
    $template = findTemplate($conn, $goal, $bmiCat, $ageCat, $diet_type);

    // Use override if given
    $planName = trim($_POST['plan_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$planName && $template) $planName = $template['Goal'] . " Plan";
    if (!$description && $template) $description = $template['Description'];

    if (!$planName || !$description) {
        $_SESSION['flash'] = "⚠️ No suitable template found and no custom text entered.";
        header("Location: dietician_dashboard.php");
        exit();
    }

    $planNameEsc = mysqli_real_escape_string($conn, $planName);
    $descEsc = mysqli_real_escape_string($conn, $description);

    $sql = "INSERT INTO diet_plans (Dietician_id, Mem_id, Plan_name, Diet_type, Description)
            VALUES ($dietician_id, $member_id, '$planNameEsc', '$diet_type', '$descEsc')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['flash'] = "✅ Diet plan assigned successfully!";
    } else {
        $_SESSION['flash'] = "❌ Error: " . mysqli_error($conn);
    }

    header("Location: dietician_dashboard.php");
    exit();
}
?>
